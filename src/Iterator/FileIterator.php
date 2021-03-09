<?php

declare(strict_types=1);

namespace App\Iterator;

use App\Exception\NotValidCvsFileException;
use App\Model\Amount;
use App\Model\Currency;
use App\Model\Operation;
use App\Model\OperationType;
use App\Model\Transaction;
use App\Model\User;
use App\Model\UserType;

class FileIterator implements \IteratorAggregate
{
    const COLUMN_COUNT = 6;

    private string $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function getIterator(): \Traversable
    {
        $handel = fopen($this->file, 'rb');
        if (false === $handel) {
            throw new NotValidCvsFileException(sprintf('Error Processing file "%s"', $this->file));
        }

        try {
            $line = 0;

            while (false !== ($row = fgetcsv($handel))) {
                ++$line;

                // fgetcsv() reports a blank line as a single null field
                if ([null] === $row) {
                    continue;
                }

                if (self::COLUMN_COUNT !== count($row)) {
                    throw new NotValidCvsFileException(sprintf('Line %d: expected %d columns, got %d in "%s"', $line, self::COLUMN_COUNT, count($row), implode(',', $row)));
                }

                yield $this->createTransaction($line, ...$row);
            }
        } finally {
            fclose($handel);
        }
    }

    private function createTransaction(
        int $line,
        string $date,
        string $userId,
        string $userType,
        string $operationType,
        string $operationAmount,
        string $operationCurrency
    ): Transaction {
        try {
            return new Transaction(
                new User($userId, UserType::fromString($userType)),
                new Operation(
                    $date,
                    OperationType::fromString($operationType),
                    Amount::fromString($operationAmount),
                    Currency::fromString($operationCurrency)
                )
            );
        } catch (\InvalidArgumentException $e) {
            throw new NotValidCvsFileException(sprintf('Line %d: %s', $line, $e->getMessage()), 0, $e);
        }
    }
}
