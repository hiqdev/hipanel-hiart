<?php
/**
 * HiPanel API client made with HiART.
 *
 * @link      https://github.com/hiqdev/hipanel-hiart
 * @package   hipanel-hiart
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2017, HiQDev (http://hiqdev.com/)
 */

error_reporting(E_ALL);

$bootstrap = __DIR__ . '/../src/_bootstrap.php';

if (file_exists($bootstrap)) {
    require_once $bootstrap;
} else {
    $vendor = is_file(__DIR__ . '/../vendor/autoload.php')
        ? __DIR__ . '/../vendor'
        : dirname(__DIR__, 4) . '/vendor';
    require_once $vendor . '/autoload.php';
    require_once $vendor . '/yiisoft/yii2/Yii.php';
}
