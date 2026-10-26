<?php

declare(strict_types=1);

namespace hipanel\hiart\tests\unit\hiapi;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use hipanel\hiart\Connection as HiartConnection;
use hipanel\hiart\hiapi\Connection;
use PHPUnit\Framework\TestCase;

final class HiapiConnectionTest extends TestCase
{
    private function connection(array $responses, array &$history, array &$auth): Connection
    {
        $legacy = $this->getMockBuilder(HiartConnection::class)
            ->disableOriginalConstructor()->onlyMethods(['getAuth'])->getMock();
        $legacy->expects($responses === [] ? $this->never() : $this->atLeastOnce())->method('getAuth')->willReturnCallback(static function () use (&$auth) { return $auth; });
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($history));

        return new Connection($legacy, [
            'baseUri' => 'https://hiapi.example/',
            'config' => ['handler' => $stack],
        ]);
    }

    private function response(int $status = 200, array $body = ['message' => 'ok']): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode($body));
    }

    public function testFormThenJsonThenGetDoNotReuseRequestState(): void
    {
        $history = [];
        $auth = ['access_token' => 'first', 'auth_ip' => '127.0.0.1'];
        $connection = $this->connection([$this->response(), $this->response(), $this->response()], $history, $auth);
        $connection->post('/form', ['page' => 2], ['name' => 'a b']);
        $this->assertStringEndsWith('/form?page=2', $connection->getRequest()->getFullUri());
        $this->assertSame('name=a+b', $connection->getRequest()->getParts()['body']);

        $auth = ['access_token' => 'second'];
        $payload = ['messages' => [['role' => 'user', 'content' => 'Привет']]];
        $connection->post('/v1/ai/chat', [], [], [
            'json' => $payload, 'timeout' => 180.0, 'connect_timeout' => 3.0,
            'http_errors' => false, 'headers' => ['X-Test' => 'chat'],
        ]);
        $this->assertStringEndsWith('/v1/ai/chat', $connection->getRequest()->getFullUri());
        $this->assertSame('/v1/ai/chat', $connection->getRequest()->getParts()['uri']);
        $auth = [];
        $connection->get('/next', ['page' => 3]);

        $this->assertSame('application/x-www-form-urlencoded', $history[0]['request']->getHeaderLine('Content-Type'));
        $this->assertSame('name=a+b', (string) $history[0]['request']->getBody());
        $json = $history[1]['request'];
        $this->assertSame($payload, json_decode((string) $json->getBody(), true));
        $this->assertSame('application/json', $json->getHeaderLine('Content-Type'));
        $this->assertSame('Bearer second', $json->getHeaderLine('Authorization'));
        $this->assertFalse($json->hasHeader('X-User-Ip'));
        $this->assertSame('chat', $json->getHeaderLine('X-Test'));
        $this->assertSame(180.0, $history[1]['options']['timeout']);
        $this->assertSame(3.0, $history[1]['options']['connect_timeout']);
        $this->assertFalse($history[1]['options']['http_errors']);
        $get = $history[2]['request'];
        $this->assertSame('GET', $get->getMethod());
        $this->assertSame('https://hiapi.example/next?page=3', (string) $get->getUri());
        $this->assertSame('', (string) $get->getBody());
        foreach (['Content-Type', 'Authorization', 'X-User-Ip', 'X-Test'] as $header) {
            $this->assertFalse($get->hasHeader($header));
        }
        $this->assertNotSame(180.0, $history[2]['options']['timeout'] ?? null);
    }

    public function testOptionsAreForwardedByEveryVerb(): void
    {
        foreach (['get', 'head', 'post', 'put', 'delete'] as $verb) {
            $history = [];
            $auth = [];
            $connection = $this->connection([$this->response()], $history, $auth);
            $args = ['/resource', ['page' => 1]];
            if (in_array($verb, ['post', 'put', 'delete'], true)) {
                $args[] = [];
            }
            $args[] = ['timeout' => 12.0, 'headers' => ['X-Test' => $verb]];
            $connection->$verb(...$args);
            $this->assertSame(strtoupper($verb), $history[0]['request']->getMethod());
            $this->assertSame('page=1', $history[0]['request']->getUri()->getQuery());
            $this->assertSame($verb, $history[0]['request']->getHeaderLine('X-Test'));
            $this->assertSame(12.0, $history[0]['options']['timeout']);
        }
    }

    public function testConflictingBodySourcesAreRejectedBeforeSending(): void
    {
        $history = [];
        $auth = [];
        $connection = $this->connection([], $history, $auth);
        foreach (['post', 'put', 'delete'] as $verb) {
            foreach (['json', 'body', 'form_params', 'multipart'] as $option) {
                try {
                    $connection->$verb('/resource', [], ['field' => 'value'], [$option => []]);
                    $this->fail('Conflicting body sources were accepted');
                } catch (\InvalidArgumentException $exception) {
                    $this->assertSame('Pass a form body or a body option, not both.', $exception->getMessage());
                }
            }
        }
        $this->assertSame([], $history);
    }

    public function testChatErrorResponsesKeepTheirStatusAndBody(): void
    {
        foreach ([400, 401, 404, 502] as $status) {
            $history = [];
            $auth = [];
            $body = ['error' => 'invalid_request', 'message' => 'reason'];
            $connection = $this->connection([$this->response($status, $body)], $history, $auth);
            $response = $connection->post('/v1/ai/chat', [], [], ['json' => ['messages' => []], 'http_errors' => false]);
            $this->assertSame($status, $response->getStatusCode());
            $this->assertSame($body, $response->getData());
        }
    }
}
