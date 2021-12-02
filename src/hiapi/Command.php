<?php

declare(strict_types=1);

namespace hipanel\hiart\hiapi;

use hiqdev\hiart\ConnectionInterface;
use yii\base\Component;

class Command extends Component
{
    public ConnectionInterface $db;
}
