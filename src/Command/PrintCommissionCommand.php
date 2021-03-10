<?php

declare(strict_types=1);

namespace App\Command;

use App\DataProvider\RateInterface;
use App\DataProvider\TransactionHistoryInterface;
use App\Exception\FileNotExistsException;
use App\Exception\NoRateException;
use App\Formatter\Formatter;
use App\Iterator\FileContext;
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
    private FileContext $fileContext;
    private Currency $baseCurrency;

    public function __construct(
        CommissionCalculator $commissionCalculator,
        RateInterface $rateDataProvider,
        TransactionHistoryInterface $transactionHistory,
        FileContext $fileContext,
        Formatter $formatter,
        string $baseCurrency
    ) {
        $this->commissionCalculator = $commissionCalculator;
        $this->rateDataProvider = $rateDataProvider;
        $this->transactionHistory = $transactionHistory;
        $this->formatter = $formatter;
        $this->fileContext = $fileContext;
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
        try {
            $pathToFile = $input->getArgument('pathToFile');

            if (!file_exists($pathToFile)) {
                throw new FileNotExistsException(sprintf('File "%s" does not exist', $pathToFile));
            }

            $transactions = $this->fileContext->getTransactions($pathToFile);

            foreach ($transactions as $transaction) {
                $currency = $transaction->getOperation()->getCurrency();
                $transaction = $transaction->withRate($this->resolveRate($currency));

                $value = $this->commissionCalculator->calculate($transaction);
                $this->transactionHistory->addTransaction($transaction);
                $fmtValue = $this->formatter->formatCurrency($value, $currency->getCode());

                $output->writeln($fmtValue);
            }
        } catch (\Exception $e) {
            $output->writeln($e);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function resolveRate(Currency $currency): string
    {
        if ($currency->equals($this->baseCurrency)) {
            return '1';
        }

        // Asked for only once a foreign currency actually shows up, so an input
        // entirely in the base currency does not depend on the rate API.
        // The provider caches, so this stays a single call.
        $rates = $this->rateDataProvider->getRates();

        if (!isset($rates[$currency->getCode()])) {
            throw new NoRateException(sprintf('No rate for currency "%s"', $currency->getCode()));
        }

        return (string) $rates[$currency->getCode()];
    }
}
