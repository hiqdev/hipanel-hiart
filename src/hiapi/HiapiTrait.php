<?php

namespace hipanel\hiart\hiapi;

use hiqdev\hiart\AbstractConnection;
use Yii;

trait HiapiTrait
{
    public static function getHiapiDb(bool $isHiapiScenario = false)
    {
        if ($isHiapiScenario) {
            return AbstractConnection::getDb('hiapi');
        }

        return AbstractConnection::getDb();
    }

    public function query($defaultScenario, $data = [], array $options = [])
    {
        $options['isHiapiScenario'] = self::isHiapiScenario($defaultScenario);
        $action = $this->getScenarioAction($defaultScenario);

        return static::perform($action, $data, $options);
    }

    public static function find()
    {
        $class = self::getHiapiDb(self::isHiapiScenario('search'))->activeQueryClass;

        return new $class(get_called_class());
    }

    public static function perform($action, $data = [], array $options = [])
    {
        $isHiapiScenario = isset($options['isHiapiScenario']) && (bool)$options['isHiapiScenario'];
        return self::getHiapiDb($isHiapiScenario)->createCommand()->perform($action, static::tableName(), $data, $options)->getData();
    }

    private static function isHiapiScenario(string $scenario): bool
    {
        $router = Yii::$container->get(HiapiRouter::class);
        foreach ($router->routes as $route) {
            if ($route->canApply(get_called_class(), $scenario)) {
                return true;
            }
        }

        return false;
    }
}
