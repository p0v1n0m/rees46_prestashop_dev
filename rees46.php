<?php
/**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 *  @author    p0v1n0m <ay@rees46.com>
 *  @copyright 2007-2016 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Rees46 extends Module
{
    protected static $fields = array(
        'REES46_STORE_ID',
        'REES46_SECRET_KEY',
        'REES46_LOG',
        'REES46_XML_STATUS',
        'REES46_XML_CURRENCY',
        'REES46_XML_URL',
        'REES46_ORDER_CREATED',
        'REES46_ORDER_COMPLETED',
        'REES46_ORDER_CANCELLED',
        'REES46_CUSTOMER_COUNTRY',
        'REES46_CUSTOMER_NEWSLETTER',
        'REES46_CUSTOMER_OPTIN',
    );

    public function __construct()
    {
        $this->name = 'rees46';
        $this->tab = 'front_office_features';
        $this->version = '2.0.0';
        $this->author = 'ay@rees46.com';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->display = 'view';
        $this->meta_title = $this->l('REES46');
        $this->module_key = 'b62df9df084ba63e7aa2ef146fe85c84';

        parent::__construct();

        $this->displayName = $this->l('REES46');
        $this->description = $this->l('Система рекомендаций для вашего магазина.');
        $this->ps_versions_compliancy = array('min' => '1.6.1.0', 'max' => '1.6.99.99');
    }

    public function install()
    {
        if (parent::install()) {
            Configuration::updateValue('REES46_STORE_ID', '');
            Configuration::updateValue('REES46_SECRET_KEY', '');
            Configuration::updateValue('REES46_LOG', '');
            Configuration::updateValue('REES46_XML_STATUS', '');
            Configuration::updateValue('REES46_XML_CURRENCY', '');
            Configuration::updateValue('REES46_XML_URL', 'rees46');
            Configuration::updateValue('REES46_ORDER_CREATED', '');
            Configuration::updateValue('REES46_ORDER_COMPLETED', '');
            Configuration::updateValue('REES46_ORDER_CANCELLED', '');
            Configuration::updateValue('REES46_CUSTOMER_COUNTRY', '');
            Configuration::updateValue('REES46_CUSTOMER_NEWSLETTER', '');
            Configuration::updateValue('REES46_CUSTOMER_OPTIN', '');

            return true;
        } else {
            return false;
        }
    }

    public function uninstall()
    {
        if (parent::uninstall()) {
            foreach (Rees46::$fields as $field) {
                Configuration::deleteByName($field);
            }

            return true;
        } else {
            return false;
        }
    }

    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('submit' . $this->name)) {
            Configuration::updateValue(
                'REES46_STORE_ID',
                Tools::getValue('REES46_STORE_ID')
            );
            Configuration::updateValue(
                'REES46_SECRET_KEY',
                Tools::getValue('REES46_SECRET_KEY')
            );
            Configuration::updateValue(
                'REES46_LOG',
                Tools::getValue('REES46_LOG')
            );
            Configuration::updateValue(
                'REES46_XML_STATUS',
                Tools::getValue('REES46_XML_STATUS')
            );
            Configuration::updateValue(
                'REES46_XML_CURRENCY',
                Tools::getValue('REES46_XML_CURRENCY')
            );
            Configuration::updateValue(
                'REES46_XML_URL',
                Tools::getValue('REES46_XML_URL')
            );
            Configuration::updateValue(
                'REES46_ORDER_CREATED',
                Tools::jsonEncode(Tools::getValue('REES46_ORDER_CREATED'))
            );
            Configuration::updateValue(
                'REES46_ORDER_COMPLETED',
                Tools::jsonEncode(Tools::getValue('REES46_ORDER_COMPLETED'))
            );
            Configuration::updateValue(
                'REES46_ORDER_CANCELLED',
                Tools::jsonEncode(Tools::getValue('REES46_ORDER_CANCELLED'))
            );
            Configuration::updateValue(
                'REES46_CUSTOMER_COUNTRY',
                Tools::getValue('REES46_CUSTOMER_COUNTRY')
            );
            Configuration::updateValue(
                'REES46_CUSTOMER_NEWSLETTER',
                Tools::getValue('REES46_CUSTOMER_NEWSLETTER')
            );
            Configuration::updateValue(
                'REES46_CUSTOMER_OPTIN',
                Tools::getValue('REES46_CUSTOMER_OPTIN')
            );

            $output = $this->displayConfirmation($this->l('Settings updated'));
        }

        return $output.$this->renderForm();
    }

    public function renderForm()
    {
        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
            ),
            'description' => $this->l('For use the module need to register on rees46.com'),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Store Id'),
                    'name' => 'REES46_STORE_ID',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Secret Key'),
                    'name' => 'REES46_SECRET_KEY',
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Logging'),
                    'name' => 'REES46_LOG',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ),
                    ),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'icon' => 'icon-save',
            ),
        );

        $this->fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('Products'),
                'icon' => 'icon-book',
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Status XML Feed'),
                    'name' => 'REES46_XML_STATUS',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ),
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Currency Products in XML Feed'),
                    'name' => 'REES46_XML_CURRENCY',
                    'options' => array(
                        'query' => Currency::getCurrenciesByIdShop((int)Tools::getValue('id_shop')),
                        'id' => 'id_currency',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Url XML Feed'),
                    'name' => 'REES46_XML_URL',
                    'readonly' => true,
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'icon' => 'icon-save',
            ),
        );

        $order_statuses = array();

        foreach (OrderState::getOrderStates((int)$this->context->language->id) as $order_status) {
            $order_statuses[] = array(
                'id' => (int)$order_status['id_order_state'],
                'val' => (int)$order_status['id_order_state'],
                'name' => $order_status['name'],
            );
        }

        $this->fields_form[2]['form'] = array(
            'legend' => array(
                'title' => $this->l('Orders'),
                'icon' => 'icon-shopping-cart',
            ),
            'input' => array(
                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Created Order Status'),
                    'name' => 'REES46_ORDER_CREATED[]',
                    'values' => array(
                        'query' => $order_statuses,
                        'id' => 'id',
                        'name' => 'name',
                    ),
                    'expand' => array(
                        'default' => 'show',
                        'show' => array(
                            'text' => $this->l('show'),
                            'icon' => 'plus-sign-alt',
                        ),
                        'hide' => array(
                            'text' => $this->l('hide'),
                            'icon' => 'minus-sign-alt',
                        ),
                    ),
                ),
                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Completed Order Status'),
                    'name' => 'REES46_ORDER_COMPLETED[]',
                    'values' => array(
                        'query' => $order_statuses,
                        'id' => 'id',
                        'name' => 'name',
                    ),
                    'expand' => array(
                        'default' => 'show',
                        'show' => array(
                            'text' => $this->l('show'),
                            'icon' => 'plus-sign-alt',
                        ),
                        'hide' => array(
                            'text' => $this->l('hide'),
                            'icon' => 'minus-sign-alt',
                        ),
                    ),
                ),
                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Cancelled Order Status'),
                    'name' => 'REES46_ORDER_CANCELLED[]',
                    'values' => array(
                        'query' => $order_statuses,
                        'id' => 'id',
                        'name' => 'name',
                    ),
                    'expand' => array(
                        'default' => 'show',
                        'show' => array(
                            'text' => $this->l('show'),
                            'icon' => 'plus-sign-alt',
                        ),
                        'hide' => array(
                            'text' => $this->l('hide'),
                            'icon' => 'minus-sign-alt',
                        ),
                    ),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'icon' => 'icon-save',
            ),
            'description' => $this->l('Export only once on time initial install module.')
            . $this->l('Will be exported orders over the past six months with selected statuses of above.')
            . $this->l('Please save settings before export.'),
            'buttons' => array(
                array(
                    'href' => '',
                    'title' => $this->l('Export Orders'),
                    'icon' => 'icon-upload',
                ),
            ),
        );

        $countries = array(
            array(
                'id' => 0,
                'name' => $this->l('All countries'),
            ),
        );

        foreach (Country::getCountries($this->context->language->id) as $country) {
            $countries[] = array(
                'id' => $country['id_country'],
                'name' => $country['name'],
            );
        }

        $this->fields_form[3]['form'] = array(
            'legend' => array(
                'title' => $this->l('Customers'),
                'icon' => 'icon-group',
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Customers\' country'),
                    'desc' => $this->l('Filter customers by country.'),
                    'name' => 'REES46_CUSTOMER_COUNTRY',
                    'options' => array(
                        'query' => $countries,
                        'id' => 'id',
                        'name' => 'name',
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Newsletter subscribers'),
                    'desc' => $this->l('Filter customers who have subscribed to the newsletter or not,')
                    . $this->l(' and who have an account or not.'),
                    'name' => 'REES46_CUSTOMER_NEWSLETTER',
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => 0,
                                'name' => $this->l('All subscribers'),
                            ),
                            array(
                                'id' => 1,
                                'name' => $this->l('Subscribers with account'),
                            ),
                            array(
                                'id' => 2,
                                'name' => $this->l('Subscribers without account'),
                            ),
                            array(
                                'id' => 3,
                                'name' => $this->l('Non-subscribers'),
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name',
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Opt-in subscribers'),
                    'desc' => $this->l('Filter customers who have agreed to receive your partners\' offers or not.'),
                    'name' => 'REES46_CUSTOMER_OPTIN',
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => 0,
                                'name' => $this->l('All customers'),
                            ),
                            array(
                                'id' => 2,
                                'name' => $this->l('Opt-in subscribers'),
                            ),
                            array(
                                'id' => 1,
                                'name' => $this->l('Opt-in non-subscribers'),
                            )
                        ),
                        'id' => 'id',
                        'name' => 'name',
                    )
                ),
            ),
            'description' => $this->l('Please save settings before export.'),
            'submit' => array(
                'title' => $this->l('Save'),
                'icon' => 'icon-save',
            ),
            'buttons' => array(
                array(
                    'href' => '',
                    'title' => $this->l('Export Customers'),
                    'icon' => 'icon-upload',
                ),
            ),
        );

        $this->fields_form[4]['form'] = array(
            'legend' => array(
                'title' => $this->l('Web Push'),
                'icon' => 'icon-envelope',
            ),
            'buttons' => array(
                array(
                    'href' => '',
                    'title' => $this->l('Check Necessary Files'),
                    'icon' => 'icon-refresh',
                ),
            ),
        );

        $this->fields_form[5]['form'] = array(
            'legend' => array(
                'title' => $this->l('Recommendations'),
                'icon' => 'icon-eye',
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang =
        Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit' . $this->name;
        $helper->currentIndex =
        $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name
        . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => array(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        foreach (Rees46::$fields as $field) {
            if ('REES46_ORDER_CREATED' == $field
                || 'REES46_ORDER_COMPLETED' == $field
                || 'REES46_ORDER_CANCELLED' == $field) {
                foreach (OrderState::getOrderStates((int)$this->context->language->id) as $order_status) {
                    $helper->tpl_vars['fields_value'][$field . '[]_' . $order_status['id_order_state']] =
                        in_array(
                            $order_status['id_order_state'],
                            Tools::jsonDecode(Configuration::get($field), true)
                        ) ? true : false;
                }
            } else {
                $helper->tpl_vars['fields_value'][$field] = Configuration::get($field);
            }
        }

        return $helper->generateForm($this->fields_form);
    }
}
