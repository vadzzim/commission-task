<?php

declare(strict_types=1);

namespace App\Commission;

use App\Model\Transaction;

class FixedFeeStrategy extends CommissionStrategy
{
    protected array $requiredOptions = [
        'fee',
    ];

    private int $scale;

    public function __construct(int $bcmathScale)
    {
        $this->scale = $bcmathScale;
    }

    public function calculate(Transaction $transaction): string
    {
        $commission = bcmul($transaction->operation->amount, $this->options['fee'], $this->scale);

        return bcdiv($commission, '100', $this->scale);
    }
}
