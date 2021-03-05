<?php

declare(strict_types=1);

namespace App\CommissionTask\Iterator;

use App\CommissionTask\Model\Operation;
use App\CommissionTask\Model\Transaction;
use App\CommissionTask\Model\User;

class FileIterator implements \IteratorAggregate
{
    private string $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function getIterator(): \Traversable
    {
        $handel = fopen($this->file, 'rb');
        if (false === $handel) {
            throw new \Exception('Error Processing');
        }
        while (false === feof($handel)) {
            $row = fgetcsv($handel, 0, ',');

            if (!is_array($row) || 6 !== count($row)) {
                throw new \Exception('Not valid line');
            }

            list($date, $userId, $userType, $operationType, $operationAmount, $operationCurrency) = $row;

            yield new Transaction(
                new User($userId, $userType),
                new Operation($date, $operationType, $operationAmount, $operationCurrency, '')
            );
        }
        fclose($handel);
    }
}
