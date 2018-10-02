<?php

namespace Shopware\Plugin\K10rDeployment\Command;

use Shopware\Bundle\PluginInstallerBundle\Service\InstallerService;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class K10rPluginInstallCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('k10r:plugin:install')
            ->setDescription('Installs a plugin and updates it, if necessary [extends sw:plugin:install].')
            ->addOption(
                'activate',
                null,
                InputOption::VALUE_NONE,
                'Activate plugin after installation.'
            )
            ->addArgument(
                'plugin',
                InputArgument::REQUIRED,
                'Name of the plugin to be installed.'
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

        if ($plugin->getInstalled()) {
            if ($plugin->hasCapabilityUpdate()) {
                $pluginManager->updatePlugin($plugin);
                $output->writeln(sprintf('Plugin by name "%s" has been updated.', $pluginName));
            } else {
                $output->writeln(sprintf('Plugin by name "%s" can not be updated.', $pluginName));
            }
        } else {
            if ($plugin->hasCapabilityInstall()) {
                $pluginManager->installPlugin($plugin);
                $output->writeln(sprintf('Plugin by name "%s" has been installed.', $pluginName));
            } else {
                $output->writeln(sprintf('Plugin by name "%s" can not be installed.', $pluginName));
            }
        }

        if ($input->getOption('activate')) {
            if ($plugin->getInstalled() && $plugin->hasCapabilityEnable()) {
                $pluginManager->activatePlugin($plugin);
                $output->writeln(sprintf('Plugin by name "%s" has been activated.', $pluginName));
            } else {
                $output->writeln(sprintf('Plugin by name "%s" can not be activated.', $pluginName));
            }
        }
    }
}
