<?php

declare(strict_types=1);

namespace App\CommissionTask\Service;

use App\CommissionTask\Commission\RangeStrategyDataProviderInterface;
use App\CommissionTask\DataProvider\RateInterface;
use App\CommissionTask\Formatter\Formatter;

class CommissionPrinter
{
    private CommissionCalculator $commissionCalculator;
    private RateInterface $rateDataProvider;
    private RangeStrategyDataProviderInterface $transactionDataProvider;
    private Formatter $formatter;
    private string $baseCurrency;

    public function __construct(
        CommissionCalculator $commissionCalculator,
        RateInterface $rateDataProvider,
        RangeStrategyDataProviderInterface $transactionDataProvider,
        Formatter $formatter,
        string $baseCurrency //'EUR'
    ) {
        $this->commissionCalculator = $commissionCalculator;
        $this->rateDataProvider = $rateDataProvider;
        $this->transactionDataProvider = $transactionDataProvider;
        $this->formatter = $formatter;
        $this->baseCurrency = $baseCurrency;
    }

    public function print(iterable $transactions): void
    {
        $rates = $this->rateDataProvider->getRates();

        foreach ($transactions as $transaction) {
            $rate = $this->baseCurrency === $transaction->operation->currency
                ? '1' : (string) $rates[$transaction->operation->currency];
            $transaction->operation->rate = $rate;

            $value = $this->commissionCalculator->calculate($transaction);

            $this->transactionDataProvider->addTransaction($transaction);

            $fmtValue = $this->formatter->formatCurrency($value, $transaction->operation->currency);

            echo $fmtValue."\n";
        }
    }
}
