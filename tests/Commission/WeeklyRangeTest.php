<?php

declare(strict_types=1);

namespace App\Tests\Commission;

use App\Commission\WeeklyRange;
use PHPUnit\Framework\TestCase;

class WeeklyRangeTest extends TestCase
{
    private WeeklyRange $rangeCalculator;

    public function setUp(): void
    {
        $this->rangeCalculator = new WeeklyRange();
    }

    /**
     * @param string $date
     * @param array $expectation
     *
     * @dataProvider dataProviderForGetRangeTesting
     */
    public function testGetRange(string $date, array $expectation)
    {
        $this->assertEquals(
            $expectation,
            $this->rangeCalculator->getRange($date)
        );
    }

    public function dataProviderForGetRangeTesting(): array
    {
        return [
            ['2014-12-31', ['2014-12-29', '2015-01-04']],
            ['2016-01-06', ['2016-01-04', '2016-01-10']],
            ['2016-02-15', ['2016-02-15', '2016-02-21']],
        ];
    }
}
