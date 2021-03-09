<?php

declare(strict_types=1);

namespace App\Commission;

use App\Model\Transaction;

class FixedFeeStrategy implements CommissionInterface
{
    private string $fee;
    private int $scale;

    public function __construct(string $fee, int $scale)
    {
        $this->fee = $fee;
        $this->scale = $scale;
    }

    public function calculate(Transaction $transaction): string
    {
        $commission = bcmul($transaction->operation->amount, $this->fee, $this->scale);

        return bcdiv($commission, '100', $this->scale);
    }
}
