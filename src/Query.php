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
 * Query for HiPanel API.
 */
class Query extends \hiqdev\hiart\Query
{
    public function searchOne($db = null)
    {
        return reset(parent::searchOne($db));
    }
}
