<?php

declare(strict_types=1);

namespace hipanel\hiart\hiapi;

use hipanel\hiart\hiapi\QueryBuilder as HiapiQueryBuilder;
use hipanel\hiart\Connection as HiartConnection;

final class Connection extends HiartConnection
{
    public $queryBuilderClass = HiapiQueryBuilder::class;

    public static $dbname = 'hiapi';

    public $name = 'hiapi';

    public string $apiVersion = 'v1';
}
