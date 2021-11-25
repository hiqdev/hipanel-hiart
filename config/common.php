<?php
/**
 * HiPanel API client made with HiART.
 *
 * @link      https://github.com/hiqdev/hipanel-hiart
 * @package   hipanel-hiart
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2017, HiQDev (http://hiqdev.com/)
 */

return [
    'components' => array_filter([
        'hiapi' => array_filter([
            'class'         => \hipanel\hiart\hiapi\Connection::class,
            'requestClass'  => \hipanel\hiart\hiapi\AutoRequest::class,
            'baseUri'       => $params['hiart.baseUri'],
            'apiVersion'       => 'v1',
        ]),
    ]),
    'container' => [
        'singletons' => [
            \hiqdev\hiart\hiapi\HiapiConnectionInterface::class => function () {
                return Yii::$app->get(Yii::$app->params['hiapi']);
            },
        ],
    ],
];
