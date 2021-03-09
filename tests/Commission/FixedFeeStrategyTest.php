<?php

declare(strict_types=1);

namespace App\Tests\Commission;

use App\Commission\FixedFeeStrategy;
use App\Model\Operation;
use App\Model\Transaction;
use App\Model\User;
use PHPUnit\Framework\TestCase;

class FixedFeeStrategyTest extends TestCase
{
    private FixedFeeStrategy $strategy;

    public function setUp(): void
    {
        $this->strategy = new FixedFeeStrategy('0.03', 4);
    }

    /**
     * @param string $amount
     * @param string $expectation
     *
     * @dataProvider dataProviderForCalculateTesting
     */
    public function testCalculate(string $amount, string $expectation)
    {
        $this->assertEquals(
            $expectation,
            $this->strategy->calculate(
                new Transaction(
                    new User('1', 'business'),
                    new Operation('2014-12-31', 'withdraw', $amount, 'EUR', '1')
                )
            )
        );
    }

    public function dataProviderForCalculateTesting(): array
    {
        return [
            ['200.00', '0.0600'],
            ['10000.00', '3.0000'],
        ];
    }
}
