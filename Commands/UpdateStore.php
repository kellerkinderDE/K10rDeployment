<?php
namespace Shopware\Plugin\K10rDeployment\Command;

use Shopware\Commands\ShopwareCommand;
use Shopware\Models\Shop\Repository;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Shop\Template;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class K10rUpdateStoreCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('k10r:store:update')
            ->setDescription('Updates settings for shop.')
            ->addOption(
                'store',
                null,
                InputOption::VALUE_OPTIONAL,
                'Store ID of settings to be set, if not set, default-store will be used.'
            )
            ->addOption(
                'name',
                null,
                InputOption::VALUE_OPTIONAL,
                'Name to be set.'
            )
            ->addOption(
                'host',
                null,
                InputOption::VALUE_OPTIONAL,
                'Host to be set.'
            )
            ->addOption(
                'path',
                null,
                InputOption::VALUE_OPTIONAL,
                'Path to be set.'
            )
            ->addOption(
                'title',
                null,
                InputOption::VALUE_OPTIONAL,
                'Title to be set.'
            )
            ->addOption(
                'theme',
                null,
                InputOption::VALUE_OPTIONAL,
                'Theme to be set.'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Repository $shopRepository */
        $shopRepository = $this->container->get('models')->getRepository('Shopware\Models\Shop\Shop');
        /** @var Repository $templateRepository */
        $templateRepository = $this->container->get('models')->getRepository('Shopware\Models\Shop\Template');


        if (!$input->getOption('store')) {
            $shop = $shopRepository->findOneBy(['default' => true]);
        } else {
            $shop = $shopRepository->find((int)$input->getOption('store'));
        }

        /** @var Shop $shop */

        if ($input->getOption('title')) {
            $shop->setTitle($input->getOption('title'));
            $output->writeln(sprintf('Title for shop "%s (#%d)" has been set to "%s".', $shop->getName(), $shop->getId(), $shop->getTitle()));
        }

        if ($input->getOption('path')) {
            $shop->setBasePath($input->getOption('path'));
            $output->writeln(sprintf('Path for shop "%s (#%d)" has been set to "%s".', $shop->getName(), $shop->getId(), $shop->getBasePath()));
        }

        if ($input->getOption('host')) {
            $shop->setHost($input->getOption('host'));
            $output->writeln(sprintf('Host for shop "%s (#%d)" has been set to "%s".', $shop->getName(), $shop->getId(), $shop->getHost()));
        }

        if ($input->getOption('name')) {
            $shop->setName($input->getOption('name'));
            $output->writeln(sprintf('Name for shop "%s (#%d)" has been set to "%s".', $shop->getName(), $shop->getId(), $shop->getName()));
        }

        if ($input->getOption('theme')) {
            $this->container->get('theme_installer')->synchronize();

            /** @var Template $theme */
            $theme = $templateRepository->findOneBy(['template' => $input->getOption('theme')]);

            if (is_null($theme)) {
                $theme = $templateRepository->findOneBy(['name' => $input->getOption('theme')]);
            }

            if (is_null($theme)) {
                throw new \Exception('Theme "' . $input->getOption('theme') . '" could not be found in database.');
            }
            $shop->setTemplate($theme);
            $output->writeln(sprintf('Theme for shop "%s (#%d)" has been set to "%s".', $shop->getName(), $shop->getId(), $shop->getTemplate()->getTemplate()));
        }

        $this->container->get('models')->persist($shop);
        $this->container->get('models')->flush();
    }
}
