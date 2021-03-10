<?php

declare(strict_types=1);

namespace App\Iterator;

interface FileStrategyInterface
{
    public function canRead(string $file): bool;

    public function getTransactions(string $file): \Traversable;
}
