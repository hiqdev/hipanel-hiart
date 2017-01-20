<?php
/**
 * Tools to use API as ActiveRecord for Yii2
 *
 * @link      https://github.com/hiqdev/yii2-hiart
 * @package   yii2-hiart
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2015-2016, HiQDev (http://hiqdev.com/)
 */

namespace hipanel\hiart;

use hiqdev\hiart\Query;
use yii\base\InvalidParamException;
use yii\base\NotSupportedException;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;

/**
 * QueryBuilder for HiPanel API.
 */
class QueryBuilder extends \hiqdev\hiart\QueryBuilder
{
    public function buildMethod(Query $query)
    {
        return 'POST';
    }

    public function buildUri(Query $query)
    {
        $from = is_array($query->from) ? reset($query->from) : $query->from;

        return lcfirst($from . ($query->getOption('batch') ? 's' : '') . $this->buildCommand($query));
    }

    public function buildCommand(Query $query)
    {
        $action = $query->action;
        if ($action === 'search' && empty($query->getOption('batch'))) {
            $action = 'get-info';
        }

        return Inflector::id2camel($action);
    }

    public function buildFormParams(Query $query)
    {
        return $query->params;
    }

    public function buildAuth(Query $query)
    {
        $query->addParams($this->db->getAuth());
    }

    /**
     * @param Query $query
     * @throws NotSupportedException
     * @return Query
     */
    public function prepare(Query $query)
    {
        $parts = [];
        $query->prepare($this);

        $this->buildSelect($query->select, $parts);
        $this->buildCount($query->count, $parts);
        $this->buildLimit($query->limit, $parts);
        $this->buildPage($query->offset, $query->limit, $parts);
        $this->buildOrderBy($query->orderBy, $parts);

        $parts = ArrayHelper::merge($parts, $this->buildCondition($query->where), $query->body);

        $query->addParams($parts);

        return $query;
    }

    public function buildCount($count, &$parts)
    {
        if (!empty($count)) {
            $parts['count'] = 1;
        }
    }

    public function buildLimit($limit, &$parts)
    {
        if (!empty($limit)) {
            if ($limit === -1) {
                $limit = 'ALL';
            }
            $parts['limit'] = $limit;
        }
    }

    public function buildPage($offset, $limit, &$parts)
    {
        if ($offset > 0) {
            $parts['page'] = ceil($offset / $limit) + 1;
        }
    }

    private $_sort = [
        SORT_ASC  => '_asc',
        SORT_DESC => '_desc',
    ];

    public function buildOrderBy($orderBy, &$parts)
    {
        if (!empty($orderBy)) {
            $parts['orderby'] = key($orderBy) . $this->_sort[reset($orderBy)];
        }
    }

    public function buildSelect($select, &$parts)
    {
        if (!empty($select)) {
            foreach ($select as $attribute) {
                $parts['select'][$attribute] = $attribute;
            }
        }
    }
}
