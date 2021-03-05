<?php

declare(strict_types=1);

namespace App\CommissionTask\Commission;

use App\CommissionTask\Model\Transaction;

class RangeStrategy implements CommissionInterface
{
    private RangeCalculatorInterface $rangeCalculator;
    private RangeStrategyDataProviderInterface $dataProvider;
    private string $fee;
    private string $freeAmountPerWeek;
    private int $freeWithdrawCountPerWeek;
    private int $scale;

    public function __construct(
        RangeCalculatorInterface $rangeCalculator,
        RangeStrategyDataProviderInterface $dataProvider,
        string $fee,
        string $freeAmountPerWeek,
        int $freeWithdrawCountPerWeek,
        int $scale
    ) {
        $this->rangeCalculator = $rangeCalculator;
        $this->dataProvider = $dataProvider;
        $this->fee = $fee;
        $this->freeAmountPerWeek = $freeAmountPerWeek;
        $this->freeWithdrawCountPerWeek = $freeWithdrawCountPerWeek;
        $this->scale = $scale;
    }

    public function calculate(Transaction $transaction): string
    {
        $user = $transaction->user;
        $operation = $transaction->operation;
        list($weekStart, $weekEnd) = $this->rangeCalculator->getRange($operation->date);
        list($perWeekAmount, $perWeekCount) = $this->dataProvider->getTotalAmountAndTransactionCount(
            $user->id, $operation->type, $weekStart, $weekEnd
        );
        $freeAmountPerWeekAfterConversion = bcmul($this->freeAmountPerWeek, $operation->rate, $this->scale);
        $perWeekAmountAfterConversion = bcmul($perWeekAmount, $operation->rate, $this->scale);

        if (
            $perWeekAmountAfterConversion > $freeAmountPerWeekAfterConversion
            || $perWeekCount >= $this->freeWithdrawCountPerWeek
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

        $commission = bcmul($amountForFee, $this->fee, $this->scale);

        return bcdiv($commission, '100', $this->scale);
    }
}
