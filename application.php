<?php

declare(strict_types=1);

//require __DIR__.'/vendor/autoload.php';

//use App\CommissionTask\Iterator\FileIterator;
//use Symfony\Component\Config\FileLocator;
//use Symfony\Component\DependencyInjection\ContainerBuilder;
//use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
//use Symfony\Component\Console\Application;
//use App\CommissionTask\Command\PrintCommissionCommand;
//
//$containerBuilder = new ContainerBuilder();
//$loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__));
//$loader->load('config/services.yaml');
//
//$application = new Application();
//$application->add(new PrintCommissionCommand());
//$application->run();



use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Dotenv\Dotenv;

if (!in_array(PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
    echo 'Warning: The console should be invoked via the CLI version of PHP, not the '.PHP_SAPI.' SAPI'.PHP_EOL;
}

set_time_limit(0);

//require dirname(__DIR__).'/vendor/autoload.php';
require __DIR__.'/vendor/autoload.php';

if (!class_exists(Application::class) || !class_exists(Dotenv::class)) {
    throw new LogicException('You need to add "symfony/framework-bundle" and "symfony/dotenv" as Composer dependencies.');
}

$kernel = new Kernel('prod', true);
$application = new Application($kernel);
$application->run();
