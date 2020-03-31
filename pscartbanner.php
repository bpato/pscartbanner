<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

class Pscartbanner extends Module
{
    /**
     * @var array Hooks used
     */
    public $hooks = [
        'displayContentWrapperTop'
    ];

    /**
     * Name of ModuleAdminController used for configuration
     */
    const MODULE_ADMIN_CONTROLLER = 'AdminPsCartBanner';

    const CONFIG_BANNER_CONTENT = 'PSCARTBANNER_CONTENT';
    const CONFIG_BANNER_BORDER_COLOR = 'PSCARTBANNER_BORDER_COLOR';

    public function __construct()
    {
        $this->name = 'pscartbanner';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Cart banner');
        $this->description = $this->l('Adds a banner on cart with customized message.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
    }

    /**
     * @return bool
     */
    public function install()
    {
        return parent::install()
            && $this->registerHook($this->hooks)
            && $this->installTabs()
            && $this->installConfiguration();
    }

    /**
     * Install Tabs
     *
     * @return bool
     */
    public function installTabs()
    {
        if (Tab::getIdFromClassName(static::MODULE_ADMIN_CONTROLLER)) {
            return true;
        }

        $tab = new Tab();
        $tab->class_name = static::MODULE_ADMIN_CONTROLLER;
        $tab->module = $this->name;
        $tab->active = true;
        $tab->id_parent = -1;
        $tab->name = array_fill_keys(
            Language::getIDs(false),
            $this->displayName
        );

        return $tab->add();
    }

    /**
     * @return bool
     */
    public function installConfiguration()
    {
        $langs = Language::getLanguages(false);
        $trads = [];

        foreach ($langs as $lang) {
            if ($lang['code'] == 'fr') {
                $trads[$lang['id_lang']] = "Message à nos clients " . PHP_EOL . " En raison de la cette situation exceptionnelle les délais de préparation et d'expédition de votre commande peuvent être rallongés. N'hésitez pas à grouper vos commandes !";
            } else {
                $trads[$lang['id_lang']] = "Message to our customers " . PHP_EOL . " Due to current circumstances some deliveries may take longer than usual ! Don't hesitate to group your weekly orders !";
            }
        }

        return Configuration::updateValue(static::CONFIG_BANNER_CONTENT, $trads)
            && Configuration::updateValue(static::CONFIG_BANNER_BORDER_COLOR, '#189300');
    }

    /**
     * Uninstall Module
     *
     * @return bool
     */
    public function uninstall()
    {
        return parent::uninstall()
            && $this->uninstallTabs()
            && $this->uninstallConfiguration();
    }

    /**
     * @return bool
     */
    public function uninstallTabs()
    {
        $id_tab = (int) Tab::getIdFromClassName(static::MODULE_ADMIN_CONTROLLER);

        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }

        return true;
    }

    /**
     * @return bool
     */
    public function uninstallConfiguration()
    {
        return Configuration::deleteByName(static::CONFIG_BANNER_CONTENT)
            && Configuration::deleteByName(static::CONFIG_BANNER_BORDER_COLOR);
    }

    /**
     * Redirect to our ModuleAdminController when click on Configure button
     */
    public function getContent()
    {


        Tools::redirectAdmin($this->context->link->getAdminLink(static::MODULE_ADMIN_CONTROLLER));
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function hookDisplayContentWrapperTop(array $params)
    {
        if (isset($this->context->controller->controller_name) && $this->context->controller->controller_name !== 'cart') {
            return '';
        }

        $this->context->smarty->assign([
            'bannerContent' => Configuration::get(static::CONFIG_BANNER_CONTENT, (int) $this->context->language->id),
            'bannerBorderColor' => Configuration::get(static::CONFIG_BANNER_BORDER_COLOR, (int) $this->context->language->id),
        ]);

        return $this->display(__FILE__, '/views/templates/hook/displayContentWrapperTop.tpl');
    }
}