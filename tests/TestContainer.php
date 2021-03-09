<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Compiles the real config/services.yaml so that tests exercise the
 * production wiring instead of a hand made one.
 */
final class TestContainer
{
    /**
     * @param string[] $publicServices service ids the test needs to fetch
     */
    public static function build(array $publicServices, ?HttpClientInterface $httpClient = null): ContainerBuilder
    {
        $container = new ContainerBuilder();
        (new YamlFileLoader($container, new FileLocator(__DIR__.'/../config')))->load('services.yaml');

        // Never let a test reach the real rate API.
        $container->register(HttpClientInterface::class)->setSynthetic(true)->setPublic(true);

        foreach ($publicServices as $id) {
            $container->getDefinition($id)->setPublic(true);
        }

        $container->compile();
        $container->set(HttpClientInterface::class, $httpClient);

        return $container;
    }
}
