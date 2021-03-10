<?php

declare(strict_types=1);

namespace App\Iterator;

class CvsStrategy implements FileStrategyInterface
{
    private const FILE_EXTENSION = 'csv';

    public function canRead(string $file): bool
    {
        $info = pathinfo($file);

        return self::FILE_EXTENSION === $info['extension'];
    }

    public function getTransactions(string $file): \Traversable
    {
        return new CsvIterator($file);
    }
}
