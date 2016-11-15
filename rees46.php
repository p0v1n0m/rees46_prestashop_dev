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
        'REES46_LOG_STATUS',
        'REES46_XML_STATUS',
        'REES46_XML_CURRENCY',
        'REES46_XML_URL',
        'REES46_ORDER_CREATED',
        'REES46_ORDER_COMPLETED',
        'REES46_ORDER_CANCELLED',
        'REES46_CUSTOMER_TYPE',
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
        $this->description = $this->l('All-in-one eCommerce marketing and sales automation platform.');
        $this->ps_versions_compliancy = array('min' => '1.6.1.0', 'max' => '1.6.99.99');
    }

    public function install()
    {
        if (parent::install()) {
            foreach (Rees46::$fields as $field) {
                Configuration::updateValue($field, '');
            }

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
        $this->context->controller->addJS($this->_path.'views/js/admin/rees46.js');

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
                'REES46_LOG_STATUS',
                Tools::getValue('REES46_LOG_STATUS')
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
                'https://rees46.com'
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
                'REES46_CUSTOMER_TYPE',
                Tools::getValue('REES46_CUSTOMER_TYPE')
            );

            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }

        //$output .= $this->renderForm().$this->renderFormRecommendations();
        $output .= $this->renderForm();

        return $output;
    }

    public function renderForm()
    {
        $fields_form[0]['form'] = array(
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
                    'name' => 'REES46_LOG_STATUS',
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

        $fields_form[1]['form'] = array(
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

        $fields_form[2]['form'] = array(
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
                . $this->l(' Will be exported orders over the past six months with selected statuses of above.')
                . $this->l(' Please save settings before export.'),
            'buttons' => array(
                array(
                    'title' => $this->l('Export Orders'),
                    'icon' => 'icon-upload',
                    'id' => 'submitExportOrders',
                    'name' => 'submitExportOrders',
                ),
            ),
        );

        $fields_form[3]['form'] = array(
            'legend' => array(
                'title' => $this->l('Customers'),
                'icon' => 'icon-group',
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Customers type'),
                    'name' => 'REES46_CUSTOMER_TYPE',
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => 0,
                                'name' => $this->l('All customers'),
                            ),
                            array(
                                'id' => 1,
                                'name' => $this->l('Newsletter subscribers'),
                            ),
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
                    'title' => $this->l('Export Customers'),
                    'icon' => 'icon-upload',
                    'id' => 'submitExportCustomers',
                    'name' => 'submitExportCustomers',
                ),
            ),
        );

        $fields_form[4]['form'] = array(
            'legend' => array(
                'title' => $this->l('Web Push'),
                'icon' => 'icon-envelope',
            ),
            'buttons' => array(
                array(
                    'title' => $this->l('Check Necessary Files'),
                    'icon' => 'icon-refresh',
                    'id' => 'submitCheckFiles',
                    'name' => 'submitCheckFiles',
                ),
            ),
        );

        $fields_form[5]['form'] = array(
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
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name
            . '&tab_module=' . $this->tab
            . '&module_name='. $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => array(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        foreach (Rees46::$fields as $field) {
            if ('REES46_ORDER' == substr($field, 0, 12)) {
                foreach (OrderState::getOrderStates((int)$this->context->language->id) as $order_status) {
                    $helper->tpl_vars['fields_value'][$field . '[]_' . $order_status['id_order_state']] =
                        in_array($order_status['id_order_state'],
                        Tools::jsonDecode(Configuration::get($field), true)) ? true : false;
                }
            } else {
                $helper->tpl_vars['fields_value'][$field] = Configuration::get($field);
            }
        }

        return $helper->generateForm($fields_form);
    }

    public function renderFormRecommendations()
    {

    }

    public function ajaxProcessExportOrders() {
        $json = array();

        $next = (int)Tools::getValue('next');
        $limit = 100;

        $filter_data = array(
            'start' => ($next - 1) * $limit,
            'limit' => $limit,
        );

        if ($filter_data['start'] < 0) {
            $filter_data['start'] = 0;
        }

        $results_total = (int)$this->getTotalOrders();

        $results = $this->getOrders($filter_data);

        $data = array();

        if (!empty($results)) {
            foreach ($results as $result) {
                $order_products = array();

                $products = $this->getOrderProducts($result['id_order']);

                foreach ($products as $product) {
                    $categories = array();

                    $categories = Product::getProductCategories((int)$product['product_id']);

                    $order_products[] = array(
                        'id' => $product['product_id'],
                        'price' => $product['total_price_tax_incl'],
                        'categories' => $categories,
                        'is_available' => $product['quantity'],
                        'amount' => $product['product_quantity'],
                    );
                }

                $data[] = array(
                    'id' => $result['id_order'],
                    'user_id' => $result['id_customer'],
                    'user_email' => $result['email'],
                    'date' => strtotime($result['date_add']),
                    'items' => $order_products,
                );
            }

            $params['shop_id'] = Configuration::get('REES46_STORE_ID');
            $params['shop_secret'] = Configuration::get('REES46_SECRET_KEY');
            $params['orders'] = $data;

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_URL, 'http://api.rees46.com/import/orders');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, true));

            $return['result'] = curl_exec($ch);
            $return['info'] = curl_getinfo($ch);

            curl_close($ch);

            if ($return['info']['http_code'] < 200 || $return['info']['http_code'] >= 300) {
                $json['error'] = 'Error: No data for export!';

                if (Configuration::get('REES46_LOG_STATUS')) {
                    PrestaShopLogger::addLog('REES46 [error]: Export orders (' . $return['info']['http_code'] . ')' . ' [' . time() . ']', 3);
                }
            } else {
                if ($results_total > $next * $limit) {
                    $json['next'] = $next + 1;

                    $json['success'] = sprintf($this->l('Processing: You have exported %s of %s selected orders into REES46!'), $next * $limit, $results_total);
                } else {
                    $json['success'] = sprintf($this->l('Success: You have exported all %s selected orders into REES46!'), $results_total);

                    if (Configuration::get('REES46_LOG_STATUS')) {
                        PrestaShopLogger::addLog('REES46 [success]: Export orders (' . $results_total . ')' . ' [' . time() . ']', 1);
                    }
                }
            }
        } else {
            $json['error'] = 'Error: No data for export!';
        }

        echo (Tools::jsonEncode($json));
    }

    public function ajaxProcessExportCustomers() {
        $json = array();

        $next = (int)Tools::getValue('next');
        $limit = 100;

        $filter_data = array(
            'start' => ($next - 1) * $limit,
            'limit' => $limit,
        );

        if ($filter_data['start'] < 0) {
            $filter_data['start'] = 0;
        }

        $results_total = (int)$this->getTotalCustomers();

        $results = $this->getCustomers($filter_data);

        $data = array();

        if (!empty($results)) {
            foreach ($results as $result) {
                $data[] = array(
                    'id' => $result['id_customer'],
                    'email' => $result['email'],
                );
            }

            $params['shop_id'] = Configuration::get('REES46_STORE_ID');
            $params['shop_secret'] = Configuration::get('REES46_SECRET_KEY');
            $params['audience'] = $data;

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_URL, 'http://api.rees46.com/import/audience');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, true));

            $return['result'] = curl_exec($ch);
            $return['info'] = curl_getinfo($ch);

            curl_close($ch);

            if ($return['info']['http_code'] < 200 || $return['info']['http_code'] >= 300) {
                $json['error'] = 'Error: No data for export!';

                if (Configuration::get('REES46_LOG_STATUS')) {
                    PrestaShopLogger::addLog('REES46 [error]: Export customers (' . $return['info']['http_code'] . ')' . ' [' . time() . ']', 3);
                }
            } else {
                if ($results_total > $next * $limit) {
                    $json['next'] = $next + 1;

                    $json['success'] = sprintf($this->l('Processing: You have exported %s of %s selected customers into REES46!'), $next * $limit, $results_total);
                } else {
                    $json['success'] = sprintf($this->l('Success: You have exported all %s selected customers into REES46!'), $results_total);

                    if (Configuration::get('REES46_LOG_STATUS')) {
                        PrestaShopLogger::addLog('REES46 [success]: Export customers (' . $results_total . ')' . ' [' . time() . ']', 1);
                    }
                }
            }
        } else {
            $json['error'] = 'Error: No data for export!';
        }

        echo (Tools::jsonEncode($json));
    }

    public function ajaxProcessCheckFiles()
    {
        $json = array();

        $dir = _PS_ROOT_DIR_ . '/';

        $files = array(
            'manifest.json',
            'push_sw.js'
        );

        foreach ($files as $key => $file) {
            if (!is_file($dir . $file)) {
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, 'https://raw.githubusercontent.com/rees46/web-push-files/master/' . $file);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $result = curl_exec($ch);
                $info = curl_getinfo($ch);

                curl_close($ch);

                if ($info['http_code'] < 200 || $info['http_code'] >= 300) {
                    if (Configuration::get('REES46_LOG_STATUS')) {
                        PrestaShopLogger::addLog('REES46 [error]: Not loading file ' . $file . ' (' . $info['http_code'] . ')' . ' [' . time() . ']', 3);
                    }
                } else {
                    file_put_contents($dir . $file, $result);

                    if (Configuration::get('REES46_LOG_STATUS')) {
                        PrestaShopLogger::addLog('REES46 [success]: Loading file ' . $file . ' [' . time() . ']', 1);
                    }
                }
            }

            if (is_file($dir . $file)) {
                $json['success'][$key] = sprintf($this->l('Success: File %s loaded!'), $file);
            } else {
                $json['error'][$key] = sprintf($this->l('Error: You need to load file %s!'), $file);
            }
        }

        echo (Tools::jsonEncode($json));
    }

    private function getTotalOrders()
    {
        $query = new DbQuery();
        $query->select('COUNT(*) AS total');
        $query->from('orders', 'o');
        $query->where('DATE(o.`date_add`) > DATE_SUB(NOW(), INTERVAL 6 MONTH)');

        if (Context::getContext()->cookie->shopContext) {
            $query->where('o.`id_shop` = ' . (int)Context::getContext()->shop->id);
        }

        $rees46_statuses = array();

        $rees46_order_created = Tools::jsonDecode(Configuration::get('REES46_ORDER_CREATED'));
        $rees46_order_completed = Tools::jsonDecode(Configuration::get('REES46_ORDER_COMPLETED'));
        $rees46_order_cancelled = Tools::jsonDecode(Configuration::get('REES46_ORDER_CANCELLED'));

        if ($rees46_order_created) {
            $rees46_statuses = array_merge($rees46_statuses, $rees46_order_created);
        }

        if ($rees46_order_completed) {
            $rees46_statuses = array_merge($rees46_statuses, $rees46_order_completed);
        }

        if ($rees46_order_cancelled) {
            $rees46_statuses = array_merge($rees46_statuses, $rees46_order_cancelled);
        }

        $rees46_statuses = array_unique($rees46_statuses);

        if (!empty($rees46_statuses)) {
            $implode = array();

            foreach ($rees46_statuses as $order_status_id) {
                $implode[] = "o.`current_state` = '" . (int)$order_status_id . "'";
            }

            if ($implode) {
                $query->where(implode(' OR ', $implode));
            }

            $query->orderBy('o.`id_order` ASC');

            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query->build());

            return $result[0]['total'];
        }
    }

    private function getOrders($data = array())
    {
        $query = new DbQuery();
        $query->select('o.`id_order`, o.`id_customer`, c.`email`, o.`current_state`, o.`date_add`');
        $query->from('orders', 'o');
        $query->leftJoin('customer', 'c', 'c.`id_customer` = o.`id_customer`');
        $query->where('DATE(o.`date_add`) > DATE_SUB(NOW(), INTERVAL 6 MONTH)');

        if (Context::getContext()->cookie->shopContext) {
            $query->where('o.`id_shop` = ' . (int)Context::getContext()->shop->id);
        }

        $rees46_statuses = array();

        $rees46_order_created = Tools::jsonDecode(Configuration::get('REES46_ORDER_CREATED'));
        $rees46_order_completed = Tools::jsonDecode(Configuration::get('REES46_ORDER_COMPLETED'));
        $rees46_order_cancelled = Tools::jsonDecode(Configuration::get('REES46_ORDER_CANCELLED'));

        if ($rees46_order_created) {
            $rees46_statuses = array_merge($rees46_statuses, $rees46_order_created);
        }

        if ($rees46_order_completed) {
            $rees46_statuses = array_merge($rees46_statuses, $rees46_order_completed);
        }

        if ($rees46_order_cancelled) {
            $rees46_statuses = array_merge($rees46_statuses, $rees46_order_cancelled);
        }

        $rees46_statuses = array_unique($rees46_statuses);

        if (!empty($rees46_statuses)) {
            $implode = array();

            foreach ($rees46_statuses as $order_status_id) {
                $implode[] = "o.`current_state` = '" . (int)$order_status_id . "'";
            }

            if ($implode) {
                $query->where(implode(' OR ', $implode));
            }

            $query->orderBy('o.`id_order` ASC');

            if (isset($data['start']) || isset($data['limit'])) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1) {
                    $data['limit'] = 20;
                }

                $query->limit((int)$data['start'], (int)$data['limit']);
            }

            return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query->build());
        }
    }

    private function getOrderProducts($id_order)
    {
        $query = new DbQuery();
        $query->select('od.`product_id`, od.`total_price_tax_incl`, od.`product_quantity`, sa.`quantity`');
        $query->from('order_detail', 'od');
        $query->leftJoin('product_shop', 'ps', 'ps.`id_product` = od.`product_id`');
        $query->leftJoin('stock_available', 'sa', 'sa.`id_product` = od.`product_id`');
        $query->where('od.`id_order` = ' . (int)$id_order);
        $query->where('ps.`id_shop` = od.`id_shop`');
        $query->where('sa.`id_product_attribute` = od.`product_attribute_id`');

        if (Context::getContext()->cookie->shopContext) {
            $query->where('od.`id_shop` = ' . (int)Context::getContext()->shop->id);
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query->build());
    }

    private function getTotalCustomers()
    {
        $query = new DbQuery();
        $query->select('COUNT(*) AS total');
        $query->from('customer', 'c');

        if (Configuration::get('REES46_CUSTOMER_TYPE')) {
            $query->where('c.`optin` = ' . (int)Configuration::get('REES46_CUSTOMER_TYPE'));
        }

        if (Context::getContext()->cookie->shopContext) {
            $query->where('c.`id_shop` = ' . (int)Context::getContext()->shop->id);
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query->build());

        return $result[0]['total'];
    }

    private function getCustomers($data = array())
    {
        $query = new DbQuery();
        $query->select('c.`id_customer`, c.`email`');
        $query->from('customer', 'c');

        if (Configuration::get('REES46_CUSTOMER_TYPE')) {
            $query->where('c.`optin` = ' . (int)Configuration::get('REES46_CUSTOMER_TYPE'));
        }

        if (Context::getContext()->cookie->shopContext) {
            $query->where('c.`id_shop` = ' . (int)Context::getContext()->shop->id);
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $query->limit((int)$data['start'], (int)$data['limit']);
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query->build());
    }
}
