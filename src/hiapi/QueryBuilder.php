<?php
declare(strict_types=1);

namespace hipanel\hiart\hiapi;

use hipanel\hiart\QueryBuilder as HiartQueryBuilder;
use hiqdev\hiart\Query;

class QueryBuilder extends HiartQueryBuilder
{
    public function buildUri(Query $query)
    {
        return 'api/' . $this->db->apiVersion . '/' . parent::buildUri($query);
    }

    public function buildCommand(Query $query)
    {
        $action = $query->action;
        if (is_array($action)) {
            $action = end($action);
        }

        $prefix = '';
        if ($action === 'search' && empty($query->getOption('batch'))) {
            $prefix = 's';
        }

        return $prefix . '/' . $action;
    }
}
