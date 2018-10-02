<?php

namespace Shopware\Plugin\K10rDeployment\Command;

use Shopware\Commands\ShopwareCommand;
use Shopware\Components\Theme\Compiler;
use Shopware\Models\Shop\Repository;
use Shopware\Models\Shop\Shop;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class K10rCompileThemeCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('k10r:theme:compile')
            ->setDescription('Compiles the theme for shop.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Repository $repository */
        $repository = $this->container->get('models')->getRepository('Shopware\Models\Shop\Shop');

        /** @var Compiler $compiler */
        $compiler = $this->container->get('theme_compiler');

        foreach ($repository->getActiveShops() as $shop) {
            /** @var Shop $shop */
            $compiler->compile($shop);

            $output->writeln(sprintf('Theme for shop "%s (#%d)" has been compiled.', $shop->getName(), $shop->getId()));
        }
    }
}
