<?php

declare(strict_types=1);

namespace App\Tests\Commission;

use App\Commission\RangeStrategy;
use App\Commission\WeeklyRange;
use App\DataProvider\TransactionDataProvider;
use App\Model\Amount;
use App\Model\Currency;
use App\Model\Operation;
use App\Model\OperationType;
use App\Model\Transaction;
use App\Model\User;
use App\Model\UserType;
use PHPUnit\Framework\TestCase;

class RangeStrategyTest extends TestCase
{
    /**
     * @param string $amount
     * @param string $currency
     * @param string $rate
     * @param string $amountPerWeek
     * @param int    $withdrawCountPerWeek
     * @param string $expectation
     *
     * @dataProvider dataProviderForCalculateTesting
     */
    public function testCalculate(string $amount, string $currency, string $rate, string $amountPerWeek, int $withdrawCountPerWeek, string $expectation)
    {
        $rangeCalculator = $this->createMock(WeeklyRange::class);
        $rangeCalculator->method('getRange')
            ->willReturn(['2014-12-29', '2015-01-04']);

        $history = $this->createMock(TransactionDataProvider::class);
        $history->method('getTotalAmountAndTransactionCount')
            ->willReturn([$amountPerWeek, $withdrawCountPerWeek]);

        $strategy = new RangeStrategy($rangeCalculator, $history, '0.3', '1000.00', 3, 4);

        $this->assertEquals(
            $expectation,
            $strategy->calculate(
                new Transaction(
                    new User('1', UserType::private()),
                    new Operation(
                        '2014-12-31',
                        OperationType::withdraw(),
                        Amount::fromString($amount),
                        Currency::fromString($currency)
                    ),
                    $rate
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
            'commission in JPY' => ['3000000', 'JPY', '129.53', '0', 0, '8611.4100'],
        ];
    }
}
