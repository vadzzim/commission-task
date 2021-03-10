<?php

declare(strict_types=1);

namespace App\Service;

use App\Commission\StrategyContext;
use App\Exception\OperationUserException;
use App\Exception\OptionException;
use App\Model\Transaction;

class CommissionCalculator
{
    const POSSIBLE_STRATEGIES = [
        'depositPrivate',
        'depositBusiness',
        'withdrawPrivate',
        'withdrawBusiness',
    ];

    private array $strategies = [];

    public function __construct(StrategyContext $strategyContext, array $data)
    {
        foreach (self::POSSIBLE_STRATEGIES as $key) {
            if (!array_key_exists($key, $data)) {
                throw new OptionException(sprintf('CommissionCalculator $data should have key "%s"', $key));
            }

            $options = $data[$key];
            $this->strategies[$key] = $strategyContext->getStrategy($options['strategy']);
            $this->strategies[$key]->setOptions($options);
        }
    }

    public function calculate(Transaction $transaction): string
    {
        $strategy = $transaction->operation->type.ucfirst($transaction->user->type);

        if (!array_key_exists($strategy, $this->strategies)) {
            $message = sprintf(
                'Combination OperationType "%s" and UserType "%s" not supported',
                $transaction->operation->type,
                $transaction->user->type
            );

            throw new OperationUserException($message);
        }

        return $this->strategies[$strategy]->calculate($transaction);
    }
}
