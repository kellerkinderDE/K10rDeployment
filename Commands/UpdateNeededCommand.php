<?php

namespace Shopware\Plugin\K10rDeployment\Command;

use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class K10rUpdateNeededCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('k10r:update:needed')
            ->setDescription('Verifies if an update is needed for the application to be on a requested version. Return code 0 means an update is needed.')
            ->addArgument(
                'version',
                InputArgument::REQUIRED,
                'Version of the requested update.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $currentVersion = $this->container->hasParameter('shopware.release.version')
            ? $this->container->getParameter('shopware.release.version')
            : \Shopware::VERSION;

        return (int) (version_compare($currentVersion, $input->getArgument('version')) >= 0);
    }
}
