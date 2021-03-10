<?php

declare(strict_types=1);

namespace App\Iterator;

use App\Exception\StrategyException;

class FileContext
{
    private iterable $strategies = [];

    public function __construct(iterable $strategies)
    {
        foreach ($strategies as $strategy) {
            $this->strategies[] = $strategy;
        }
    }

    public function getTransactions(string $file): \Traversable
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->canRead($file)) {
                return $strategy->getTransactions($file);
            }
        }

        throw new StrategyException(sprintf('Strategy not found for file "%s"', $file));
    }
}
