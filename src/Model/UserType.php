<?php

declare(strict_types=1);

namespace App\Model;

/**
 * The project targets PHP 7.4, which has no native enums,
 * so the closed set of user types is modelled as a value object.
 */
final class UserType
{
    public const PRIVATE = 'private';
    public const BUSINESS = 'business';

    private const VALUES = [
        self::PRIVATE,
        self::BUSINESS,
    ];

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        if (!in_array($value, self::VALUES, true)) {
            throw new \InvalidArgumentException(sprintf('Unsupported user type "%s"', $value));
        }

        return new self($value);
    }

    public static function private(): self
    {
        return new self(self::PRIVATE);
    }

    public static function business(): self
    {
        return new self(self::BUSINESS);
    }

    /**
     * @return self[]
     */
    public static function all(): array
    {
        return array_map(static function (string $value): self {
            return new self($value);
        }, self::VALUES);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
