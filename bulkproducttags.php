<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Bulkproducttags extends Module
{


    protected $tabs = [
        [
            'name'      => 'TAGS BULK',
            'className' => 'AdminTagsBulk',
            'active'    => 1,

                'childs'    => [
                    [
                        'active'    => 1,
                        'name'      => 'Stats des tags',
                        'className' => 'AdminTagsStat',
                    ],
                ],
        ],

    ];







    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'bulkproducttags';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'issam aboulwafi';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('bulk product tags');
        $this->description = $this->l('add bulk  tags to  bulk products ');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        $this->addTab($this->tabs);
        Configuration::updateValue('BULKPRODUCTTAGS_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('actionAdminControllerSetMedia');
    }

    public function uninstall()
    {
        $this->removeTab($this->tabs);
        Configuration::deleteByName('BULKPRODUCTTAGS_LIVE_MODE');
        
        return parent::uninstall();
    }



    public function addTab($tabs,$id_parent = 0)
    {
        foreach ($tabs as $tab)
        {
            $tabModel             = new Tab();
            $tabModel->module     = $this->name;
            $tabModel->active     = $tab['active'];
            $tabModel->class_name = $tab['className'];
            $tabModel->id_parent  = $id_parent;

            //tab text in each language
            foreach (Language::getLanguages(true) as $lang)
            {
                $tabModel->name[$lang['id_lang']] = $tab['name'];
            }

            $tabModel->add();

            //submenus of the tab
            if (isset($tab['childs']) && is_array($tab['childs']))
            {
                $this->addTab($tab['childs'], Tab::getIdFromClassName($tab['className']));
            }
        }
        return true;
    }





    public function removeTab($tabs)
    {
        foreach ($tabs as $tab)
        {
            $id_tab = (int) Tab::getIdFromClassName($tab["className"]);
            if ($id_tab)
            {
                $tabModel = new Tab($id_tab);
                $tabModel->delete();
            }

            if (isset($tab["childs"]) && is_array($tab["childs"]))
            {
                $this->removeTab($tab["childs"]);
            }
        }

        return true;
    }







    /**
     * Load the configuration form
     */
    public function getContent()
    {
     
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitBulkproducttagsModule')) == true) {


            

            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
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
        $helper->submit_action = 'submitBulkproducttagsModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));

    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'BULKPRODUCTTAGS_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'BULKPRODUCTTAGS_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'BULKPRODUCTTAGS_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
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
            'BULKPRODUCTTAGS_LIVE_MODE' => Configuration::get('BULKPRODUCTTAGS_LIVE_MODE', true),
            'BULKPRODUCTTAGS_ACCOUNT_EMAIL' => Configuration::get('BULKPRODUCTTAGS_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'BULKPRODUCTTAGS_ACCOUNT_PASSWORD' => Configuration::get('BULKPRODUCTTAGS_ACCOUNT_PASSWORD', null),
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

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookActionAdminControllerSetMedia()
    {
        /* Place your code here. */
    }
}
