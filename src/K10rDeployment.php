<?php

declare(strict_types=1);

namespace K10rDeployment;

use Shopware\Core\Framework\Plugin;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class K10rDeployment extends Plugin
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/DependencyInjection'));
        $loader->load('services.xml');
    }
}
