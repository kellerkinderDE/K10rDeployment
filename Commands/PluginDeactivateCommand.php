<?php

namespace Shopware\Plugin\K10rDeployment\Command;

use Shopware\Bundle\PluginInstallerBundle\Service\InstallerService;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class K10rPluginDeactivateCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('k10r:plugin:deactivate')
            ->setDescription('Deactivates a plugin [extends sw:plugin:deactivate].')
            ->addArgument(
                'plugin',
                InputArgument::REQUIRED,
                'Name of the plugin to be deactivated.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Disable error reporting for shopware menu legacy hack
        set_error_handler(function ($errno, $errstr) {
            if ($errno === E_RECOVERABLE_ERROR
                && stripos($errstr, 'Argument 1 passed to Shopware\Models\Menu\Repository::findOneBy() must be of the type array') === 0
            ) {
                return true;
            }

            return false;
        });

        /** @var InstallerService $pluginManager */
        $pluginManager = $this->container->get('shopware_plugininstaller.plugin_manager');
        $pluginName    = $input->getArgument('plugin');

        $pluginManager->refreshPluginList();

        try {
            $plugin = $pluginManager->getPluginByName($pluginName);
        } catch (\Exception $e) {
            $output->writeln(sprintf('Plugin by name "%s" was not found.', $pluginName));

            return 1;
        }

        if ($plugin->getInstalled() && $plugin->getActive()) {
            $pluginManager->deactivatePlugin($plugin);
            $output->writeln(sprintf('Plugin by name "%s" has been deactivated.', $pluginName));
        } else {
            $output->writeln(sprintf('Plugin by name "%s" is not activated, so no need to deactivate it.', $pluginName));
        }
    }
}
