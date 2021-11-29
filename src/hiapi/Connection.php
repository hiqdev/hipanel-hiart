<?php

declare(strict_types=1);

namespace hipanel\hiart\hiapi;

use hipanel\hiart\Connection as HiartConnection;
use hiqdev\hiart\Command;
use hiqdev\hiart\ConnectionInterface;
use hiqdev\hiart\Query;
use Yii;
use yii\base\Component;
use hiqdev\hiart\ResponseInterface;

class Connection extends Component implements ConnectionInterface, HiapiConnectionInterface
{
    public ?string $baseUri = null;

    public array $config = [];

    private ?Request $request = null;

    private HiartConnection $hiartConnection;

    public function __construct(HiartConnection $hiartConnection, $config = [])
    {
        parent::__construct($config);
        $this->hiartConnection = $hiartConnection;
        $this->hiartConnection->baseUri = $this->baseUri;
    }

    public function getHandler()
    {
        return null;
    }

    public function get(string $path, array $params = []): ResponseInterface
    {
        $request = $this->getRequest()->fill('GET', $path, $params);

        return $this->send($request);
    }

    public function head(string $path, array $params = []): ResponseInterface
    {
        $request = $this->getRequest()->fill('HEAD', $path, $params);

        return $this->send($request);
    }

    public function post(string $path, array $params = [], array $body = []): ResponseInterface
    {
        $request = $this->getRequest()->fill('POST', $path, $params, $body);

        return $this->send($request);
    }

    public function put(string $path, array $params = [], array $body = []): ResponseInterface
    {
        $request = $this->getRequest()->fill('PUT', $path, $params, $body);

        return $this->send($request);
    }

    public function delete(string $path, array $params = [], array $body = []): ResponseInterface
    {
        $request = $this->getRequest()->fill('DELETE', $path, $params, $body);

        return $this->send($request);
    }

    public function send($request, array $options = []): ResponseInterface
    {
        $profile = serialize($this->getRequest());
        $category = static::getProfileCategory();
        Yii::beginProfile($profile, $category);
        $response = $request->send($options);
        Yii::endProfile($profile, $category);
        $this->checkResponse($response);

        return $response;
    }

    public static function getDb($dbname = null): self
    {
        return Yii::$app->get('hiapi');
    }

    public function getRequest(): Request
    {
        if (!$this->request) {
            $this->request = new Request(new QueryBuilder($this), new Query());
        }

        return $this->request;
    }

    public function createCommand(array $config = []): Command
    {
        $config['db'] = $this;

        return new Command($config);
    }

    private static function getProfileCategory(): string
    {
        return Command::getProfileCategory();
    }

    public function checkResponse(ResponseInterface $response)
    {
        $this->hiartConnection->checkResponse($response);
    }

    public function getBaseUri(): string
    {
        return $this->hiartConnection->getBaseUri();
    }

    public function getAuth()
    {
        return $this->hiartConnection->getAuth();
    }

    public function getUserAgent()
    {
        return $this->hiartConnection->getUserAgent();
    }
}
