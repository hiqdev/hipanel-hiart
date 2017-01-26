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
 * ActiveQuery for HiPanel API.
 */
class ActiveQuery extends \hiqdev\hiart\ActiveQuery
{
    public function searchOne($db = null)
    {
        return reset(parent::searchOne($db));
    }
}
