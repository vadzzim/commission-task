<?php

declare(strict_types=1);

namespace App\DataProvider;

class RateDataProvider implements RateInterface
{
    private string $url;
    private array $cache = [];

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function getRates(): array
    {
        if ($this->cache) {
            return $this->cache;
        }

        $response = file_get_contents($this->url);

        if (false === $response) {
            throw new \Exception(sprintf('No response API "%s"', $this->url));
        }

        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        $this->cache = $json['rates'];

        return $this->cache;
    }
}
