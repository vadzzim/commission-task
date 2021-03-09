<?php

declare(strict_types=1);

namespace App\Service;

use App\Commission\CommissionInterface;
use App\Model\Transaction;

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
        $operationType = $transaction->getOperation()->getType()->getValue();
        $userType = $transaction->getUser()->getType()->getValue();
        $strategy = $operationType.ucfirst($userType).'Strategy';

        if (!property_exists($this, $strategy)) {
            $message = sprintf(
                'Combination operationType "%s" userType "%s" not supported',
                $operationType,
                $userType
            );

            throw new \Exception($message);
        }

        return $this->$strategy->calculate($transaction);
    }
}
