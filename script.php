<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

use App\CommissionTask\Iterator\FileIterator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

$containerBuilder = new ContainerBuilder();
$loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__));
$loader->load('config/services.yaml');

$filePath = $argv[1];
if (!file_exists($filePath)) {
    die(sprintf('File "%s" does not exists', $filePath));
}

$transactions = new FileIterator($filePath);

$printer = $containerBuilder->get('App\CommissionTask\Service\CommissionPrinter');
$printer->print($transactions);
