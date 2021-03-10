<?php

declare(strict_types=1);

namespace App\Tests\DataProvider;

use App\DataProvider\RateDataProvider;
use App\Exception\NoRateException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class RateDataProviderTest extends TestCase
{
    private const URL = 'https://rates.example.com/latest';

    public function testReturnsRatesFromTheApi()
    {
        $provider = new RateDataProvider(
            new MockHttpClient(new MockResponse('{"base":"EUR","rates":{"USD":1.1497,"JPY":129.53}}')),
            self::URL
        );

        $this->assertSame(['USD' => 1.1497, 'JPY' => 129.53], $provider->getRates());
    }

    public function testCallsTheApiOnlyOnce()
    {
        $client = new MockHttpClient([new MockResponse('{"rates":{"USD":1.1497}}')]);
        $provider = new RateDataProvider($client, self::URL);

        $provider->getRates();
        $provider->getRates();

        $this->assertSame(1, $client->getRequestsCount());
    }

    public function testNetworkFailureBecomesNoRateException()
    {
        $client = new MockHttpClient(static function (): MockResponse {
            throw new class('connection refused') extends \RuntimeException implements TransportExceptionInterface {
            };
        });

        $this->expectException(NoRateException::class);

        (new RateDataProvider($client, self::URL))->getRates();
    }

    /**
     * @param MockResponse $response
     *
     * @dataProvider dataProviderForInvalidResponses
     */
    public function testInvalidResponseBecomesNoRateException(MockResponse $response)
    {
        $this->expectException(NoRateException::class);

        (new RateDataProvider(new MockHttpClient($response), self::URL))->getRates();
    }

    public function dataProviderForInvalidResponses(): array
    {
        return [
            'server error' => [new MockResponse('', ['http_code' => 500])],
            'not found' => [new MockResponse('', ['http_code' => 404])],
            'not a json body' => [new MockResponse('<html>nope</html>')],
            'json without rates' => [new MockResponse('{"base":"EUR"}')],
            'rates is not a map' => [new MockResponse('{"rates":"none"}')],
        ];
    }

    public function testInvalidUrlIsRejectedOnConstruction()
    {
        $this->expectException(NoRateException::class);

        new RateDataProvider(new MockHttpClient(), 'not an url');
    }
}
