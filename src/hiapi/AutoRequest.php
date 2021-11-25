<?php

declare(strict_types=1);

namespace hipanel\hiart\hiapi;

use hiqdev\hiart\Query;
use hiqdev\hiart\QueryBuilderInterface;

class AutoRequest extends \hiqdev\hiart\auto\Request
{
    public function __construct(QueryBuilderInterface $builder, Query $query)
    {
        parent::__construct($builder, $query);
        array_unshift($this->tryClasses, Request::class);
    }
}
