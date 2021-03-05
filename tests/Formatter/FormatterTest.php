<?php

declare(strict_types=1);

namespace App\CommissionTask\Tests\Formatter;

use App\CommissionTask\Formatter\Formatter;
use PHPUnit\Framework\TestCase;


class FormatterTest extends TestCase
{
    /**
     * @var Formatter
     */
    private $fmt;

    public function setUp(): void
    {
        $this->fmt = new Formatter();
    }

    /**
     * @param string $value
     * @param string $currency
     * @param string $expectation
     *
     * @dataProvider dataProviderForAddTesting
     */
    public function testFormatCurrency(string $value, string $currency, string $expectation)
    {
        $this->assertEquals(
            $expectation,
            $this->fmt->formatCurrency($value, $currency)
        );
    }

    public function dataProviderForAddTesting(): array
    {
        return [
            'fraction digits' => ['12345.1234', 'EUR', '12345.13'],
            'fraction digits' => ['12345.1234', 'JPY', '12346'],
            'rounded up' => ['0.023', 'EUR', '0.03'],
        ];
    }
}
