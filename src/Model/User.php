<?php

declare(strict_types=1);

namespace App\Model;

final class User
{
    private string $id;
    private UserType $type;

    public function __construct(string $id, UserType $type)
    {
        if ('' === $id) {
            throw new \InvalidArgumentException('User id must not be empty');
        }

        $this->id = $id;
        $this->type = $type;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): UserType
    {
        return $this->type;
    }
}
