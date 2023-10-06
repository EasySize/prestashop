<?php
/**
* 2007-2020 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2020 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Easysize_sizeadvice extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'easysize_sizeadvice';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Easysize';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Easysize');
        $this->description = $this->l('simplified integration of Easysize');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('actionCartSave') &&
            $this->registerHook('footer') &&
            $this->registerHook('displayProductActions') &&
            $this->registerHook('displayProductButtons');
    }

    public function uninstall()
    {
        Configuration::deleteByName('EASYSIZE_SIZEADVICE_LIVE_MODE');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        if (((bool)Tools::isSubmit('submitEasysize_sizeadviceModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        // $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        // return $output.$this->renderForm();

        return $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitEasysize_sizeadviceModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of the form.
     */
    protected function getConfigForm()
    {
        $id_lang = Context::getContext()->language->id;
        $attributes = AttributeGroup::getAttributesGroups($id_lang);
        $categories = Category::getCategories($id_lang, true, false);

        array_unshift($categories, array('id_category' => -1, 'name' => 'Not available'));

        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Easysize configuration'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'EASYSIZE_SIZEADVICE_SHOP_ID',
                        'label' => $this->l('Easysize Shop ID'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-css3"></i>',
                        'desc' => $this->l('CSS selector for Easysize button placeholder. Use (size_selector) to use size selector'),
                        'name' => 'EASYSIZE_SIZEADVICE_PLACEHOLDER',
                        'label' => $this->l('Easysize placeholder'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-css3"></i>',
                        'desc' => $this->l('CSS selector for cart button'),
                        'name' => 'EASYSIZE_SIZEADVICE_CART_BTN',
                        'label' => $this->l('Add to cart button'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'select',
                        'prefix' => '<i class="icon icon-css3"></i>',
                        'name' => 'EASYSIZE_SIZEADVICE_SIZE_ATTRIBUTE',
                        'label' => $this->l('Size attribute'),
                        'options' => array(
                          'query' => $attributes,
                          'id' => 'id_attribute_group',
                          'name' => 'public_name',
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-css3"></i>',
                        'desc' => $this->l('CSS selector for size selector. Use (size_attribute) to replace with size attribute group id'),
                        'name' => 'EASYSIZE_SIZEADVICE_SIZE_SELECTOR',
                        'label' => $this->l('Size selector'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'textarea',
                        'prefix' => '<i class="icon icon-css3"></i>',
                        'desc' => $this->l('key==value per line to override specific attributes'),
                        'name' => 'EASYSIZE_SIZEADVICE_CONF_OVERRIDE',
                        'label' => $this->l('Easysize conf override'),
                    ),

                    // Categories
                    array(
                        'col' => 3,
                        'type' => 'select',
                        'prefix' => '<i class="icon icon-male"></i>',
                        'name' => 'EASYSIZE_SIZEADVICE_MALE_CAT',
                        'label' => $this->l('Male category identifier'),
                        'options' => array(
                          'query' => $categories,
                          'id' => 'id_category',
                          'name' => 'name',
                        ),
                    ),

                    array(
                        'col' => 3,
                        'type' => 'select',
                        'prefix' => '<i class="icon icon-female"></i>',
                        'name' => 'EASYSIZE_SIZEADVICE_FEMALE_CAT',
                        'label' => $this->l('Female category identifier'),
                        'options' => array(
                          'query' => $categories,
                          'id' => 'id_category',
                          'name' => 'name',
                        ),
                    ),

                    // Custom
                    array(
                        'col' => 3,
                        'type' => 'textarea',
                        'prefix' => '<i class="icon icon-css3"></i>',
                        'desc' => $this->l('Execute custom javascript, e.g How custom size selector works. Modify es_conf object for overrides'),
                        'name' => 'EASYSIZE_SIZEADVICE_CUSTOM_JS',
                        'label' => $this->l('Easysize custom JS'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'EASYSIZE_SIZEADVICE_SHOP_ID' => Configuration::get('EASYSIZE_SIZEADVICE_SHOP_ID', null),
            'EASYSIZE_SIZEADVICE_CART_BTN' => Configuration::get('EASYSIZE_SIZEADVICE_CART_BTN', null),
            'EASYSIZE_SIZEADVICE_PLACEHOLDER' => Configuration::get('EASYSIZE_SIZEADVICE_PLACEHOLDER', null),
            'EASYSIZE_SIZEADVICE_SIZE_ATTRIBUTE' => Configuration::get('EASYSIZE_SIZEADVICE_SIZE_ATTRIBUTE', null),
            'EASYSIZE_SIZEADVICE_SIZE_SELECTOR' => Configuration::get('EASYSIZE_SIZEADVICE_SIZE_SELECTOR', null),
            'EASYSIZE_SIZEADVICE_CONF_OVERRIDE' => Configuration::get('EASYSIZE_SIZEADVICE_CONF_OVERRIDE', ''),
            'EASYSIZE_SIZEADVICE_MALE_CAT' => Configuration::get('EASYSIZE_SIZEADVICE_MALE_CAT', null),
            'EASYSIZE_SIZEADVICE_FEMALE_CAT' => Configuration::get('EASYSIZE_SIZEADVICE_FEMALE_CAT', null),
            'EASYSIZE_SIZEADVICE_CUSTOM_JS' => Configuration::get('EASYSIZE_SIZEADVICE_CUSTOM_JS', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    public function hookHeader()
    {
        $this->context->controller->registerJavascript(
            'easysize-library',
            'https://webapp.easysize.me/web_app_v1.0/js/easysize.js',
            array('server' => 'remote')
        );
    }

    public function hookActionCartUpdateQuantityBefore($data)
    {
    }

    public function hookDisplayProductActions()
    {
        $id_lang = Context::getContext()->language->id;
        $product = $this->context->controller->getProduct();

        $this->context->smarty->assign('easysize_product_stock', $product->getAttributeCombinations($id_lang, false));
        $this->context->smarty->assign('easysize', $this->getConfigFormValues());
        $res = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $res;
    }
}
