<?php

declare(strict_types=1);

namespace App\CommissionTask\Commission;

class WeeklyRange implements RangeCalculatorInterface
{
    public function getRange(string $date): array
    {
        $ts = strtotime($date);
        $start = (date('w', $ts) === 1) ? $ts : strtotime('Monday this week', $ts);

        return [
            date('Y-m-d', $start),
            date('Y-m-d', strtotime('next sunday', $start)),
        ];
    }
}
