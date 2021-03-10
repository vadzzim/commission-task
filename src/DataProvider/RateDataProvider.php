<?php

declare(strict_types=1);

namespace App\DataProvider;

use App\Exception\NoRateException;

class RateDataProvider implements RateInterface
{
    private string $url;
    private array $cache = [];

    public function __construct(string $url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new NoRateException(sprintf('Not valid API url "%s"', $url));
        }

        $this->url = $url;
    }

    public function getRates(): array
    {
        if ($this->cache) {
            return $this->cache;
        }

        $response = file_get_contents($this->url);

        if (false === $response) {
            throw new NoRateException(sprintf('API call failure "%s"', $this->url));
        }

        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        if (!key_exists('rates', $json) || !is_array($json['rates'])) {
            throw new NoRateException(sprintf('API call failure "%s". No "rates" key.', $this->url));
        }

        $this->cache = $json['rates'];

        return $this->cache;
    }
}
