<?php

declare(strict_types=1);

namespace App\CommissionTask\Tests\Commission;

use App\CommissionTask\Model\Transaction;
use App\CommissionTask\Model\Operation;
use App\CommissionTask\Model\User;
use App\CommissionTask\Commission\WeeklyRange;
use App\CommissionTask\Commission\RangeStrategy;
use App\CommissionTask\DataProvider\TransactionDataProvider;
use PHPUnit\Framework\TestCase;

class RangeStrategyTest extends TestCase
{
    /**
     * @param string $amount
     * @param string $currency
     * @param string $rate
     * @param string $amountPerWeek
     * @param int $withdrawCountPerWeek
     * @param string $expectation
     *
     * @dataProvider dataProviderForCalculateTesting
     */
    public function testCalculate(string $amount, string $currency, string $rate, string $amountPerWeek, int $withdrawCountPerWeek, string $expectation)
    {
        $rangeCalculator = $this->createMock(WeeklyRange::class);
        $rangeCalculator->method('getRange')
            ->willReturn(['from', 'to']);

        $dataProvider = $this->createMock(TransactionDataProvider::class);
        $dataProvider->method('getTotalAmountAndTransactionCount')
            ->willReturn([$amountPerWeek, $withdrawCountPerWeek]);

        $strategy = new RangeStrategy($rangeCalculator,$dataProvider, '0.3', '1000.00',3, 4);

        $this->assertEquals(
            $expectation,
            $strategy->calculate(
                new Transaction(
                    new User('1', 'business'),
                    new Operation('2014-12-31', 'withdraw', $amount, $currency, $rate)
                )
            )
        );
    }

    public function dataProviderForCalculateTesting(): array
    {
        return [
            'free of charge 1' => ['200.00', 'EUR', '1', '700.00', 0, '0.0000'],
            'free of charge 2' => ['200.00', 'EUR', '1', '700.00', 1, '0.0000'],
            'free of charge 3' => ['200.00', 'EUR', '1', '700.00', 2, '0.0000'],
            'commission for the exceeded amount 1' => ['1000.00', 'EUR', '1', '1200.00', 1, '3.0000'],
            'commission for the exceeded amount 2' => ['500.00', 'EUR', '1', '700.00', 1, '0.6000'],
            'commission for the exceeded amount 3' => ['1200.00', 'EUR', '1', '0.00', 0, '0.6000'],
            'commission for the exceeded amount 4' => ['200.00', 'EUR', '1', '1000.00', 1, '0.6000'],
            'commission for the exceeded amount 5' => ['200.00', 'EUR', '1', '1000.00', 2, '0.6000'],
            'commission for the exceeded count 1' => ['200.00', 'EUR', '1', '700.00', 3, '0.6000'],
            'commission for the exceeded count 2' => ['200.00', 'EUR', '1', '700.00', 4, '0.6000'],
            'commission in JPY' => ['3000000', 'EUR', '129.53', '0', 0, '8611.4100'],
        ];
    }
}
