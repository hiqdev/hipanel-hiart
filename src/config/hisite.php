<?php

return [
    'components' => [
        'hiart' => [
            'class' => \hipanel\hiart\Connection::class,
            'config' => $params['hiart.config'],
        ],
    ],
    'container' => [
        'singletons' => [
            \hipanel\hiart\ConnectionInterface::class => function () {
                return Yii::$app->get('hiart');
            },
        ],
    ],
];
