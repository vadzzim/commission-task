<?php

declare(strict_types=1);

set_time_limit(0);

require __DIR__.'/vendor/autoload.php';

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Dotenv\Dotenv;

if (!in_array(PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
    echo 'Warning: The console should be invoked via the CLI version of PHP, not the '.PHP_SAPI.' SAPI'.PHP_EOL;
}

if (!class_exists(Application::class) || !class_exists(Dotenv::class)) {
    throw new LogicException('You need to add "symfony/framework-bundle" and "symfony/dotenv" as Composer dependencies.');
}

$kernel = new Kernel('prod', true);
$application = new Application($kernel);
$application->run();
