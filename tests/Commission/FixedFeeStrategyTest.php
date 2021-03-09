<?php

declare(strict_types=1);

namespace App\Tests\Commission;

use App\Commission\FixedFeeStrategy;
use App\Model\Amount;
use App\Model\Currency;
use App\Model\Operation;
use App\Model\OperationType;
use App\Model\Transaction;
use App\Model\User;
use App\Model\UserType;
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
                    new User('1', UserType::business()),
                    new Operation(
                        '2014-12-31',
                        OperationType::withdraw(),
                        Amount::fromString($amount),
                        Currency::fromString('EUR')
                    )
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

    /**
     * Two policies sharing the implementation class must stay independent.
     */
    public function testInstancesAreIndependent()
    {
        $other = new FixedFeeStrategy('0.5', 4);

        $transaction = new Transaction(
            new User('1', UserType::business()),
            new Operation(
                '2014-12-31',
                OperationType::withdraw(),
                Amount::fromString('200.00'),
                Currency::fromString('EUR')
            )
        );

        $this->assertEquals('1.0000', $other->calculate($transaction));
        $this->assertEquals('0.0600', $this->strategy->calculate($transaction));
    }
}
