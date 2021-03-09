<?php

declare(strict_types=1);

namespace App\DataProvider;

use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RateDataProvider implements RateInterface
{
    private HttpClientInterface $httpClient;
    private string $url;
    private ?array $cache = null;

    public function __construct(HttpClientInterface $httpClient, string $url)
    {
        $this->httpClient = $httpClient;
        $this->url = $url;
    }

    public function getRates(): array
    {
        if (null !== $this->cache) {
            return $this->cache;
        }

        try {
            // toArray() also turns a non 2xx status and a non JSON body
            // into a Symfony HttpClient exception.
            $payload = $this->httpClient->request('GET', $this->url)->toArray();
        } catch (HttpClientExceptionInterface $e) {
            throw new \Exception(sprintf('API call failure "%s": %s', $this->url, $e->getMessage()), 0, $e);
        }

        $this->cache = $payload['rates'];

        return $this->cache;
    }
}
