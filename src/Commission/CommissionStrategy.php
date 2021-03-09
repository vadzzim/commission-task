<?php

declare(strict_types=1);

namespace App\Commission;

use App\Exception\OptionException;
use App\Model\Transaction;

abstract class CommissionStrategy implements CommissionInterface
{
    protected array $requiredOptions = [];
    protected array $options = [];

    abstract public function calculate(Transaction $transaction): string;

    public function setOptions(array $options): void
    {
        $this->validateOptions($options);
        $this->options = $options;
    }

    protected function validateOptions(array $options): void
    {
        foreach ($this->requiredOptions as $option) {
            if (!key_exists($option, $options)) {
                throw new OptionException(sprintf('Required option "%s" is missed', $option));
            }
        }
    }
}
