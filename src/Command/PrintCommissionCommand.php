<?php

declare(strict_types=1);

namespace App\Command;

use App\DataProvider\RateInterface;
use App\DataProvider\TransactionHistoryInterface;
use App\Formatter\Formatter;
use App\Iterator\FileIterator;
use App\Model\Currency;
use App\Service\CommissionCalculator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PrintCommissionCommand extends Command
{
    protected static $defaultName = 'app:print-commission';

    private CommissionCalculator $commissionCalculator;
    private RateInterface $rateDataProvider;
    private TransactionHistoryInterface $transactionHistory;
    private Formatter $formatter;
    private Currency $baseCurrency;

    public function __construct(
        CommissionCalculator $commissionCalculator,
        RateInterface $rateDataProvider,
        TransactionHistoryInterface $transactionHistory,
        Formatter $formatter,
        string $baseCurrency
    ) {
        $this->commissionCalculator = $commissionCalculator;
        $this->rateDataProvider = $rateDataProvider;
        $this->transactionHistory = $transactionHistory;
        $this->formatter = $formatter;
        $this->baseCurrency = Currency::fromString($baseCurrency);

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Read transactions from a file and print commissions.')
            ->setHelp('app:print-commission path/to/file')
            ->addArgument('pathToFile', InputArgument::REQUIRED, 'Path to file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pathToFile = $input->getArgument('pathToFile');
        $transactions = new FileIterator($pathToFile);
        $rates = $this->rateDataProvider->getRates();

        foreach ($transactions as $transaction) {
            $currency = $transaction->getOperation()->getCurrency();
            $transaction = $transaction->withRate($this->resolveRate($currency, $rates));

            $value = $this->commissionCalculator->calculate($transaction);
            $this->transactionHistory->addTransaction($transaction);
            $fmtValue = $this->formatter->formatCurrency($value, $currency->getCode());

            $output->writeln($fmtValue);
        }

        // return this if there was no problem running the command
        // (it's equivalent to returning int(0))
        return Command::SUCCESS;

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;
    }

    private function resolveRate(Currency $currency, array $rates): string
    {
        if ($currency->equals($this->baseCurrency)) {
            return '1';
        }

        return (string) $rates[$currency->getCode()];
    }
}
