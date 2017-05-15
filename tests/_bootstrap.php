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

require_once file_exists($bootstrap) ? $bootstrap : __DIR__ . '/../vendor/autoload.php';
