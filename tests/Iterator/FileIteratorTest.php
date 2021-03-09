<?php

declare(strict_types=1);

namespace App\Tests\Iterator;

use App\Iterator\FileIterator;
use App\Model\OperationType;
use App\Model\UserType;
use PHPUnit\Framework\TestCase;

class FileIteratorTest extends TestCase
{
    /** @var string[] */
    private array $files = [];

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            @unlink($file);
        }

        $this->files = [];
    }

    public function testReadsEveryRowOfAFileEndingWithANewLine()
    {
        $transactions = iterator_to_array($this->iterate(
            "2014-12-31,4,private,withdraw,1200.00,EUR\n2016-01-10,2,business,deposit,10000.00,EUR\n"
        ));

        $this->assertCount(2, $transactions);

        $first = $transactions[0];
        $this->assertSame('2014-12-31', $first->getOperation()->getDate());
        $this->assertSame('4', $first->getUser()->getId());
        $this->assertTrue($first->getUser()->getType()->equals(UserType::private()));
        $this->assertTrue($first->getOperation()->getType()->equals(OperationType::withdraw()));
        $this->assertSame('1200.00', $first->getOperation()->getAmount()->getValue());
        $this->assertSame('EUR', $first->getOperation()->getCurrency()->getCode());
    }

    public function testSkipsBlankLines()
    {
        $transactions = iterator_to_array($this->iterate(
            "2014-12-31,4,private,withdraw,1200.00,EUR\n\n2016-01-10,2,business,deposit,10000.00,EUR"
        ));

        $this->assertCount(2, $transactions);
    }

    /**
     * @param string $content
     *
     * @dataProvider dataProviderForInvalidRows
     */
    public function testRejectsInvalidRows(string $content)
    {
        $this->expectException(\Exception::class);

        iterator_to_array($this->iterate($content));
    }

    public function dataProviderForInvalidRows(): array
    {
        return [
            'unknown operation type' => ['2016-01-05,1,private,transfer,200.00,EUR'],
            'unknown user type' => ['2016-01-05,1,vip,deposit,200.00,EUR'],
            'amount is not a number' => ['2016-01-05,1,private,deposit,two hundred,EUR'],
            'negative amount' => ['2016-01-05,1,private,deposit,-200.00,EUR'],
            'unknown currency format' => ['2016-01-05,1,private,deposit,200.00,Euro'],
            'wrong date format' => ['05/01/2016,1,private,deposit,200.00,EUR'],
            'impossible date' => ['2016-02-31,1,private,deposit,200.00,EUR'],
            'empty user id' => ['2016-01-05,,private,deposit,200.00,EUR'],
            'too few columns' => ['2016-01-05,1,private,deposit,200.00'],
            'too many columns' => ['2016-01-05,1,private,deposit,200.00,EUR,extra'],
        ];
    }

    private function iterate(string $content): \Traversable
    {
        $file = tempnam(sys_get_temp_dir(), 'csv-iterator-test');
        $this->files[] = $file;
        file_put_contents($file, $content);

        return (new FileIterator($file))->getIterator();
    }
}
