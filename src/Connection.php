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

    public $queryClass = Query::class;

    public $activeQueryClass = ActiveQuery::class;

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
        $data = $response->getData();
        if ($data === '0') {
            return false;
        }

        return is_array($data) ? array_key_exists('_error', $data) : !$data;
    }

    private function getError(ResponseInterface $response)
    {
        $data = $response->getData();

        return isset($data['_error']) ? $data['_error'] : null;
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
        if (!$token && $user->loginRequired() !== null) {
            Yii::$app->response->redirect('/site/logout');
            Yii::$app->end();
        }

        return ['access_token' => $token];
    }
}
