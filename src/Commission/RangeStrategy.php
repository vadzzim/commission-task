<?php

declare(strict_types=1);

namespace App\Commission;

use App\DataProvider\TransactionHistoryInterface;
use App\Model\Transaction;

final class RangeStrategy implements CommissionInterface
{
    private RangeCalculatorInterface $rangeCalculator;
    private TransactionHistoryInterface $history;
    private string $fee;
    private string $freeAmountPerWeek;
    private int $freeWithdrawCountPerWeek;
    private int $scale;

    public function __construct(
        RangeCalculatorInterface $rangeCalculator,
        TransactionHistoryInterface $history,
        string $fee,
        string $freeAmountPerWeek,
        int $freeWithdrawCountPerWeek,
        int $scale
    ) {
        $this->rangeCalculator = $rangeCalculator;
        $this->history = $history;
        $this->fee = $fee;
        $this->freeAmountPerWeek = $freeAmountPerWeek;
        $this->freeWithdrawCountPerWeek = $freeWithdrawCountPerWeek;
        $this->scale = $scale;
    }

    public function calculate(Transaction $transaction): string
    {
        $operation = $transaction->getOperation();
        $rate = $transaction->getRate();
        [$weekStart, $weekEnd] = $this->rangeCalculator->getRange($operation->getDate());
        [$perWeekAmount, $perWeekCount] = $this->history->getTotalAmountAndTransactionCount(
            $transaction->getUser()->getId(), $operation->getType(), $weekStart, $weekEnd
        );
        $freeAmountPerWeekAfterConversion = bcmul($this->freeAmountPerWeek, $rate, $this->scale);
        $perWeekAmountAfterConversion = bcmul($perWeekAmount, $rate, $this->scale);

        if (
            bccomp($perWeekAmountAfterConversion, $freeAmountPerWeekAfterConversion, $this->scale) > 0
            || $perWeekCount >= $this->freeWithdrawCountPerWeek
        ) {
            // standard fee
            $amountForFee = $operation->getAmount()->getValue();
        } else {
            $totalAmount = bcadd($perWeekAmountAfterConversion, $operation->getAmount()->getValue(), $this->scale);

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
