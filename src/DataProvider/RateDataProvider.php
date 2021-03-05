<?php

declare(strict_types=1);

namespace App\CommissionTask\DataProvider;

class RateDataProvider implements RateInterface
{
    private string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function getRates(): array
    {
        $response = file_get_contents($this->url);
        if (false === $response) {
            throw new \Exception(sprintf('No response API "%s"', $this->url));
        }

        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        return $json['rates'];
    }
}
