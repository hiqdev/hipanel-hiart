<?php
/**
 * HiPanel API client made with HiART.
 *
 * @link      https://github.com/hiqdev/hipanel-hiart
 * @package   hipanel-hiart
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2017, HiQDev (http://hiqdev.com/)
 */

use hipanel\hiart\hiapi\Connection;
use hipanel\hiart\hiapi\HiapiConnectionInterface;

return [
    'components' => [
        'hiapi' => [
            'class' => Connection::class,
            'baseUri' => $params['hiart.baseUri'],
        ],
    ],
    'container' => [
        'singletons' => [
            HiapiConnectionInterface::class => static fn() => Yii::$app->get('hiapi'),
        ],
    ],
];
