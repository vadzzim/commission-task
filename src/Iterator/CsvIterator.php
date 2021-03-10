<?php

declare(strict_types=1);

namespace App\Iterator;

use App\Exception\NotValidCvsFileException;
use App\Model\Operation;
use App\Model\Transaction;
use App\Model\User;

class CsvIterator implements \IteratorAggregate
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
            throw new NotValidCvsFileException(sprintf('Error Processing file "%s', $this->file));
        }

        while (false === feof($handel)) {
            $row = fgetcsv($handel);

            if (!is_array($row)) {
                throw new NotValidCvsFileException('An invalid handle is supplied or other errors, including end of file');
            }

            if (self::COLUMN_COUNT !== count($row)) {
                throw new NotValidCvsFileException(sprintf('Not valid line "%s"', join(',', $row)));
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
