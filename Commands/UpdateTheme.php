<?php

namespace Shopware\Plugin\K10rDeployment\Command;

use Shopware\Commands\ShopwareCommand;
use Shopware\Models\Shop\Template;
use Shopware\Models\Shop\TemplateConfig\Element;
use Shopware\Models\Shop\TemplateConfig\Value;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class K10rUpdateThemeCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('k10r:theme:update')
            ->setDescription('Updates settings for theme in a given shop.')
            ->addOption(
                'shop',
                null,
                InputOption::VALUE_OPTIONAL,
                'Shop ID of settings to be set, if not set, default-shop will be used.'
            )
            ->addOption(
                'theme',
                null,
                InputOption::VALUE_REQUIRED,
                'Name of the theme for settings to be set.'
            )
            ->addOption(
                'setting',
                null,
                InputOption::VALUE_REQUIRED,
                'Name of the setting to be set.'
            )
            ->addOption(
                'value',
                null,
                InputOption::VALUE_OPTIONAL,
                'Value to be set.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shopRepository     = $this->container->get('models')->getRepository('Shopware\Models\Shop\Shop');
        $templateRepository = $this->container->get('models')->getRepository('Shopware\Models\Shop\Template');

        if (!$input->getOption('shop')) {
            $shop = $shopRepository->findOneBy(['default' => true]);
        } else {
            $shop = $shopRepository->find((int) $input->getOption('shop'));
        }

        $this->container->get('theme_installer')->synchronize();

        /** @var Template $theme */
        $theme = $templateRepository->findOneBy(['template' => $input->getOption('theme')]);
        if (is_null($theme)) {
            throw new \Exception('Theme "' . $input->getOption('theme') . '" could not be found in database.');
        }

        $name = $input->getOption('setting');

        $elements = $theme->getElements()->filter(function ($element) use ($name) {
            /** @var Element $element */
            return $element->getName() == $name;
        });
        /** @var Element $element */
        $element  = $elements->first();
        $newValue = $this->processValue($input->getOption('value'));

        $allValues = $element->getValues();
        $values    = $allValues->filter(function ($value) use ($shop) {
            /** @var Value $value */
            return $value->getShop() && $value->getShop()->getId() == $shop->getId();
        });
        $value = $values->first();

        if (!$value) {
            $value = new Value();
            $value->setElement($element);
            $value->setShop($shop);
            $allValues->add($value);
        }

        $value->setValue($newValue);

        $output->writeln(sprintf('Setting "%s" for theme "%s" has been set to "%s".', $theme->getName(),
            $element->getName(), $input->getOption('value')));

        $this->container->get('models')->persist($value);
        $this->container->get('models')->flush();
    }

    protected function processValue($value)
    {
        if ($value === '0' || $value === '1') {
            $value = (bool) $value;
        }

        return $value;
    }
}
