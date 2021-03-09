<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Commission\FixedFeeStrategy;
use App\Exception\OperationUserException;
use App\Model\Amount;
use App\Model\Currency;
use App\Model\Operation;
use App\Model\OperationType;
use App\Model\Transaction;
use App\Model\User;
use App\Model\UserType;
use App\Service\CommissionCalculator;
use App\Tests\TestContainer;
use PHPUnit\Framework\TestCase;

/**
 * Integration test over the real container wiring from config/services.yaml.
 *
 * A unit test that builds the strategies by hand cannot see policies sharing
 * one strategy instance, which is exactly the failure mode guarded here.
 */
class CommissionCalculatorTest extends TestCase
{
    private CommissionCalculator $calculator;

    public function setUp(): void
    {
        $container = TestContainer::build([CommissionCalculator::class]);
        $this->calculator = $container->get(CommissionCalculator::class);
    }

    /**
     * Each policy keeps its own fee: calculating a business withdrawal must
     * not change what a private deposit costs.
     */
    public function testPoliciesDoNotLeakConfigurationIntoEachOther()
    {
        $depositPrivate = $this->transaction(OperationType::deposit(), UserType::private(), '200.00');
        $withdrawBusiness = $this->transaction(OperationType::withdraw(), UserType::business(), '200.00');

        $this->assertEquals('0.0600', $this->calculator->calculate($depositPrivate));
        $this->assertEquals('1.0000', $this->calculator->calculate($withdrawBusiness));
        $this->assertEquals('0.0600', $this->calculator->calculate($depositPrivate));
    }

    public function testDepositBusinessKeepsItsOwnFee()
    {
        $this->assertEquals(
            '0.0600',
            $this->calculator->calculate($this->transaction(OperationType::deposit(), UserType::business(), '200.00'))
        );
    }

    public function testEveryConfiguredPolicyIsReachable()
    {
        foreach (OperationType::all() as $operationType) {
            foreach (UserType::all() as $userType) {
                $this->assertIsString(
                    $this->calculator->calculate($this->transaction($operationType, $userType, '100.00'))
                );
            }
        }
    }

    public function testRefusesConfigurationWithAMissingPolicy()
    {
        $this->expectException(OperationUserException::class);

        new CommissionCalculator(['deposit.private' => new FixedFeeStrategy('0.03', 4)]);
    }

    private function transaction(OperationType $operationType, UserType $userType, string $amount): Transaction
    {
        return new Transaction(
            new User('1', $userType),
            new Operation(
                '2016-01-05',
                $operationType,
                Amount::fromString($amount),
                Currency::fromString('EUR')
            )
        );
    }
}
