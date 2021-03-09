<?php

declare(strict_types=1);

namespace App\Commission;

use App\DataProvider\RangeStrategyDataProviderInterface;
use App\Model\Transaction;

class RangeStrategy extends CommissionStrategy
{
    protected array $requiredOptions = [
        'fee',
        'freeAmountPerWeek',
        'freeWithdrawCountPerWeek',
    ];

    private RangeCalculatorInterface $rangeCalculator;
    private RangeStrategyDataProviderInterface $dataProvider;
    private int $scale;

    public function __construct(
        RangeCalculatorInterface $rangeCalculator,
        RangeStrategyDataProviderInterface $dataProvider,
        int $bcmathScale
    ) {
        $this->rangeCalculator = $rangeCalculator;
        $this->dataProvider = $dataProvider;
        $this->scale = $bcmathScale;
    }

    public function calculate(Transaction $transaction): string
    {
        $user = $transaction->user;
        $operation = $transaction->operation;
        [$weekStart, $weekEnd] = $this->rangeCalculator->getRange($operation->date);
        [$perWeekAmount, $perWeekCount] = $this->dataProvider->getTotalAmountAndTransactionCount(
            $user->id, $operation->type, $weekStart, $weekEnd
        );
        $freeAmountPerWeekAfterConversion = bcmul($this->options['freeAmountPerWeek'], $operation->rate, $this->scale);
        $perWeekAmountAfterConversion = bcmul($perWeekAmount, $operation->rate, $this->scale);

        if (
            $perWeekAmountAfterConversion > $freeAmountPerWeekAfterConversion
            || $perWeekCount >= $this->options['freeWithdrawCountPerWeek']
        ) {
            // standard fee
            $amountForFee = $operation->amount;
        } else {
            $totalAmount = bcadd($perWeekAmountAfterConversion, $operation->amount, $this->scale);

            // commission is calculated only for the exceeded amount
            $amountForFee = bcsub($totalAmount, $freeAmountPerWeekAfterConversion, $this->scale);

            // no fee
            if (bccomp($amountForFee, '0.00', $this->scale) <= 0) {
                $amountForFee = '0.00';
            }
        }

        $commission = bcmul($amountForFee, $this->options['fee'], $this->scale);

        return bcdiv($commission, '100', $this->scale);
    }
}
