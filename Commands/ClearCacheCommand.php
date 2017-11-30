<?php

namespace Shopware\Plugin\K10rDeployment\Command;

use Shopware\Commands\ShopwareCommand;
use Shopware\Components\CacheManager;
use Shopware\Kernel;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class K10rClearCacheCommand
 * @package Shopware\Plugin\K10rDeployment\Command
 */
class K10rClearCacheCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $snippet = Shopware()->Snippets();
        $namespace = $snippet->getNamespace('backend/performance/main');
        $this
            ->setName('k10r:clear:cache')
            ->setDescription('Extended clear cache command.')
            ->addOption(
                'all',
                null,
                InputArgument::REQUIRED,
                'All caches'
            )
            ->addOption(
                'config',
                null,
                InputArgument::REQUIRED,
                $namespace->get('form/items/config', 'Shopware configuration cache')
            )
            ->addOption(
                'template',
                null,
                InputArgument::REQUIRED,
                $namespace->get('form/items/frontend/template', 'Template cache')
            )
            ->addOption(
                'theme',
                null,
                InputArgument::REQUIRED,
                $namespace->get('form/items/frontend/theme', 'Theme cache')
            )
            ->addOption(
                'http',
                null,
                InputArgument::REQUIRED,
                $namespace->get('form/items/backend', 'Http-Proxy-Cache')
            )
            ->addOption(
                'proxy',
                null,
                InputArgument::REQUIRED,
                $namespace->get('form/items/proxy', 'Doctrine Annotations and Proxies')
            )
            ->addOption(
                'search',
                null,
                InputArgument::REQUIRED,
                $namespace->get('form/items/search', 'Cache search function')
            )
            ->addOption(
                'router',
                null,
                InputArgument::REQUIRED,
                $namespace->get('form/items/router', 'Index SEO-URLs')
            )
            ->addOption(
                'frontend',
                null,
                InputArgument::REQUIRED,
                'Placeholder for all frontend related caches'
            )
            ->addOption(
                'backend',
                null,
                InputArgument::REQUIRED,
                'Placeholder for all backend related caches'
            )
            ->setHelp('This command flushes specific shopware caches like calling Shopware_Controllers_Backend_Cache::clearCacheAction from backend.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $outputIsVerbose = $output->isVerbose();
        $io = new SymfonyStyle($input, $output);

        /** @var CacheManager $cacheManager */
        $cacheManager = Shopware()->Container()->get('shopware.cache_manager');
        $cacheInstance = $cacheManager->getCoreCache();
        $capabilities = $cacheInstance->getBackend()->getCapabilities();

        $all = $input->getOption('all');
        $config = $input->getOption('config');
        $template = $input->getOption('template');
        $theme = $input->getOption('theme');
        $http = $input->getOption('http');
        $proxy = $input->getOption('proxy');
        $search = $input->getOption('search');
        $router = $input->getOption('router');
        $frontend = $input->getOption('frontend');
        $backend = $input->getOption('backend');


        if (empty($capabilities['tags'])) {
            if ($config || $template) {
                $cacheInstance->clean();
            }
        } else {
            $tags = [];
            if ($all || $config || $backend) {
                $tags[] = 'Shopware_Config';
                $tags[] = 'Shopware_Plugin';
            }
            if ($all || $search) {
                $tags[] = 'Shopware_Modules_Search';
            }
            if ($all || $backend) {
                $tags[] = 'Shopware_Config';
            }
            if ($all || $proxy) {
                $tags[] = 'Shopware_Models';
            }
            if (!empty($tags) && $tags < 7) {
                $cacheInstance->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tags);
            } else {
                $cacheInstance->clean();
            }
        }

        if ($all || $config || $backend || $frontend) {
            $cacheManager->clearConfigCache();
            $io->comment('Clearing config cache.');
        }
        if ($all || $search) {
            $cacheManager->clearSearchCache();
            $io->comment('Clearing search cache.');
        }
        if ($all || $router) {
            $cacheManager->clearRewriteCache();
            $io->comment('Clearing index SEO-URLs cache.');
        }
        if ($all || $template || $backend || $frontend) {
            $cacheManager->clearTemplateCache();
            $io->comment('Clearing template cache.');
        }
        if ($all || $theme || $frontend) {
            $cacheManager->clearThemeCache();
            $io->comment('Clearing theme cache.');
        }
        if ($all || $http || $frontend) {
            $cacheManager->clearHttpCache();
            $io->comment('Clearing HTTP-Cache.');
        }
        if ($all || $proxy) {
            $cacheManager->clearProxyCache();
            $io->comment('Clearing proxy cache.');
            $cacheManager->clearOpCache();
            $io->comment('Clearing Zend OPcache cache.');
        }

        /** @var Kernel $kernel */
        $kernel = $this->getContainer()->get('kernel');

        $io->success(sprintf('Selected caches for the "%s" environment was successfully cleared.', $kernel->getEnvironment()));
    }
}
