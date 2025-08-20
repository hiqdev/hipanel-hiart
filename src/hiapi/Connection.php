<?php declare(strict_types=1);

namespace hipanel\hiart\hiapi;

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use hipanel\hiart\Connection as HiartConnection;
use hiqdev\hiart\Command;
use hiqdev\hiart\ConnectionInterface;
use hiqdev\hiart\guzzle\Response;
use hiqdev\hiart\guzzle\Request;
use hipanel\hiart\hiapi\Request as HiapiRequest;
use hiqdev\hiart\Query;
use Yii;
use yii\base\Component;
use hiqdev\hiart\ResponseInterface;

/**
 *
 * @property-read null $handler
 * @property-read mixed $auth
 * @property-read mixed $userAgent
 */
class Connection extends Component implements ConnectionInterface, HiapiConnectionInterface
{
    public ?string $baseUri = null;
    public ?string $name = null;
    public array $config = [];
    private ?Request $request = null;
    private HiartConnection $hiartConnection;

    public function __construct(HiartConnection $hiartConnection, $config = [])
    {
        parent::__construct($config);
        $this->hiartConnection = $hiartConnection;
        $this->hiartConnection->baseUri = $this->baseUri;
        $this->name = $this->hiartConnection->name;
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
        $profile = serialize($request);
        $profileCategory = static::getProfileCategory();
        Yii::beginProfile($profile, $profileCategory);
        $response = $request->send($options);
        Yii::endProfile($profile, $profileCategory);
        $this->checkResponse($response);

        return $response;
    }

    /**
     * @param \hiqdev\hiart\guzzle\Request[] $requests
     * @return Response[]
     */
    public function sendPool(array $requests): array
    {
        $request = reset($requests);
        /** @var Client $client * */
        $client = $request->getHandler();
        $profileCategory = static::getProfileCategory();
        $responses = [];
        $requestGenerator = function ($requests) use ($client, $profileCategory) {
            foreach ($requests as $idx => $request) {
                Yii::beginProfile(serialize($request), $profileCategory);
                yield $idx => fn() => $client->sendAsync($request->getWorker());
            }
        };
        $pool = new Pool($client, $requestGenerator($requests), [
            'concurrency' => 5,
            'fulfilled' => function ($response, $idx) use ($requests, $profileCategory, &$responses) {
                $request = $requests[$idx];
                $response = new Response($request, $response);
                Yii::endProfile(serialize($request), $profileCategory);
                $this->checkResponse($response);
                $responses[] = $response;
            },
            'rejected' => function ($reason) {
                // this is delivered each failed request
                Yii::getLogger()->log($reason, \yii\log\Logger::LEVEL_ERROR);
            },
        ]);
        $promise = $pool->promise(); // Initiate the transfers and create a promise
        $promise->wait(); // Force the pool of requests to complete

        return $responses;
    }

    public static function getDb($dbname = null): self
    {
        return Yii::$app->get('hiapi');
    }

    public function getRequest(): Request
    {
        if (!$this->request) {
            $this->request = new HiapiRequest(new QueryBuilder($this), new Query());
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

    public function getTraceparentHeader(): string
    {
        return $this->hiartConnection->getTraceparentHeader();
    }
}
