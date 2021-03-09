<?php

declare(strict_types=1);

namespace App\Service;

use App\Commission\CommissionInterface;
use App\Exception\OperationUserException;
use App\Model\OperationType;
use App\Model\Transaction;
use App\Model\UserType;

class CommissionCalculator
{
    /**
     * Each configured policy is its own immutable strategy instance.
     * The key is the business identity of the policy, not the class
     * of the strategy that happens to implement it.
     *
     * @var array<string, CommissionInterface>
     */
    private array $policies = [];

    /**
     * @param iterable<string, CommissionInterface> $policies keyed by "<operationType>.<userType>"
     */
    public function __construct(iterable $policies)
    {
        foreach ($policies as $key => $policy) {
            $this->policies[$key] = $policy;
        }

        foreach (OperationType::all() as $operationType) {
            foreach (UserType::all() as $userType) {
                $key = self::policyKey($operationType, $userType);

                if (!array_key_exists($key, $this->policies)) {
                    throw new OperationUserException(sprintf('Combination OperationType "%s" and UserType "%s" not supported', $operationType->getValue(), $userType->getValue()));
                }
            }
        }
    }

    public function calculate(Transaction $transaction): string
    {
        $key = self::policyKey($transaction->getOperation()->getType(), $transaction->getUser()->getType());

        return $this->policies[$key]->calculate($transaction);
    }

    private static function policyKey(OperationType $operationType, UserType $userType): string
    {
        return $operationType->getValue().'.'.$userType->getValue();
    }
}
