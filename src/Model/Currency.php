<?php

declare(strict_types=1);

namespace App\Model;

final class Currency
{
    private string $code;

    private function __construct(string $code)
    {
        $this->code = $code;
    }

    public static function fromString(string $code): self
    {
        if (1 !== preg_match('/^[A-Z]{3}$/', $code)) {
            throw new \InvalidArgumentException(sprintf('Not a valid currency code "%s"', $code));
        }

        return new self($code);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function equals(self $other): bool
    {
        return $this->code === $other->code;
    }
}
