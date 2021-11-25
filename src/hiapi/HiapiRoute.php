<?php
declare(strict_types=1);

namespace hipanel\hiart\hiapi;

final class HiapiRoute
{
    private string $modelClass;

    private array $scenarios;

    public function __construct(string $modelClass, array $scenarios)
    {
        $this->modelClass = $modelClass;
        $this->scenarios = $scenarios;
    }

    public function canApply(string $calledClass, $scenario): bool
    {
        return $this->modelClass === $calledClass && in_array($scenario, $this->scenarios, true);
    }
}
