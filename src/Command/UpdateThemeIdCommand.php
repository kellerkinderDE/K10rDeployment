<?php declare(strict_types=1);

namespace K10rDeployment\Command;


use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateThemeIdCommand extends Command
{

    /** @var Connection */
    private $connection;

    protected static $defaultName = 'k10r:theme:set:id';

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('technicalThemeName', InputArgument::REQUIRED, 'The technical name of the theme.')
            ->addArgument('id', InputArgument::REQUIRED, 'The UUID you want to assign to the theme.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $technicalThemeName = $input->getArgument('technicalThemeName');
        $newId = $input->getArgument('id');

        $this->connection->executeQuery(
            'UPDATE `theme` SET `id` = UNHEX(:id) WHERE `technical_name` = :technicalName',
            [
                'id' => $newId,
                'technicalName' => $technicalThemeName
            ]
        );
    }


}