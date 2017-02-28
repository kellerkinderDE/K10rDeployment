<?php

class Shopware_Plugins_Core_K10rDeployment_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * @var array
     */
    protected $pluginInfo = [];

    /**
     * @var Enlight_Controller_Request_Request
     */
    protected $request;

    /**
     * @return array
     */
    public function getCapabilities()
    {
        return [
            'install'         => true,
            'enable'          => true,
            'update'          => false,
            'secureUninstall' => true,
        ];
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        return [
            'version'     => $this->getVersion(),
            'author'      => $this->getPluginInfo()['author'],
            'label'       => $this->getLabel(),
            'description' => str_replace('%label%', $this->getLabel(), file_get_contents(sprintf('%s/plugin.txt', __DIR__))),
            'copyright'   => $this->getPluginInfo()['copyright'],
            'support'     => $this->getPluginInfo()['support'],
            'link'        => $this->getPluginInfo()['link'],
        ];
    }

    /**
     * @return array
     */
    protected function getPluginInfo()
    {
        if ($this->pluginInfo === []) {
            $file = sprintf('%s/plugin.json', __DIR__);

            if (!file_exists($file) || !is_file($file)) {
                throw new \RuntimeException('The plugin has an invalid version file.');
            }

            $this->pluginInfo = json_decode(file_get_contents($file), true);
        }

        return $this->pluginInfo;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return (string)$this->getPluginInfo()['label']['de'];
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->getPluginInfo()['currentVersion'];
    }

    /**
     * @return bool
     */
    public function install()
    {
        return $this->createEvents();
    }

    /**
     * @param string $oldVersion
     *
     * @return bool
     */
    public function update($oldVersion)
    {
        return $this->createEvents($oldVersion);
    }

    /**
     * @throws \Exception
     */
    public function uninstall()
    {
        throw new \Exception('Sorry, but you cannot uninstall this plugin.');
    }

    /**
     * @return bool
     */
    public function secureUninstall()
    {
        return true;
    }

    /**
     * @param null|string $oldVersion
     *
     * @return bool
     */
    private function createEvents($oldVersion = null)
    {
        $versionClosures = [

            '0.0.1' => function (Shopware_Plugins_Core_K10rDeployment_Bootstrap $bootstrap) {

                $bootstrap->subscribeEvent(
                    'Shopware_Console_Add_Command',
                    'onAddConsoleCommands'
                );

                return true;
            },
            '0.0.2' => function (Shopware_Plugins_Core_K10rDeployment_Bootstrap $bootstrap) {

                $bootstrap->subscribeEvent(
                    'Shopware_Console_Add_Command',
                    'onAddConsoleCommands'
                );

                return true;
            },
        ];

        foreach ($versionClosures as $version => $versionClosure) {
            if ($oldVersion === null || (version_compare($oldVersion, $version, '<') && version_compare($version, $this->getVersion(), '<='))) {
                if (!$versionClosure($this)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function onAddConsoleCommands(Enlight_Event_EventArgs $args)
    {
        require_once __DIR__ . '/Commands/PluginInstallCommand.php';
        require_once __DIR__ . '/Commands/CompileThemeCommand.php';
        require_once __DIR__ . '/Commands/UpdateStore.php';
        require_once __DIR__ . '/Commands/UpdateNeededCommand.php';
        require_once __DIR__ . '/Commands/PluginDeactivateCommand.php';
        require_once __DIR__ . '/Commands/UpdateTheme.php';

        return new Doctrine\Common\Collections\ArrayCollection([
                                                                   new \Shopware\Plugin\K10rDeployment\Command\K10rPluginInstallCommand(),
                                                                   new \Shopware\Plugin\K10rDeployment\Command\K10rCompileThemeCommand(),
                                                                   new \Shopware\Plugin\K10rDeployment\Command\K10rUpdateStoreCommand(),
                                                                   new \Shopware\Plugin\K10rDeployment\Command\K10rUpdateNeededCommand(),
                                                                   new \Shopware\Plugin\K10rDeployment\Command\K10rPluginDeactivateCommand(),
                                                                   new \Shopware\Plugin\K10rDeployment\Command\K10rUpdateThemeCommand(),
                                                               ]);
    }
}
