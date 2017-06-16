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
        $params['hiart.dbname'] => array_filter([
            'class'         => \hiqdev\hiart\demux\Connection::class,
            'selector'      => function ($query) {
                return in_array($query->from, ['bill', 'order'], true) ? 'billing-hiapi' : 'old-hiapi';
            },
        ]),
        $params['old-hiapi.dbname'] => array_filter([
            'class'         => \hipanel\hiart\old\Connection::class,
            'requestClass'  => $params['hiart.requestClass'],
            'name'          => $params['old-hiapi.dbname'],
            'config'        => $params['hiart.config'],
            'baseUri'       => $params['hiart.baseUri'],
        ]),
        $params['billing-hiapi.dbname'] => array_filter([
            'class'         => \hipanel\hiart\hiapi\Connection::class,
            'requestClass'  => $params['hiart.requestClass'],
            'name'          => $params['billing-hiapi.dbname'],
            'config'        => $params['billing-hiapi.config'],
            'baseUri'       => $params['billing-hiapi.baseUri'],
        ]),
    ]),
];
