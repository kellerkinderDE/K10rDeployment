<?php

namespace Shopware\Plugin\K10rDeployment\Command;

use Shopware\Commands\ShopwareCommand;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Config\Value;
use Shopware\Models\Shop\Shop;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class K10rConfigSetCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('k10r:config:set')
            ->setDescription('Sets non-plugin-specific configuration values.')
            ->addArgument(
                'key',
                InputArgument::REQUIRED,
                'Configuration key.'
            )
            ->addArgument(
                'value',
                InputArgument::REQUIRED,
                'Configuration value. Can be true, false, null, an integer or an array specified with brackets: [value,anothervalue]. Everything else will be interpreted as string.'
            )
            ->addOption(
                'shop',
                null,
                InputOption::VALUE_OPTIONAL,
                'Set configuration for shop id'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ModelManager $em */
        $em = $this->container->get('models');

        if ($input->getOption('shop')) {
            $shop = $em->getRepository('Shopware\Models\Shop\Shop')->find($input->getOption('shop'));
            if (!$shop) {
                $output->writeln(sprintf('Could not find shop with id %s.', $input->getOption('shop')));

                return 1;
            }
        } else {
            $shop = $em->getRepository('Shopware\Models\Shop\Shop')->findOneBy(['default' => true]);
        }

        $rawValue = $input->getArgument('value');
        $value    = $this->castValue($rawValue);

        if (preg_match('/^\[(.+,?)*\]$/', $value, $matches) && count($matches) == 2) {
            $value = explode(',', $matches[1]);
            $value = array_map(
                function ($val) {
                    return $this->castValue($val);
                },
                $value
            );
        }

        $this->saveElement($input->getArgument('key'), $value, $shop);

        $output->writeln(sprintf('Configuration for key %s saved.', $input->getArgument('key')));
    }

    /**
     * Casts a given string into the proper type.
     * Works only for some types, see return.
     *
     * @param $value
     *
     * @return null|bool|int|string
     */
    private function castValue($value)
    {
        if ($value === 'null') {
            return null;
        }
        if ($value === 'false') {
            return false;
        }
        if ($value === 'true') {
            return true;
        }
        if (preg_match('/^\d+$/', $value)) {
            return intval($value);
        }

        return $value;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param Shop   $shop
     */
    private function saveElement($key, $value, $shop)
    {
        $em = $this->container->get('models');

        /** @var $element Element */
        $element = $em->getRepository('Shopware\Models\Config\Element')->findOneBy(['name' => $key]);

        $removedValues = [];
        /** @var Value $oldValue */
        foreach ($element->getValues() as $oldValue) {
            if ($oldValue->getShop() === $shop) {
                $em->remove($oldValue);
                $removedValues[] = $oldValue;
            }
        }
        $em->flush($removedValues);

        $valueModel = new Value();
        $valueModel->setElement($element);
        $valueModel->setShop($shop);
        $valueModel->setValue($value);

        $em->persist($valueModel);
        $em->flush($valueModel);
    }
}
