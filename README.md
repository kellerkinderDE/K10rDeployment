# K10rDeployment - Deployment helper plugin for Shopware 5

K10rDeployment adds some helpful console commands for automatic deployments to Shopware 5.

## Usage
### Install with composer
* Change to your root installation of Shopware
* Run command `composer require k10r/deployment`
* Make sure composer dependencies are installed during your deployment
* Activate the plugin during your deployment using the Shopware command line interface (`php bin/console sw:plugin:reinstall K10rDeployment`)

### Zip/Git installation
* Download the ZIP or clone this repository into your `engine/Shopware/Plugins/Local/Core/` folder.
* Activate the plugin during your deployment using the Shopware command line interface (`php bin/console sw:plugin:reinstall K10rDeployment`)
* You can now use any of the commands listed below

## Commands
### Compile Theme
Compiles the theme for all shops.

* Usage: `php bin/console k10r:theme:compile`

### Deactivate a Plugin
Deactivates a given plugin.

* Usage: `php bin/console k10r:plugin:deactivate <pluginName>`

### Install, Update and Activate a Plugin
Installs a plugin and updates it, if necessary. This command does not fail, if the plugin is already installed (the default `sw:plugin:install` command fails).

* Usage: `php bin/console k10r:plugin:install [--activate] <pluginName>`

Use the optional `--activate` option to activate the plugin after the installation.

### Update Shop Settings
Updates the settings of a subshop

* Usage/Example: `php bin/console k10r:store:update [--store 1] [--name <NewShopName] [--host new.example.com] [--path /staging] [--title "DEV! Shop"] [--theme MyAwesomeTheme] `

#### Parameters
* `store` : Shop ID of settings to be set, if not set, default-shop will be used
* `name`  : Sets the name of a shop
* `host`  : Sets the hostname of a shop
* `path`  : Sets the path of the shop relativ to the hostname (e.g. new.example.com/__staging__)
* `title` : Sets the title of the shop
* `theme` : Sets the theme of the shop, based on the given template name
* `secure`: Activate SSL on the shop

### Update Theme Options
* Usage/Example: `php bin/console k10r:theme:update --theme MyAweSomeTheme [--shop 1] --setting "text-color" --value "#FF0000"`

#### Parameters
* `theme`  : Name of the theme for settings to be set
* `shop`   : Shop ID of settings to be set, if not set, default-shop will be used
* `setting`: Name of the setting to be set
* `value`  : Value to be set

### Check if a shopware update is necessary
Verifies if an update is needed for the application to be on a requested version. Return code 0 means an update is needed.

* Usage: `php bin/console k10r:update:needed <targetVersion>`

### Update Configuration
* Usage/Example: `php bin/console k10r:config:set [--shop 1] "noaccountdisable" "true"`

#### Parameters
* `shop`   : Shop ID of settings to be set, if not set, default-shop will be used

#### Arguments
* `key`: Name of the configuration element to be set
* `value`  : Value to be set

### Fetch Configuration
Retrieves the plugin configuration and display it inside the console.

* Usage/Example: `php bin/console k10r:config:get pluginName`

#### Arguments
* `pluginName`: Name of the plugin

#### Example Response:
```console
$ ./bin/console k10r:config:get PluginName
+---------------------+-----------------+------------+---------------+
| Config Eement       | Shop ID: 1      | Shop ID: 2 | Default Value |
+---------------------+-----------------+------------+---------------+
| Element Name        | Value           |            |               |
| Other Element       | Other Value     |            |               |
+---------------------+-----------------+------------+---------------+
```

### Clear Cache
Clear specific caches like in backend performance modul
* Usage/Examples: 
    * `php bin/console k10r:clear:cache --all`
    * `php bin/console k10r:clear:cache --frontend`
    * `php bin/console k10r:clear:cache --config --template`

#### Options
* `all`: All caches
* `frontend`: All frontend related caches
* `backend`: All backend related caches
* `config`: Shopware configuration cache
* `template`: Template cache
* `theme`: Theme cache
* `http`: HTTP cache
* `proxy`: Doctrine Annotations and Proxies
* `search`: Search cache
* `router`: SEO URL index

## License
MIT licensed, see `LICENSE.md`
