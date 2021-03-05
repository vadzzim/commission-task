<?php

declare(strict_types=1);

namespace App\CommissionTask\Formatter;

use NumberFormatter;

class Formatter
{
    public function formatCurrency(string $value, string $currency)
    {
        $fmt = new NumberFormatter('us_US', NumberFormatter::CURRENCY);
        $fmt->setAttribute(NumberFormatter::GROUPING_USED, 0);
        $fmt->setAttribute(NumberFormatter::ROUNDING_MODE, NumberFormatter::ROUND_CEILING);

        $fmt->setTextAttribute(NumberFormatter::CURRENCY_CODE, $currency);
        $fmt->setSymbol(NumberFormatter::CURRENCY_SYMBOL, '');

        return $fmt->format($value);
    }
}