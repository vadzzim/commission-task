<?php

declare(strict_types=1);

namespace App\Commission;

use App\Model\Transaction;

interface CommissionInterface
{
    public function calculate(Transaction $transaction): string;

    public function setOptions(array $options): void;
}
