<?php

declare(strict_types=1);

namespace App\CommissionTask\Service;

use App\CommissionTask\Commission\CommissionInterface;
use App\CommissionTask\Model\Transaction;

class CommissionCalculator
{
    private CommissionInterface $depositPrivateStrategy;
    private CommissionInterface $depositBusinessStrategy;
    private CommissionInterface $withdrawPrivateStrategy;
    private CommissionInterface $withdrawBusinessStrategy;

    public function __construct(
        CommissionInterface $depositPrivateStrategy,
        CommissionInterface $depositBusinessStrategy,
        CommissionInterface $withdrawPrivateStrategy,
        CommissionInterface $withdrawBusinessStrategy
    ) {
        $this->depositPrivateStrategy = $depositPrivateStrategy;
        $this->depositBusinessStrategy = $depositBusinessStrategy;
        $this->withdrawPrivateStrategy = $withdrawPrivateStrategy;
        $this->withdrawBusinessStrategy = $withdrawBusinessStrategy;
    }

    public function calculate(Transaction $transaction): string
    {
        $strategy = $transaction->operation->type . ucfirst($transaction->user->type) . 'Strategy';

        if (!property_exists($this, $strategy)) {
            $message = sprintf(
                'Combination operationType "%s" userType "%s" not supported',
                $transaction->operation->type,
                $transaction->user->type
            );

            throw new \Exception($message);
        }

        return $this->$strategy->calculate($transaction);
    }
}
