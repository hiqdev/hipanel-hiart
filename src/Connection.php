<?php
/**
 * HiPanel Hiart
 *
 * @link      https://github.com/hiqdev/hipanel-hiart
 * @package   hipanel-hiart
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2017, HiQDev (http://hiqdev.com/)
 */

namespace hipanel\hiart;

use hiqdev\hiart\ResponseInterface;
use Yii;
use yii\base\Application;

class Connection extends \hiqdev\hiart\rest\Connection implements ConnectionInterface
{
    public $queryBuilderClass = QueryBuilder::class;

    public $commandClass = Command::class;

    private $app;

    public function __construct(Application $app, $config = [])
    {
        $this->app = $app;
        parent::__construct($config);
    }

    /**
     * @param ResponseInterface $response
     * @return string|false error text or false
     */
    public function getResponseError(ResponseInterface $response)
    {
        if (!$this->isError($response)) {
            return false;
        }

        $error = $this->getError($response);
        if ($error === 'invalid_token') {
            $this->app->user->logout();
            $this->app->response->refresh()->send();
            $this->app->end();
        }

        return $error ?: 'unknown api error';
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
        if ($data === '0') {
            return false;
        }

        return is_array($data) ? array_key_exists('_error', $data) : !$data;
    }

    private function isRawDataEmpty(ResponseInterface $response)
    {
        return $response->getRawData() === null || $response->getRawData() === '';
    }

    private function getError(ResponseInterface $response)
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
     * Prepares authorization data.
     * If user is not authorized redirects to authorization.
     * @return array
     */
    public function getAuth()
    {
        if ($this->_disabledAuth) {
            return [];
        }

        $user  = Yii::$app->user;

        $identity = $user->identity;
        if ($identity === null) {
            Yii::$app->response->redirect('/site/login');
            Yii::$app->end();
        }

        $token = $identity->getAccessToken();
        if (empty($token)) {
            /// this is very important line
            /// without this line - redirect loop
            Yii::$app->user->logout();

            Yii::$app->response->redirect('/site/login');
            Yii::$app->end();
        }

        return ['access_token' => $token];
    }
}
