<?php

declare(strict_types=1);

namespace App\Tests\Model;

use App\Model\Amount;
use App\Model\Currency;
use App\Model\Operation;
use App\Model\OperationType;
use App\Model\Transaction;
use App\Model\User;
use App\Model\UserType;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    /**
     * @param string $rate
     *
     * @dataProvider dataProviderForValidRates
     */
    public function testAcceptsPlainDecimalRates(string $rate)
    {
        $this->assertSame($rate, $this->transaction()->withRate($rate)->getRate());
    }

    public function dataProviderForValidRates(): array
    {
        return [
            ['1'],
            ['1.1497'],
            ['129.53'],
            ['0.0001'],
        ];
    }

    /**
     * A rate reaches bcmath as a string, and bcmath reads neither scientific
     * notation nor a sign; a zero rate would also divide by zero when the
     * transaction history converts back to the base currency.
     *
     * @param string $rate
     *
     * @dataProvider dataProviderForInvalidRates
     */
    public function testRejectsRatesBcmathCannotRead(string $rate)
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->transaction()->withRate($rate);
    }

    public function dataProviderForInvalidRates(): array
    {
        return [
            'scientific notation' => ['1.0E-5'],
            'float cast of a small rate' => [(string) 0.00001],
            'zero' => ['0'],
            'zero with decimals' => ['0.00'],
            'negative' => ['-1.5'],
            'not a number' => ['abc'],
            'empty' => [''],
        ];
    }

    public function testDefaultsToTheBaseCurrencyRate()
    {
        $this->assertSame('1', $this->transaction()->getRate());
    }

    public function testWithRateLeavesTheOriginalUntouched()
    {
        $transaction = $this->transaction();
        $converted = $transaction->withRate('129.53');

        $this->assertSame('1', $transaction->getRate());
        $this->assertSame('129.53', $converted->getRate());
        $this->assertNotSame($transaction, $converted);
    }

    private function transaction(): Transaction
    {
        return new Transaction(
            new User('1', UserType::private()),
            new Operation(
                '2016-01-05',
                OperationType::withdraw(),
                Amount::fromString('100.00'),
                Currency::fromString('JPY')
            )
        );
    }
}
