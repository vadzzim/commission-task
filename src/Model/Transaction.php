<?php

declare(strict_types=1);

namespace App\Model;

class Transaction
{
    public User $user;
    public Operation $operation;

    public function __construct(User $user, Operation $operation)
    {
        $this->user = $user;
        $this->operation = $operation;
    }
}
