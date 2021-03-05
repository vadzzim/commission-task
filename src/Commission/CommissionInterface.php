<?php

declare(strict_types=1);

namespace App\CommissionTask\Commission;

use App\CommissionTask\Model\Transaction;

interface CommissionInterface
{
    public function calculate(Transaction $transaction): string;
}
