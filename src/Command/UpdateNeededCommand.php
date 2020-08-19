<?php

namespace K10rDeployment\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateNeededCommand extends Command
{
    protected static $defaultName = 'k10r:update:needed';

    /** @var string */
    private $currentShopwareVersion;

    public function __construct(string $currentShopwareVersion)
    {
        $this->currentShopwareVersion = $currentShopwareVersion;

        parent::__construct(self::$defaultName);
    }

    protected function configure()
    {
        $this
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
        return (int) (version_compare($this->currentShopwareVersion, $input->getArgument('version')) >= 0);
    }
}