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

/**
 * Command for HiPanel API.
 */
class Command extends \hiqdev\hiart\Command
{
    public function search($options = [])
    {
        $rows = parent::search($options);
        $isBatch = $this->request->getQuery()->getOption('batch');

        return $isBatch ? $rows : reset($rows);
    }
}
