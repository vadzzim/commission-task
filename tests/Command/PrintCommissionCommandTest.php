<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\PrintCommissionCommand;
use App\Tests\TestContainer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * End to end run of the sample input from the task, on the real container
 * wiring and with the rate API replaced by a mock.
 */
class PrintCommissionCommandTest extends TestCase
{
    private const RATES = '{"base":"EUR","rates":{"USD":1.1497,"JPY":129.53}}';

    /** @var string[] */
    private array $files = [];

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            @unlink($file);
        }

        $this->files = [];
    }

    public function testPrintsCommissionsForTheSampleInput()
    {
        $tester = $this->commandTester(new MockHttpClient(new MockResponse(self::RATES)));

        $exitCode = $tester->execute(['pathToFile' => __DIR__.'/../../assets/input.csv']);

        $this->assertSame(0, $exitCode);
        $this->assertSame(
            [
                '0.60',
                '3.00',
                '0.00',
                '0.06',
                '1.50',
                '0',
                '0.70',
                '0.30',
                '0.30',
                '3.00',
                '0.00',
                '0.00',
                '8612',
            ],
            $this->lines($tester->getDisplay())
        );
    }

    public function testFailsWhenTheRateApiIsUnreachable()
    {
        $tester = $this->commandTester(new MockHttpClient(new MockResponse('', ['http_code' => 500])));

        $exitCode = $tester->execute(['pathToFile' => __DIR__.'/../../assets/input.csv']);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('NoRateException', $tester->getDisplay());
    }

    /**
     * Input entirely in the base currency needs no conversion, so a broken rate
     * API must not stop it from being processed.
     */
    public function testDoesNotTouchTheRateApiForBaseCurrencyOnlyInput()
    {
        $client = new MockHttpClient(static function (): MockResponse {
            throw new class('rate api is down') extends \RuntimeException implements TransportExceptionInterface {
            };
        });
        $tester = $this->commandTester($client);

        $exitCode = $tester->execute(['pathToFile' => $this->csv(
            "2016-01-05,1,private,deposit,200.00,EUR\n2016-01-06,2,business,withdraw,300.00,EUR\n"
        )]);

        $this->assertSame(0, $exitCode);
        $this->assertSame(['0.06', '1.50'], $this->lines($tester->getDisplay()));
        $this->assertSame(0, $client->getRequestsCount());
    }

    public function testFailsWhenTheFileDoesNotExist()
    {
        $tester = $this->commandTester(new MockHttpClient(new MockResponse(self::RATES)));

        $exitCode = $tester->execute(['pathToFile' => __DIR__.'/../../assets/nope.csv']);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('FileNotExistsException', $tester->getDisplay());
    }

    private function csv(string $content): string
    {
        $file = tempnam(sys_get_temp_dir(), 'commission-command-test').'.csv';
        $this->files[] = $file;
        file_put_contents($file, $content);

        return $file;
    }

    private function commandTester(MockHttpClient $httpClient): CommandTester
    {
        $container = TestContainer::build([PrintCommissionCommand::class], $httpClient);

        $application = new Application();
        $application->add($container->get(PrintCommissionCommand::class));

        return new CommandTester($application->find('app:print-commission'));
    }

    /**
     * Formatting details (the currency symbol and the non breaking space that
     * NumberFormatter puts in front of the amount) are covered by FormatterTest;
     * here only the commission values matter.
     *
     * @return string[]
     */
    private function lines(string $display): array
    {
        $lines = array_map(static function (string $line): string {
            return preg_replace('/^[\s\x{00A0}]+|[\s\x{00A0}]+$/u', '', $line);
        }, explode("\n", $display));

        return array_values(array_filter($lines, static function (string $line): bool {
            return '' !== $line;
        }));
    }
}
