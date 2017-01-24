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

interface ConnectionInterface
{
    /**
     * Creates API command.
     * @param array $config
     * @return mixed response
     */
    public function createCommand(array $config = []);
}
