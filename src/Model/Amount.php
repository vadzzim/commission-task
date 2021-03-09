<?php

declare(strict_types=1);

namespace App\Model;

/**
 * A non-negative decimal amount kept as a string, so that it stays
 * exact and can be handed to bcmath without an intermediate float.
 */
final class Amount
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        if (1 !== preg_match('/^\d+(\.\d+)?$/', $value)) {
            throw new \InvalidArgumentException(sprintf('Not a valid amount "%s"', $value));
        }

        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
