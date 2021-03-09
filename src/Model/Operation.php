<?php

declare(strict_types=1);

namespace App\Model;

final class Operation
{
    public const DATE_FORMAT = 'Y-m-d';

    private string $date;
    private OperationType $type;
    private Amount $amount;
    private Currency $currency;

    public function __construct(string $date, OperationType $type, Amount $amount, Currency $currency)
    {
        $parsed = \DateTimeImmutable::createFromFormat('!'.self::DATE_FORMAT, $date);

        if (false === $parsed || $parsed->format(self::DATE_FORMAT) !== $date) {
            throw new \InvalidArgumentException(sprintf('Not a valid date "%s", expected format %s', $date, self::DATE_FORMAT));
        }

        $this->date = $date;
        $this->type = $type;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getType(): OperationType
    {
        return $this->type;
    }

    public function getAmount(): Amount
    {
        return $this->amount;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }
}
