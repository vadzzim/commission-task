<?php

declare(strict_types=1);

namespace App\Commission;

use App\Exception\StrategyException;

class StrategyContext
{
    private iterable $strategies = [];

    public function __construct(iterable $strategies)
    {
        foreach ($strategies as $strategy) {
            $key = get_class($strategy);

            if (array_key_exists($key, $this->strategies)) {
                throw new StrategyException(sprintf('Strategy already exist with key "%s"', $key));
            }

            $this->strategies[get_class($strategy)] = $strategy;
        }
    }

    public function getStrategy(string $key): CommissionInterface
    {
        if (!array_key_exists($key, $this->strategies)) {
            throw new StrategyException(sprintf('Strategy "%s" does not exist', $key));
        }

        return $this->strategies[$key];
    }
}
