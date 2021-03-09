<?php

declare(strict_types=1);

namespace App\Commission;

use App\Model\Transaction;

final class FixedFeeStrategy implements CommissionInterface
{
    private string $fee;
    private int $scale;

    public function __construct(string $fee, int $bcmathScale)
    {
        $this->fee = $fee;
        $this->scale = $bcmathScale;
    }

    public function calculate(Transaction $transaction): string
    {
        $commission = bcmul($transaction->getOperation()->getAmount()->getValue(), $this->fee, $this->scale);

        return bcdiv($commission, '100', $this->scale);
    }
}
