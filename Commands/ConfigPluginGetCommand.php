<?php

namespace Shopware\Plugin\K10rDeployment\Command;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PDO;
use Shopware\Commands\ShopwareCommand;
use Shopware\Models\Shop\Shop;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class K10rConfigPluginGetCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('k10r:config:plugin:get');
        $this->setDescription('Display all plugin configuration values.');
        $this->addArgument(
            'pluginName',
            InputArgument::REQUIRED,
            'Plugin Name'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->container->get('models');

        $configData = $this->fetchConfigData($input->getArgument('pluginName'));

        if (empty($configData)) {
            $output->writeln('No configuration available');

            return;
        }

        $shops = $entityManager->getRepository(Shop::class)->findAll();

        $table = new Table($output);
        $table->setHeaders($this->getTableHeader($shops));
        $table->setRows($this->getTableRows($configData, $shops));
        $table->render();
    }

    /**
     * @param Shop[] $shops
     *
     * @return array
     */
    private function getTableHeader(array $shops)
    {
        $header = [
            'Config Element',
        ];

        foreach ($shops as $shop) {
            $header[] = 'Shop ID: ' . $shop->getId();
        }

        $header[] = 'Default Value';

        return $header;
    }

    /**
     * @param string $pluginName
     *
     * @return array
     */
    private function fetchConfigData($pluginName)
    {
        /** @var Connection $connection */
        $connection = $this->container->get('dbal_connection');

        $sql = '
            SELECT ce.name, val.value, val.shop_id, ce.value as defaultValue
            
            FROM s_core_plugins p
            
            INNER JOIN s_core_config_forms cf ON cf.plugin_id = p.id
            INNER JOIN s_core_config_elements ce ON ce.form_id = cf.id
            
            LEFT JOIN s_core_config_values val ON val.element_id = ce.id
            
            WHERE p.name=:pluginName
        ';

        $query = $connection->prepare($sql);
        $query->execute([
            ':pluginName' => $pluginName,
        ]);

        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        $config = [];

        foreach ($result as $element) {
            if (!empty($element['shop_id'])) {
                $value                                         = !empty($element['value']) ? @unserialize($element['value']) : null;
                $config[$element['name']][$element['shop_id']] = $value;
            }

            $defaultValue                        = !empty($element['defaultValue']) ? @unserialize($element['defaultValue']) : null;
            $config[$element['name']]['default'] = $defaultValue;
        }

        return $config;
    }

    /**
     * @param array  $configData
     * @param Shop[] $shops
     *
     * @return array
     */
    private function getTableRows(array $configData, array $shops)
    {
        $rows = [];

        foreach ($configData as $name => $elements) {
            $row = [
                $name,
            ];

            foreach ($shops as $shop) {
                if (!empty($elements[$shop->getId()])) {
                    $row[] = $elements[$shop->getId()];
                } else {
                    $row[] = '';
                }
            }

            if (!empty($elements['default'])) {
                $row[] = $elements['default'];
            }

            $rows[] = $row;
        }

        return $rows;
    }
}
