<?php

declare(strict_types=1);

namespace App\Tests\Formatter;

use App\Formatter\Formatter;
use PHPUnit\Framework\TestCase;


class FormatterTest extends TestCase
{
    private Formatter $fmt;

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
            'fraction digits 1' => ['12345.1234', 'EUR', '12345.13'],
            'fraction digits 2' => ['12345.1234', 'JPY', '12346'],
            'rounded up' => ['0.023', 'EUR', '0.03'],
        ];
    }
}
