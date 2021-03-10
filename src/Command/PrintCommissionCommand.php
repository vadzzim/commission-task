<?php

declare(strict_types=1);

namespace App\Command;

use App\DataProvider\RangeStrategyDataProviderInterface;
use App\DataProvider\RateInterface;
use App\Exception\FileNotExistsException;
use App\Exception\NoRateException;
use App\Formatter\Formatter;
use App\Iterator\FileContext;
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
    private FileContext $fileContext;
    private string $baseCurrency;

    public function __construct(
        CommissionCalculator $commissionCalculator,
        RateInterface $rateDataProvider,
        RangeStrategyDataProviderInterface $transactionDataProvider,
        FileContext $fileContext,
        Formatter $formatter,
        string $baseCurrency
    ) {
        $this->commissionCalculator = $commissionCalculator;
        $this->rateDataProvider = $rateDataProvider;
        $this->transactionDataProvider = $transactionDataProvider;
        $this->formatter = $formatter;
        $this->fileContext = $fileContext;
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
        try {
            $pathToFile = $input->getArgument('pathToFile');

            if (!file_exists($pathToFile)) {
                throw new FileNotExistsException(sprintf('File "%s" does not exist', $pathToFile));
            }

            $transactions = $this->fileContext->getTransactions($pathToFile);
            $rates = $this->rateDataProvider->getRates();

            foreach ($transactions as $transaction) {
                if (
                    $this->baseCurrency !== $transaction->operation->currency
                    && !isset($rates[$transaction->operation->currency])
                ) {
                    throw new NoRateException(sprintf('No rate for currency "%s"', $transaction->operation->currency));
                }

                $rate = $this->baseCurrency === $transaction->operation->currency
                    ? '1' : (string) $rates[$transaction->operation->currency];
                $transaction->operation->rate = $rate;
                $value = $this->commissionCalculator->calculate($transaction);
                $this->transactionDataProvider->addTransaction($transaction);
                $fmtValue = $this->formatter->formatCurrency($value, $transaction->operation->currency);

                $output->writeln($fmtValue);
            }
        } catch (\Exception $e) {
            $output->writeln($e);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
