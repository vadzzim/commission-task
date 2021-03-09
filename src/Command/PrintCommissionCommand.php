<?php

declare(strict_types=1);

namespace App\Command;

use App\DataProvider\RangeStrategyDataProviderInterface;
use App\DataProvider\RateInterface;
use App\Formatter\Formatter;
use App\Iterator\FileIterator;
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
    private RangeStrategyDataProviderInterface $transactionDataProvider;
    private Formatter $formatter;
    private string $baseCurrency;

    public function __construct(
        CommissionCalculator $commissionCalculator,
        RateInterface $rateDataProvider,
        RangeStrategyDataProviderInterface $transactionDataProvider,
        Formatter $formatter,
        string $baseCurrency
    ) {
        $this->commissionCalculator = $commissionCalculator;
        $this->rateDataProvider = $rateDataProvider;
        $this->transactionDataProvider = $transactionDataProvider;
        $this->formatter = $formatter;
        $this->baseCurrency = $baseCurrency;

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
            $rate = $this->baseCurrency === $transaction->operation->currency
                ? '1' : (string) $rates[$transaction->operation->currency];
            $transaction->operation->rate = $rate;
            $value = $this->commissionCalculator->calculate($transaction);
            $this->transactionDataProvider->addTransaction($transaction);
            $fmtValue = $this->formatter->formatCurrency($value, $transaction->operation->currency);

            $output->writeln($fmtValue);
        }

        // return this if there was no problem running the command
        // (it's equivalent to returning int(0))
        return Command::SUCCESS;

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;
    }
}
