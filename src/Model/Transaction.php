<?php

declare(strict_types=1);

namespace App\Model;

final class Transaction
{
    private User $user;
    private Operation $operation;

    /**
     * Rate of the operation currency against the base currency.
     * It is not known when the transaction is read from the input,
     * so it is attached later with withRate().
     */
    private string $rate;

    public function __construct(User $user, Operation $operation, string $rate = '1')
    {
        // A plain positive decimal string. is_numeric() would also accept
        // scientific notation such as "1.0E-5" (what a small float becomes when
        // cast to string), which bcmath silently misreads as zero.
        if (1 !== preg_match('/^\d+(\.\d+)?$/', $rate) || 0.0 === (float) $rate) {
            throw new \InvalidArgumentException(sprintf('Not a valid rate "%s"', $rate));
        }

        $this->user = $user;
        $this->operation = $operation;
        $this->rate = $rate;
    }

    public function withRate(string $rate): self
    {
        return new self($this->user, $this->operation, $rate);
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getOperation(): Operation
    {
        return $this->operation;
    }

    public function getRate(): string
    {
        return $this->rate;
    }
}
