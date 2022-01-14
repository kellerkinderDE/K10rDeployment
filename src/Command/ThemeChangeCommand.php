<?php declare(strict_types=1);

namespace K10rDeployment\Command;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelCollection;
use Shopware\Storefront\Theme\StorefrontPluginRegistry;
use Shopware\Storefront\Theme\ThemeCompiler;
use Shopware\Storefront\Theme\ThemeEntity;
use Shopware\Storefront\Theme\ThemeService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ThemeChangeCommand extends Command
{
    protected static $defaultName = 'k10r:theme:change';

    /**
     * @var ThemeService
     */
    private $themeService;

    /**
     * @var StorefrontPluginRegistry
     */
    private $pluginRegistry;

    /**
     * @var EntityRepositoryInterface
     */
    private $salesChannelRepository;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var EntityRepositoryInterface
     */
    private $themeRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $themeSalesChannelRepository;

    public function __construct(
        ThemeService $themeService,
        StorefrontPluginRegistry $pluginRegistry,
        EntityRepositoryInterface $salesChannelRepository,
        EntityRepositoryInterface $themeRepository,
        EntityRepositoryInterface $themeSalesChannelRepository
    ) {
        parent::__construct();

        $this->themeService = $themeService;
        $this->pluginRegistry = $pluginRegistry;
        $this->salesChannelRepository = $salesChannelRepository;
        $this->themeRepository = $themeRepository;
        $this->themeSalesChannelRepository = $themeSalesChannelRepository;
        $this->context = Context::createDefaultContext();
    }

    protected function configure(): void
    {
        $this->addArgument('theme-name', InputArgument::REQUIRED, 'Theme name');
        $this->addArgument('saleschannel', InputArgument::OPTIONAL, 'ID of the Sales Channel you want to assign the theme to. Will be ignored if --all is set.');
        $this->addOption('all', null, InputOption::VALUE_NONE, 'Set theme for all sales channel');
        $this->addOption('no-compile', null, InputOption::VALUE_NONE, 'Do not compile theme after change');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        /** @var SalesChannelCollection $salesChannels */
        $salesChannels = $this->salesChannelRepository->search(new Criteria(), $this->context)->getEntities();

        if (!$input->getOption('all')) {
            $salesChannelId = $input->getArgument('saleschannel');
            if ($salesChannelId === null) {
                $this->io->error('No Sales Channel ID given. Please provide an id or add --all option.');

                return 1;
            }

            $salesChannel = $salesChannels->get($salesChannelId);

            if ($salesChannel === null) {
                $this->io->error('Sales Channel ID unknown');

                return 1;
            }

            $salesChannels = new SalesChannelCollection([$salesChannel]);
        }

        if (!$input->getArgument('theme-name')) {
            $this->io->error('No theme name given. Please provide the technical name of the theme.');

            return 1;
        } else {
            $themeName = $input->getArgument('theme-name');
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('technicalName', $themeName));

        $themes = $this->themeRepository->search($criteria, $this->context);

        foreach ($salesChannels as $salesChannel) {
            $this->io->writeln(sprintf('Set "%s" as new theme for sales channel "%s"', $themeName, $salesChannel->getName()));

            if ($themes->count() === 0) {
                $this->io->error(sprintf('No theme found with the technical name %s.', $themeName));

                return 1;
            }

            /** @var ThemeEntity $theme */
            $theme = $themes->first();
            $this->themeSalesChannelRepository->upsert([[
                                                            'themeId' => $theme->getId(),
                                                            'salesChannelId' => $salesChannel->getId(),
                                                        ]], $this->context);

            if (!$input->getOption('no-compile')) {
                $this->io->writeln(sprintf('Compiling theme %s for sales channel %s', $theme->getId(), $theme->getName()));

                $this->themeService->compileTheme($salesChannel->getId(), $theme->getId(), $this->context);
            }
        }

        return 0;
    }
}
