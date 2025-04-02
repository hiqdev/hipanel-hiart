<?php
/**
 * HiPanel API client made with HiART.
 *
 * @link      https://github.com/hiqdev/hipanel-hiart
 * @package   hipanel-hiart
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2017, HiQDev (http://hiqdev.com/)
 */

namespace hipanel\hiart;

use hiqdev\hiart\ResponseInterface;
use yii\base\Application;

class Connection extends \hiqdev\hiart\rest\Connection implements ConnectionInterface
{
    public $queryBuilderClass = QueryBuilder::class;

    private $app;

    public function __construct(Application $app, $config = [])
    {
        $this->app = $app;
        parent::__construct($config);
    }

    /**
     * Fixes response:
     * - if batch - don't touch
     * - if single: [] => null, else get first item
     * @param ResponseInterface $response
     * @return bool if response doesn't need to be checked for error
     */
    protected function fixResponse(ResponseInterface $response)
    {
        if ($this->isBatchRequest($response)) {
            return false;
        }

        if ($this->isHttpError($response)) {
            throw new HttpResponseException(sprintf(
                "HTTP Error: %s - %s",
                $response->getStatusCode(),
                var_export($response->getData(), true),
            ));
        }

        $rows = $response->getData();

        if (!is_array($rows)) {
            throw new InvalidApiResponseException("Invalid API response: " . var_export($rows, true));
        }

        if (empty($rows)) {
            $this->setResponseData($response, null);

            return true;
        }

        $this->setResponseData($response, reset($rows));

        return false;
    }

    private function isBatchRequest(ResponseInterface $response): bool
    {
        return $response->getRequest()->getQuery()->getOption('batch') ?? false;
    }

    private function isHttpError(ResponseInterface $response): bool
    {
        return $response->getStatusCode() != 200;
    }

    /**
     * @param ResponseInterface $response
     * @param array|null $data
     */
    protected function setResponseData(ResponseInterface $response, $data)
    {
        $class = new \ReflectionObject($response);
        $prop = $class->getProperty('data');
        $prop->setAccessible(true);
        $prop->setValue($response, $data);
    }

    /**
     * Calls fixResponse.
     * @param ResponseInterface $response
     * @return string|false error text or false
     */
    public function getResponseError(ResponseInterface $response)
    {
        if ($this->isError($response)) {
            $error = $this->getError($response);
            if (in_array($error, ['invalid_token', 'not_allowed_ip'], true)) {
                $this->app->user->logout();
                $this->app->response->refresh()->send();
                $this->app->end();
            }

            return $error ?: 'unknown api error';
        }

        if ($response->getRequest()->getQuery()->action === 'search') {
            $this->fixResponse($response);
        }

        return false;
    }

    /**
     * @param ResponseInterface $response
     * @return bool
     */
    public function isError(ResponseInterface $response)
    {
        if ($this->isRawDataEmpty($response)) {
            return true;
        }

        $data = $response->getData();
        if ($data === '0' || $data === 0) {
            return false;
        }

        return is_array($data) ? array_key_exists('_error', $data) : !$data;
    }

    protected function isRawDataEmpty(ResponseInterface $response)
    {
        return $response->getRawData() === null || $response->getRawData() === '';
    }

    protected function getError(ResponseInterface $response)
    {
        if ($this->isRawDataEmpty($response)) {
            return 'The response body is empty';
        }

        $data = $response->getData();
        if (isset($data['_error'])) {
            return $data['_error'];
        }

        return null;
    }

    /**
     * Gets auth data from user.
     * @return array
     */
    public function getAuth()
    {
        if ($this->_disabledAuth) {
            return [];
        }

        return $this->app->user->getAuthData();
    }
}
