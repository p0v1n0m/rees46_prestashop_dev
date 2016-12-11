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

use PrestaShop\PrestaShop\Adapter\Product\ProductDataProvider;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;

class Rees46 extends Module
{
    protected static $fields = array(
        'REES46_STORE_KEY',
        'REES46_SECRET_KEY',
        'REES46_LOG_STATUS',
        'REES46_XML_EXPORTED',
        'REES46_XML_CURRENCY',
        'REES46_XML_CRON',
        'REES46_ORDER_CREATED',
        'REES46_ORDER_COMPLETED',
        'REES46_ORDER_CANCELLED',
        'REES46_CUSTOMER_TYPE',
    );

    protected static $hooks = array(
        'header',
        'actionProductAdd',
        'actionProductUpdate',
        'actionProductDelete',
        'actionCartSave',
        'actionValidateOrder',
        'actionOrderStatusPostUpdate',
        'displayHome',
        'displayLeftColumn',
        'displayRightColumn',
        'displayFooterProduct',
        'displayRightColumnProduct',
        'displayLeftColumnProduct',
        'displayShoppingCartFooter',
        'displayOrderConfirmation',
        'displaySearch',
    );

    protected static $recommends = array(
        'interesting' => 'You may like it',
        'also_bought' => 'Also bought with this product',
        'similar' => 'Similar products',
        'popular' => 'Popular products',
        'see_also' => 'See also',
        'recently_viewed' => 'Recently viewed',
        'buying_now' => 'Right now bought',
        'search' => 'Customers who looked for this product also bought',
    );

    public function __construct()
    {
        $this->name = 'rees46';
        $this->tab = 'front_office_features';
        $this->version = '2.0.0';
        $this->author = 'REES46';
        $this->need_instance = 0;

        $this->bootstrap = true;
        $this->display = 'view';
        $this->meta_title = $this->l('REES46');
        $this->module_key = 'b62df9df084ba63e7aa2ef146fe85c84';

        parent::__construct();

        $this->displayName = $this->l('REES46');
        $this->description = $this->l('eCommerce marketing automation suite.');
        $this->ps_versions_compliancy = array(
            'min' => '1.5.0.0',
            'max' => _PS_VERSION_,
        );
    }

    public function install()
    {
        $this->_clearCache('*');

        if (parent::install() && $this->updateFields() && $this->registerHooks()) {
            return true;
        } else {
            return false;
        }
    }

    public function uninstall()
    {
        $this->_clearCache('*');

        if (parent::uninstall() && $this->deleteFields() && $this->unregisterHooks()) {
            return true;
        } else {
            return false;
        }
    }

    private function updateFields()
    {
        foreach (Rees46::$fields as $field) {
            if (!Configuration::updateValue($field, '')) {
                $this->_errors[] = Tools::displayError('Failed to update value: ' . $field . '.');

                return false;
            }
        }

        return true;
    }

    private function deleteFields()
    {
        foreach (Rees46::$fields as $field) {
            if (!Configuration::deleteByName($field)) {
                $this->_errors[] = Tools::displayError('Failed to delete value: ' . $field . '.');

                return false;
            }
        }

        for ($id_module = 1; $id_module <= Configuration::get('REES46_MODULE_ID'); $id_module++) {
            Configuration::deleteByName('REES46_MODULE_' . $id_module);
        }

        Configuration::deleteByName('REES46_MODULE_ID');

        return true;
    }

    private function registerHooks()
    {
        foreach (Rees46::$hooks as $hook) {
            if (!$this->registerHook($hook)) {
                $this->_errors[] = Tools::displayError('Failed to install hook: ' . $hook . '.');

                return false;
            }
        }

        return true;
    }

    private function unregisterHooks()
    {
        foreach (Rees46::$hooks as $hook) {
            if (!$this->unregisterHook($hook)) {
                $this->_errors[] = Tools::displayError('Failed to uninstall hook: ' . $hook . '.');

                return false;
            }
        }

        return true;
    }

    public function hookActionProductAdd($params)
    {
        $this->_clearCache('*');
    }

    public function hookActionProductUpdate($params)
    {
        $this->_clearCache('*');
    }

    public function hookActionProductDelete($params)
    {
        $this->_clearCache('*');
    }

    public function hookHeader()
    {
        if (Configuration::get('REES46_STORE_KEY') != ''
            && Configuration::get('REES46_SECRET_KEY') != ''
            && $_SERVER['REQUEST_METHOD'] != 'POST'
        ) {
            $js = '<script type="text/javascript">';
            $js .= '(function(r){window.r46=window.r46||function(){(r46.q=r46.q||[]).push(arguments)};';
            $js .= 'var s=document.getElementsByTagName(r)[0],rs=document.createElement(r);rs.async=1;';
            $js .= 'rs.src=\'//cdn.rees46.com/v3.js\';s.parentNode.insertBefore(rs,s);})(\'script\');' . "\n";
            $js .= 'r46(\'init\', \'' . Configuration::get('REES46_STORE_KEY') . '\');' . "\n";

            if ($this->context->customer->isLogged() && (!isset($this->context->cookie->rees46_customer)
                    || (isset($this->context->cookie->rees46_customer)
                    && $this->context->cookie->rees46_customer != $this->context->customer->id))
                ) {
                if ($this->context->customer->id_gender) {
                    $gender = new Gender((int)$this->context->customer->id_gender, $this->context->language->id);

                    if ($gender->type) {
                        $customer_gender = 'f';
                    } else {
                        $customer_gender = 'm';
                    }
                } else {
                    $customer_gender = null;
                }

                if ($this->context->customer->birthday != '0000-00-00') {
                    $customer_birthday = $this->context->customer->birthday;
                } else {
                    $customer_birthday = null;
                }

                $js .= 'r46(\'profile\', \'set\', {';
                $js .= ' id: ' . (int)$this->context->customer->id . ',';
                $js .= ' email: \'' . $this->context->customer->email . '\',';
                $js .= ' gender: \'' . $customer_gender . '\',';
                $js .= ' birthday: \'' . $customer_birthday . '\',';
                $js .= '});' . "\n";

                $this->context->cookie->__set('rees46_customer', $this->context->customer->id);
            } elseif (!$this->context->customer->isLogged() && $this->context->cookie->email) {
                $js .= 'r46(\'profile\', \'set\', {';
                $js .= ' email: \'' . $this->context->customer->email . '\',';
                $js .= '});' . "\n";
            } elseif (!$this->context->customer->isLogged()) {
                unset($this->context->cookie->rees46_customer);
            }

            if (Tools::getValue('id_product')) {
                $product = new Product(
                    (int)Tools::getValue('id_product'),
                    true,
                    $this->context->language->id,
                    $this->context->shop->id
                );

                $img = Product::getCover($product->id);

                if ($product->quantity) {
                    $stock = true;
                } else {
                    $stock = false;
                }

                $image = $this->context->link->getImageLink(
                    $product->link_rewrite[$this->context->language->id],
                    $img['id_image'],
                    'home_default'
                );

                $js .= 'r46(\'track\', \'view\', {';
                $js .= ' id: ' . (int)$product->id . ',';
                $js .= ' stock: ' . (int)$stock . ',';
                $js .= ' price: \'' . $product->getPrice(!Tax::excludeTaxeOption()) . '\',';
                $js .= ' name: \'' . $product->name . '\',';
                $js .= ' categories: ' . Tools::jsonEncode($product->getCategories()) . ',';
                $js .= ' image: \'' . $image . '\',';
                $js .= ' url: \'' . $this->context->link->getProductLink($product->id) . '\',';
                $js .= '});' . "\n";
            }

            if (isset($this->context->cookie->rees46_cart)) {
                $js .= $this->context->cookie->rees46_cart;

                unset($this->context->cookie->rees46_cart);
            }

            if (isset($this->context->cookie->rees46_purchase)) {
                $js .= $this->context->cookie->rees46_purchase;

                unset($this->context->cookie->rees46_purchase);
            }

            $js .= '</script>';

            return $js;
        }
    }

    public function hookActionCartSave()
    {
        if (isset($this->context->cart)
            && Tools::getValue('action') != 'productrefresh'
            && Tools::getValue('id_product') == true
            && Configuration::get('REES46_STORE_KEY') != ''
            && Configuration::get('REES46_SECRET_KEY') != ''
        ) {
            $js = '';
            $product_id = Tools::getValue('id_product');
            $quantity = Tools::getValue('qty');
            $add = Tools::getValue('add');
            $delete = Tools::getValue('delete');
            $op = Tools::getValue('op');

            if ($op && $op == 'up' && $product_id) {
                $js .= 'r46(\'track\', \'cart\', {id: ' . $product_id . ', amount: 1});' . "\n";
            } elseif ($op && $op == 'down' && $product_id) {
                $cart = array();

                foreach ($this->context->cart->getProducts() as $product) {
                    $cart[] = array(
                        'id' => $product['id_product'],
                        'amount' => $product['quantity'],
                    );
                }

                $js .= 'r46(\'track\', \'cart\', ' . Tools::jsonEncode($cart) . ');' . "\n";
            } elseif ($add && $product_id && $quantity) {
                $js .= 'r46(\'track\', \'cart\', {id: ' . $product_id . ', amount: ' . $quantity . '});' . "\n";
            } elseif ($delete && $product_id) {
                $js .= 'r46(\'track\', \'remove_from_cart\', ' . $product_id . ');' . "\n";
            }

            if ($js != '') {
                $this->context->cookie->__set('rees46_cart', $this->context->cookie->rees46_cart . $js);
            }
        }
    }

    public function hookActionValidateOrder($params)
    {
        if (Configuration::get('REES46_STORE_KEY') != ''
            && Configuration::get('REES46_SECRET_KEY') != ''
        ) {
            $js_data = array();

            $js_data['order'] = $params['order']->id;
            $js_data['order_price'] = $params['order']->total_paid;

            foreach ($params['order']->product_list as $order_product) {
                $product = new Product($order_product['id_product'], false);

                $js_data['products'][] = array(
                    'id' => $order_product['id_product'],
                    'price' => $product->getPrice(!Tax::excludeTaxeOption()),
                    'amount' => $order_product['cart_quantity'],
                );
            }

            $js = 'r46(\'track\', \'purchase\', ' . Tools::jsonEncode($js_data) . ');';

            $this->context->cookie->__set('rees46_purchase', $this->context->cookie->rees46_purchase . $js);

            $order_id = $params['order']->id;
            $order_status_id = $params['orderStatus']->id;

            $rees46_order_created = Tools::jsonDecode(Configuration::get('REES46_ORDER_CREATED'), true);
            $rees46_order_completed = Tools::jsonDecode(Configuration::get('REES46_ORDER_COMPLETED'), true);
            $rees46_order_cancelled = Tools::jsonDecode(Configuration::get('REES46_ORDER_CANCELLED'), true);

            if ($rees46_order_created && in_array($order_status_id, $rees46_order_created)) {
                $status = 0;
            } elseif ($rees46_order_completed && in_array($order_status_id, $rees46_order_completed)) {
                $status = 1;
            } elseif ($rees46_order_cancelled && in_array($order_status_id, $rees46_order_cancelled)) {
                $status = 2;
            }

            if (isset($status)) {
                $order_products = array();

                foreach ($params['order']->product_list as $order_product) {
                    $product = new Product($order_product['id_product'], false);

                    $order_products[] = array(
                        'id' => $order_product['id_product'],
                        'price' => Product::getPriceStatic(
                            (int)$order_product['id_product'],
                            true,
                            ($order_product['id_product_attribute']?(int)$order_product['id_product_attribute'] : null),
                            2,
                            null,
                            false,
                            true,
                            1,
                            false,
                            (int)$params['order']->id_customer,
                            (int)$params['order']->id_cart,
                            (int)$params['order']->{Configuration::get('PS_TAX_ADDRESS_TYPE')}
                        ),
                        'categories' => $product->getCategories(),
                        'is_available' => $order_product['in_stock'],
                        'amount' => $order_product['cart_quantity'],
                    );
                }

                $data = array();

                $data[] = array(
                    'id' => $order_id,
                    'user_id' => $params['customer']->id,
                    'user_email' => $params['customer']->email,
                    'date' => strtotime($params['order']->date_add),
                    'items' => $order_products,
                );

                $curl_data = array();

                $curl_data['shop_id'] = Configuration::get('REES46_STORE_KEY');
                $curl_data['shop_secret'] = Configuration::get('REES46_SECRET_KEY');
                $curl_data['orders'] = $data;

                $url = 'http://api.rees46.com/import/orders';

                $return = $this->curl($url, Tools::jsonEncode($curl_data));

                if (Configuration::get('REES46_LOG_STATUS')) {
                    if ($return['info']['http_code'] < 200 || $return['info']['http_code'] >= 300) {
                        if (version_compare(_PS_VERSION_, '1.6', '<')) {
                            Logger::addLog(
                                'REES46: Autoexport order_id [' . $order_id . ']',
                                3,
                                $return['info']['http_code'],
                                null,
                                null,
                                true
                            );
                        } else {
                            PrestaShopLogger::addLog(
                                'REES46: Autoexport order_id [' . $order_id . ']',
                                3,
                                $return['info']['http_code'],
                                null,
                                null,
                                true
                            );
                        }
                    } else {
                        if (version_compare(_PS_VERSION_, '1.6', '<')) {
                            Logger::addLog(
                                'REES46: Autoexport order_id [' . $order_id . ']',
                                1,
                                null,
                                null,
                                null,
                                true
                            );
                        } else {
                            PrestaShopLogger::addLog(
                                'REES46: Autoexport order_id [' . $order_id . ']',
                                1,
                                null,
                                null,
                                null,
                                true
                            );
                        }
                    }
                }
            }
        }
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        if (Configuration::get('REES46_STORE_KEY') != ''
            && Configuration::get('REES46_SECRET_KEY') != ''
        ) {
            $order_id = $params['id_order'];
            $order_status_id = $params['newOrderStatus']->id;

            $rees46_order_created = Tools::jsonDecode(Configuration::get('REES46_ORDER_CREATED'), true);
            $rees46_order_completed = Tools::jsonDecode(Configuration::get('REES46_ORDER_COMPLETED'), true);
            $rees46_order_cancelled = Tools::jsonDecode(Configuration::get('REES46_ORDER_CANCELLED'), true);

            if ($rees46_order_created && in_array($order_status_id, $rees46_order_created)) {
                $status = 0;
            } elseif ($rees46_order_completed && in_array($order_status_id, $rees46_order_completed)) {
                $status = 1;
            } elseif ($rees46_order_cancelled && in_array($order_status_id, $rees46_order_cancelled)) {
                $status = 2;
            }

            if (isset($status)) {
                $data = array();

                $data[] = array(
                    'id' => $order_id,
                    'status' => $order_status_id,
                );

                $curl_data = array();

                $curl_data['shop_id'] = Configuration::get('REES46_STORE_KEY');
                $curl_data['shop_secret'] = Configuration::get('REES46_SECRET_KEY');
                $curl_data['orders'] = $data;

                $url = 'http://api.rees46.com/import/sync_orders';

                $return = $this->curl($url, Tools::jsonEncode($curl_data));

                if (Configuration::get('REES46_LOG_STATUS')) {
                    if ($return['info']['http_code'] < 200 || $return['info']['http_code'] >= 300) {
                        if (version_compare(_PS_VERSION_, '1.6', '<')) {
                            Logger::addLog(
                                'REES46: Autoexport status [' . $order_status_id . '] of order_id [' . $order_id . ']',
                                3,
                                $return['info']['http_code'],
                                null,
                                null,
                                true
                            );
                        } else {
                            PrestaShopLogger::addLog(
                                'REES46: Autoexport status [' . $order_status_id . '] of order_id [' . $order_id . ']',
                                3,
                                $return['info']['http_code'],
                                null,
                                null,
                                true
                            );
                        }
                    } else {
                        if (version_compare(_PS_VERSION_, '1.6', '<')) {
                            Logger::addLog(
                                'REES46: Autoexport status [' . $order_status_id . '] of order_id [' . $order_id . ']',
                                1,
                                null,
                                null,
                                null,
                                true
                            );
                        } else {
                            PrestaShopLogger::addLog(
                                'REES46: Autoexport status [' . $order_status_id . '] of order_id [' . $order_id . ']',
                                1,
                                null,
                                null,
                                null,
                                true
                            );
                        }
                    }
                }
            }
        }
    }

    public function hookDisplayHome($params)
    {
        return $this->getModules('displayHome');
    }

    public function hookDisplayLeftColumn($params)
    {
        return $this->getModules('displayLeftColumn');
    }

    public function hookDisplayRightColumn($params)
    {
        return $this->getModules('displayRightColumn');
    }

    public function hookDisplayRightColumnProduct($params)
    {
        return $this->getModules('displayRightColumnProduct');
    }

    public function hookDisplayLeftColumnProduct($params)
    {
        return $this->getModules('displayLeftColumnProduct');
    }

    public function hookDisplayShoppingCartFooter($params)
    {
        return $this->getModules('displayShoppingCartFooter');
    }

    public function hookDisplayOrderConfirmation($params)
    {
        return $this->getModules('displayOrderConfirmation');
    }

    public function hookDisplaySearch($params)
    {
        return $this->getModules('displaySearch');
    }

    private function getModules($hook)
    {
        if (Configuration::get('REES46_STORE_KEY') != ''
            && Configuration::get('REES46_SECRET_KEY') != ''
            && Configuration::get('REES46_MODULE_ID')
        ) {
            if (Tools::getValue('id_product')) {
                $item = (int)Tools::getValue('id_product');

                $product = new Product($item, true, $this->context->language->id, $this->context->shop->id);

                $category = (int)$product->id_category_default;
            }

            if (Tools::getValue('id_category')) {
                $category = (int)Tools::getValue('id_category');
            }

            if ($this->context->cart->getProducts()) {
                $cart = array();

                foreach ($this->context->cart->getProducts() as $product) {
                    $cart[] = $product['id_product'];
                }
            }

            if (Tools::getValue('s')) {
                $search_query = Tools::getValue('s');
            } elseif (Tools::getValue('search_query')) {
                $search_query = Tools::getValue('search_query');
            }

            $modules = array();

            for ($id_module = 1; $id_module <= Configuration::get('REES46_MODULE_ID'); $id_module++) {
                $settings = Tools::jsonDecode(Configuration::get('REES46_MODULE_' . $id_module), true);

                if ($settings['hook'] == $hook && $settings['status']) {
                    $modules[] = $settings;

                    $css = false;

                    if ($settings['template'] == 'basic') {
                        $css = true;
                    } elseif ($settings['template'] == 'product-list') {
                        $this->context->controller->addCSS(_THEME_CSS_DIR_.'product_list.css');
                    }
                }
            }

            if (!empty($modules)) {
                foreach ($modules as $key => $module) {
                    $params = array();

                    if ($module['limit'] > 0) {
                        $params['limit'] = (int)$module['limit'];
                    } else {
                        $params['limit'] = 6;
                    }

                    $params['discount'] = (int)$module['discount'];

                    $manufacturers = Tools::jsonDecode($module['manufacturers'], true);

                    if (!empty($manufacturers)) {
                        $params['brands'] = array();

                        foreach ($manufacturers as $manufacturer) {
                             $params['brands'][] = Manufacturer::getNameById($manufacturer);
                        }
                    }

                    $manufacturers_exclude = Tools::jsonDecode($module['manufacturers_exclude'], true);

                    if (!empty($manufacturers_exclude)) {
                        $params['exclude_brands'] = array();

                        foreach ($manufacturers_exclude as $manufacturer) {
                            $params['exclude_brands'][] = Manufacturer::getNameById($manufacturer);
                        }
                    }

                    if ($module['type'] == 'interesting') {
                        if (isset($item)) {
                            $params['item'] = $item;
                        }

                        $modules[$key]['params'] = $params;
                    } elseif ($module['type'] == 'also_bought') {
                        if (isset($item)) {
                            $params['item'] = $item;

                            $modules[$key]['params'] = $params;
                        }
                    } elseif ($module['type'] == 'similar') {
                        if (isset($item) && isset($cart)) {
                            $params['item'] = $item;
                            $params['cart'] = $cart;

                            if (isset($category)) {
                                $params['category'] = $category;
                            }

                            $modules[$key]['params'] = $params;
                        }
                    } elseif ($module['type'] == 'popular') {
                        if (isset($category)) {
                            $params['category'] = $category;
                        }

                        $modules[$key]['params'] = $params;
                    } elseif ($module['type'] == 'see_also') {
                        if (isset($cart)) {
                            $params['cart'] = $cart;

                            $modules[$key]['params'] = $params;
                        }
                    } elseif ($module['type'] == 'recently_viewed') {
                        $modules[$key]['params'] = Tools::jsonEncode($params);
                    } elseif ($module['type'] == 'buying_now') {
                        if (isset($item)) {
                            $params['item'] = $item;
                        }

                        if (isset($cart)) {
                            $params['cart'] = $cart;
                        }

                        $modules[$key]['params'] = $params;
                    } elseif ($module['type'] == 'search') {
                        if (isset($search_query)) {
                            $params['search_query'] = $search_query;

                            if (isset($cart)) {
                                $params['cart'] = $cart;
                            }

                            $modules[$key]['params'] = $params;
                        }
                    }

                    $modules[$key]['link'] = $this->context->link->getModuleLink(
                        'rees46',
                        'recommendations',
                        array(),
                        null,
                        (int)$this->context->language->id,
                        (int)$this->context->shop->id
                    );
                }

                uasort($modules, function ($a, $b) {
                    return ($a['position'] - $b['position']);
                });

                $this->context->smarty->assign(
                    array(
                        'rees46_modules' => $modules,
                        'rees46_css' => $css,
                    )
                );

                return $this->display(__FILE__, 'views/templates/hook/rees46.tpl');
            }
        }
    }

    public function getProducts($module_id, $ids)
    {
        $products = array();

        $product_ids = explode(',', $ids);

        if (!empty($product_ids)) {
            $module_values = Tools::jsonDecode(Configuration::get('REES46_MODULE_' . $module_id), true);

            if ($module_values['title'][$this->context->language->id] == '') {
                $title = $this->l(Rees46::$recommends[$module_values['type']]);
            } else {
                $title = $module_values['title'][$this->context->language->id];
            }

            if ($module_values['template'] == 'product-list') {
                $this->smarty->assign(
                    array(
                        'page_name' => 'index',
                    )
                );

                $template = 'custom';
            } else {
                $template = $module_values['template'];
            }

            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                $dir = '15/';
            } elseif (version_compare(_PS_VERSION_, '1.7', '<')) {
                $dir = '16/';
            } else {
                $dir = '';
            }

            $template_file = 'views/templates/front/' . $dir . 'recommendations_' . $template . '.tpl';

            $cache_id = 'rees46|' . $module_id . '|' . $template . '|' . implode('|', $product_ids);

            if (!$this->isCached($template_file, $this->getCacheId($cache_id))) {
                if (version_compare(_PS_VERSION_, '1.7', '<')) {
                    foreach ($product_ids as $product_id) {
                        $product = new Product(
                            (int)$product_id,
                            true,
                            $this->context->language->id,
                            $this->context->shop->id
                        );

                        $image = Product::getCover($product->id);

                        if ($product->name != null && $product->active && $product->available_for_order) {
                            $link = $this->context->link->getProductLink(
                                (int)$product->id,
                                $product->link_rewrite,
                                $product->category,
                                $product->ean13,
                                $this->context->language->id,
                                $this->context->shop->id,
                                0,
                                false,
                                false,
                                false
                            );

                            if (parse_url($link, PHP_URL_QUERY)) {
                                $link = $link . '&recommended_by=' . $module_values['type'];
                            } else {
                                $link = $link . '?recommended_by=' . $module_values['type'];
                            }

                            $products[] = array(
                                'id_product' => $product->id,
                                'name' => $product->name,
                                'link' => $link,
                                'show_price' => $product->show_price,
                                'link_rewrite' => $product->link_rewrite,
                                'price' => $product->getPrice(!Tax::excludeTaxeOption()),
                                'price_without_reduction' => Product::getPriceStatic((int)$product->id),
                                'id_product_attribute' => Product::getDefaultAttribute($product->id),
                                'customizable' => $product->customizable,
                                'allow_oosp' => Product::isAvailableWhenOutOfStock($product->out_of_stock),
                                'quantity' => $product->quantity,
                                'image' => $this->context->link->getImageLink(
                                    $product->link_rewrite[$this->context->language->id],
                                    $image['id_image'],
                                    $module_values['image_type']
                                ),
                                'id_image' => $image['id_image'],
                                'description_short' => $product->description_short,
                                'available_for_order' => false,
                            );
                        } else {
                            $this->curlDisable($product_id);
                        }
                    }
                } else {
                    foreach ($product_ids as $product_id) {
                        $product = (new ProductDataProvider)->getProduct(
                            (int)$product_id,
                            true,
                            $this->context->language->id,
                            $this->context->shop->id
                        );

                        $id_image = $product->getCover($product_id);

                        $fix_product = new Product(
                            (int)$product_id,
                            true,
                            $this->context->language->id,
                            $this->context->shop->id
                        );

                        $cover = (new ImageRetriever($this->context->link))->getImage(
                            $fix_product,
                            (int)$id_image['id_image']
                        );

                        if ($product->name != null && $product->active && $product->available_for_order) {
                            $url = $this->context->link->getProductLink(
                                (int)$product_id,
                                $product->link_rewrite,
                                $product->category,
                                $product->ean13,
                                $this->context->language->id,
                                $this->context->shop->id,
                                0,
                                false,
                                false,
                                false
                            );

                            if (parse_url($url, PHP_URL_QUERY)) {
                                $url = $url . '&recommended_by=' . $module_values['type'];
                            } else {
                                $url = $url . '?recommended_by=' . $module_values['type'];
                            }

                            $products[] = array(
                                'id_product' => $product_id,
                                'name' => $product->name,
                                'url' => $url,
                                'cover' => $cover,
                                'id_product_attribute' => Product::getDefaultAttribute($product->id),
                                'available_for_order' => (bool)$product->available_for_order,
                                'show_price' => (bool)$product->show_price,
                                'price' => Tools::displayPrice(Tools::convertPrice($product->getPrice(
                                    !Tax::excludeTaxeOption()
                                ))),
                                'online_only' => (bool)$product->online_only,
                                'description_short' => $product->description_short,
                                'main_variants' => false,
                                'has_discount' => false,
                                'flags' => false,
                            );
                        } else {
                            $this->curlDisable($product_id);
                        }
                    }
                }

                if (!empty($products)) {
                    $this->smarty->assign(
                        array(
                            'rees46_module_id' => $module_id,
                            'rees46_title' => $title,
                            'rees46_more' => $this->l('More'),
                            'rees46_products' => $products,
                            'rees46_template' => $module_values['template'],
                        )
                    );

                    return $this->display(__FILE__, $template_file, $this->getCacheId($cache_id));
                }
            } else {
                return $this->display(__FILE__, $template_file, $this->getCacheId($cache_id));
            }
        }
    }

    public function getContent()
    {
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->context->controller->addJS($this->_path.'views/js/admin/old_rees46.js');
        } else {
            $this->context->controller->addJS($this->_path.'views/js/admin/rees46.js');
        }

        $output = null;

        if (Tools::isSubmit('submit' . $this->name)) {
            $this->_clearCache('*');

            foreach (Rees46::$fields as $field) {
                if ('REES46_ORDER' == Tools::substr($field, 0, 12)) {
                    Configuration::updateValue($field, Tools::jsonEncode(Tools::getValue($field)));
                } else {
                    Configuration::updateValue($field, Tools::getValue($field));
                }
            }

            if ((!Configuration::get('REES46_XML_EXPORTED') || Configuration::get('REES46_XML_EXPORTED') == null)
                && Configuration::get('REES46_STORE_KEY') != ''
                && Configuration::get('REES46_SECRET_KEY') != ''
            ) {
                $params = array();

                $yml_file_url = _PS_BASE_URL_ . __PS_BASE_URI__ . 'index.php?fc=module&module=rees46&controller=xml';

                $params['store_key'] = Configuration::get('REES46_STORE_KEY');
                $params['store_secret'] = Configuration::get('REES46_SECRET_KEY');
                $params['yml_file_url'] = $yml_file_url;

                $url = 'https://rees46.com/api/shop/set_yml';

                $ch = curl_init();

                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POSTFIELDS, Tools::jsonEncode($params, true));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                curl_exec($ch);
                $info = curl_getinfo($ch);

                curl_close($ch);

                if ($info['http_code'] >= 200 && $info['http_code'] < 300) {
                    Configuration::updateValue('REES46_XML_EXPORTED', true);
                }
            }

            $output .= $this->displayConfirmation($this->l('The settings have been successfully updated.'));

            $output .= $this->renderForm().$this->renderList().$this->renderFormHelp();
        } elseif (Tools::isSubmit('submit_module')) { // save module
            Configuration::updateValue('REES46_MODULE_' . Tools::getValue('id_module'), Tools::jsonEncode(
                $this->getModuleValues()
            ));

            $output .= $this->displayConfirmation($this->l('The settings have been successfully updated.'));

            $output .= $this->renderForm().$this->renderList().$this->renderFormHelp();
        } elseif (Tools::isSubmit('deletemodule')
            && Tools::isSubmit('id_module')
            && Configuration::get('REES46_MODULE_' . Tools::getValue('id_module'))
        ) { // delete module
            Configuration::deleteByName('REES46_MODULE_' . Tools::getValue('id_module'));

            $output .= $this->displayConfirmation($this->l('The settings have been successfully updated.'));

            $output .= $this->renderForm().$this->renderList().$this->renderFormHelp();
        } elseif (Tools::isSubmit('new_module')
            || (Tools::isSubmit('id_module')
            && Configuration::get('REES46_MODULE_' . Tools::getValue('id_module')))
        ) { // view module
            $output .= $this->renderFormModule();
        } else {
            $output .= $this->renderForm().$this->renderList().$this->renderFormHelp();
        }

        return $output;
    }

    public function renderForm()
    {
        $fields_form = array();

        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('General'),
                'icon' => 'icon-cog',
            ),
            'description' => (Configuration::get('REES46_STORE_KEY') == ''
                || Configuration::get('REES46_SECRET_KEY') == '')
                ? $this->l('To start using this module, please register an account on rees46.com.')
                : false,
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Store Key'),
                    'name' => 'REES46_STORE_KEY',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Secret Key'),
                    'name' => 'REES46_SECRET_KEY',
                ),
                array(
                    'type' => version_compare(_PS_VERSION_, '1.6', '<') ? 'radio' : 'switch',
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
                    'class' => 't',
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'icon' => 'icon-save',
                'class' => 'button btn btn-default pull-right',
            ),
        );

        $fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('Products'),
                'icon' => 'icon-book',
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'REES46_XML_EXPORTED',
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Currency'),
                    'name' => 'REES46_XML_CURRENCY',
                    'options' => array(
                        'query' => Currency::getCurrenciesByIdShop((int)Tools::getValue('id_shop')),
                        'id' => 'id_currency',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Cron Task Link'),
                    'name' => 'REES46_XML_CRON',
                    'readonly' => true,
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'icon' => 'icon-save',
                'class' => 'button btn btn-default pull-right',
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
                'class' => 'button btn btn-default pull-right',
            ),
            'description' => $this->l('Manual export is required once - during initial configuration. ')
                . $this->l('Exported orders are the orders for the last 6 months with the statuses of your choice. ')
                . $this->l('Please remember to save current settings before starting the export.'),
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
                    'label' => $this->l('Audience Type'),
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
            'description' => $this->l('Please save current settings before starting the export.'),
            'submit' => array(
                'title' => $this->l('Save'),
                'icon' => 'icon-save',
                'class' => 'button btn btn-default pull-right',
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
            'description' => $this->l('To enable Web Push Notifications, your website first needs to support HTTPS! ')
                . $this->l('Files manifest.json and push_sw.js will automatically be placed in the root directory of your online store. ')
                . $this->l('Click "Check Files".'),
            'buttons' => array(
                array(
                    'title' => $this->l('Check Files'),
                    'icon' => 'icon-refresh',
                    'id' => 'submitCheckFiles',
                    'name' => 'submitCheckFiles',
                ),
            ),
        );

        $helper = new HelperForm();
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang =
            Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG')?Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG'):0;
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
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $helper->toolbar_btn = array(
                'new' => array(
                    'desc' => $this->l('Export Orders'),
                    'name' => 'submitExportOrders',
                ),
                'newAttributes' => array(
                    'desc' => $this->l('Export Customers'),
                    'name' => 'submitExportCustomers',
                ),
                'preview' => array(
                    'desc' => $this->l('Check Files'),
                    'name' => 'submitCheckFiles',
                ),
            );
        }

        foreach (Rees46::$fields as $field) {
            if ('REES46_XML_CRON' == $field) {
                $xml_cron = _PS_BASE_URL_ . __PS_BASE_URI__ . 'index.php?fc=module&module=rees46&controller=cron';

                $helper->tpl_vars['fields_value'][$field] = $xml_cron;
            } elseif ('REES46_ORDER' == Tools::substr($field, 0, 12)) {
                if (is_array(Tools::jsonDecode(Configuration::get($field), true))) {
                    foreach (OrderState::getOrderStates((int)$this->context->language->id) as $order_status) {
                        $helper->tpl_vars['fields_value'][$field . '[]_' . $order_status['id_order_state']] =
                            in_array(
                                $order_status['id_order_state'],
                                Tools::jsonDecode(Configuration::get($field), true)
                            ) ? true : false;
                    }
                } else {
                    $helper->tpl_vars['fields_value'][$field] = array();
                }
            } else {
                $helper->tpl_vars['fields_value'][$field] = Configuration::get($field);
            }
        }

        return $helper->generateForm($fields_form);
    }

    public function renderList()
    {
        $content = $this->getListValues();

        $fields_list = array(
            'id_module' => array(
                'title' => $this->l('ID'),
                'orderby' => false,
                'search' => false,
                'align' => 'text-center',
            ),
            'hook' => array(
                'title' => $this->l('Hook'),
                'orderby' => false,
                'search' => false,
            ),
            'type' => array(
                'title' => $this->l('Block Type'),
                'orderby' => false,
                'search' => false,
            ),
            'position' => array(
                'title' => $this->l('Position'),
                'orderby' => false,
                'search' => false,
                'align' => 'text-center',
            ),
            'status' => array(
                'title' => $this->l('Block Status'),
                'orderby' => false,
                'search' => false,
                'align' => 'text-center',
                'icon' => array(
                    0 => 'disabled.gif',
                    1 => 'enabled.gif',
                ),
            ),
        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->listTotal = count($content);
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->table = $this->table;
        $helper->title = '<i class="icon-puzzle-piece"></i> ' . $this->l('Recommendations');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->identifier = $this->identifier;
        $helper->actions = array('edit', 'delete',);
        $helper->module = $this;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->toolbar_btn = array(
            'new' => array(
                'href' => AdminController::$currentIndex
                    . '&configure=' . $this->name
                    . '&new_module=1'
                    . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Add'),
                'icon' => 'process-icon-new',
            ),
        );

        return $helper->generateList($content, $fields_list);
    }

    private function getListValues()
    {
        $list_values = array();

        if (!Configuration::get('REES46_MODULE_ID')) {
            Configuration::updateValue('REES46_MODULE_ID', 0);
        }

        for ($id_module = 1; $id_module <= Configuration::get('REES46_MODULE_ID'); $id_module++) {
            if (Configuration::get('REES46_MODULE_' . $id_module)) {
                $module_values = Tools::jsonDecode(Configuration::get('REES46_MODULE_' . $id_module), true);

                $list_values[] = array(
                    'id_module' => $module_values['id_module'],
                    'hook' => $module_values['hook'],
                    'type' => $this->l(Rees46::$recommends[$module_values['type']]),
                    'position' => $module_values['position'],
                    'status' => $module_values['status'],
                );
            } else {
                continue;
            }
        }

        return $list_values;
    }

    public function renderFormHelp()
    {
        $fields_form = array();

        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Help'),
                'icon' => 'icon-comments',
            ),
            'description' => $this->l('Documentation: ')
            . '<a href="' . $this->l('http://docs.rees46.com/display/en/PrestaShop+Module') . '" target="_blank">'
            .$this->l('http://docs.rees46.com/display/en/PrestaShop+Module').' <i class="icon-external-link"></i></a>',
        );

        $helper = new HelperForm();
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang =
            Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG')?Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG'):0;
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
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;

        return $helper->generateForm($fields_form);
    }

    public function renderFormModule()
    {
        $images_types = ImageType::getImagesTypes('products');

        $manufacturers = array();

        foreach (Manufacturer::getManufacturers() as $manufacturer) {
            $manufacturers[] = array(
                'id' => (int)$manufacturer['id_manufacturer'],
                'val' => (int)$manufacturer['id_manufacturer'],
                'name' => htmlspecialchars(trim($manufacturer['name'])),
            );
        }

        $fields_form = array();

        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Recommendation block'),
                'icon' => 'icon-puzzle-piece',
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'id_module',
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Hook'),
                    'name' => 'hook',
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => 'displayHome',
                                'name' => 'displayHome',
                            ),
                            array(
                                'id' => 'displayLeftColumn',
                                'name' => 'displayLeftColumn',
                            ),
                            array(
                                'id' => 'displayRightColumn',
                                'name' => 'displayRightColumn',
                            ),
                            array(
                                'id' => 'displayFooterProduct',
                                'name' => 'displayFooterProduct',
                            ),
                            array(
                                'id' => 'displayRightColumnProduct',
                                'name' => 'displayRightColumnProduct',
                            ),
                            array(
                                'id' => 'displayLeftColumnProduct',
                                'name' => 'displayLeftColumnProduct',
                            ),
                            array(
                                'id' => 'displayShoppingCartFooter',
                                'name' => 'displayShoppingCartFooter',
                            ),
                            array(
                                'id' => 'displayOrderConfirmation',
                                'name' => 'displayOrderConfirmation',
                            ),
                            array(
                                'id' => 'displaySearch',
                                'name' => 'displaySearch',
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name',
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Block Type'),
                    'name' => 'type',
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => 'interesting',
                                'name' => $this->l('You may like it'),
                            ),
                            array(
                                'id' => 'also_bought',
                                'name' => $this->l('Also bought with this product'),
                            ),
                            array(
                                'id' => 'similar',
                                'name' => $this->l('Similar products'),
                            ),
                            array(
                                'id' => 'popular',
                                'name' => $this->l('Popular products'),
                            ),
                            array(
                                'id' => 'see_also',
                                'name' => $this->l('See also'),
                            ),
                            array(
                                'id' => 'recently_viewed',
                                'name' => $this->l('Recently viewed'),
                            ),
                            array(
                                'id' => 'buying_now',
                                'name' => $this->l('Right now bought'),
                            ),
                            array(
                                'id' => 'search',
                                'name' => $this->l('Customers who looked for this product also bought'),
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name',
                    )
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Block Title'),
                    'name' => 'title',
                    'lang' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Limit'),
                    'name' => 'limit',
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Image Type'),
                    'name' => 'image_type',
                    'options' => array(
                        'query' => $images_types,
                        'id' => 'name',
                        'name' => 'name',
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Recommendation Block Template'),
                    'name' => 'template',
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => 'home',
                                'name' => $this->l('Home'),
                            ),
                            array(
                                'id' => 'sidebar',
                                'name' => $this->l('Sidebar'),
                            ),
                            array(
                                'id' => 'product-list',
                                'name' => $this->l('Product List'),
                            ),
                            array(
                                'id' => 'basic',
                                'name' => $this->l('Basic REES46'),
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name',
                    )
                ),
                array(
                    'type' => version_compare(_PS_VERSION_, '1.6', '<') ? 'radio' : 'switch',
                    'label' => $this->l('Show Only Special Products'),
                    'name' => 'discount',
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
                    'class' => 't',
                ),
                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Show Only Products of Following Brands'),
                    'name' => 'manufacturers[]',
                    'values' => array(
                        'query' => $manufacturers,
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
                    'label' => $this->l('Exclude Products of Following Brands'),
                    'name' => 'manufacturers_exclude[]',
                    'values' => array(
                        'query' => $manufacturers,
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
                    'type' => 'text',
                    'label' => $this->l('Position Block Within the Hook'),
                    'name' => 'position',
                ),
                array(
                    'type' => version_compare(_PS_VERSION_, '1.6', '<') ? 'radio' : 'switch',
                    'label' => $this->l('Block Status'),
                    'name' => 'status',
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
                    'class' => 't',
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'icon' => 'icon-save',
                'class' => 'button btn btn-default pull-right',
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->toolbar_scroll = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang =
            Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG')?Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG'):0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit_module';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name
            . '&tab_module=' . $this->tab
            . '&module_name='. $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getModuleValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm($fields_form);
    }

    private function getModuleValues()
    {
        $module_values = array();

        if (Tools::isSubmit('submit_module')) { // save module
            $module_values['id_module'] = Tools::getValue('id_module');
            $module_values['hook'] = Tools::getValue('hook');
            $module_values['type'] = Tools::getValue('type');
            $module_values['limit'] = Tools::getValue('limit');
            $module_values['template'] = Tools::getValue('template');
            $module_values['image_type'] = Tools::getValue('image_type');
            $module_values['discount'] = Tools::getValue('discount');
            $module_values['manufacturers'] = Tools::jsonEncode(Tools::getValue('manufacturers'));
            $module_values['manufacturers_exclude'] = Tools::jsonEncode(Tools::getValue('manufacturers_exclude'));
            $module_values['position'] = Tools::getValue('position');
            $module_values['status'] = Tools::getValue('status');

            $languages = Language::getLanguages(false);

            foreach ($languages as $lang) {
                $module_values['title'][$lang['id_lang']] = Tools::getValue('title_' . (int)$lang['id_lang']);
            }
        } elseif (Tools::isSubmit('id_module')
            && Configuration::get('REES46_MODULE_' . Tools::getValue('id_module'))
        ) { // view module
            $module_values = Tools::jsonDecode(Configuration::get('REES46_MODULE_'.Tools::getValue('id_module')), true);

            if (is_array(Tools::jsonDecode($module_values['manufacturers'], true))) {
                foreach (Manufacturer::getManufacturers() as $manufacturer) {
                    $module_values['manufacturers[]_' . $manufacturer['id_manufacturer']] =
                        in_array(
                            $manufacturer['id_manufacturer'],
                            Tools::jsonDecode($module_values['manufacturers'], true)
                        ) ? true : false;
                }
            } else {
                $module_values['manufacturers'] = array();
            }

            if (is_array(Tools::jsonDecode($module_values['manufacturers_exclude'], true))) {
                foreach (Manufacturer::getManufacturers() as $manufacturer) {
                    $module_values['manufacturers_exclude[]_' . $manufacturer['id_manufacturer']] =
                        in_array(
                            $manufacturer['id_manufacturer'],
                            Tools::jsonDecode($module_values['manufacturers_exclude'], true)
                        ) ? true : false;
                }
            } else {
                $module_values['manufacturers_exclude'] = array();
            }
        } else { // new module
            $id_module = Configuration::get('REES46_MODULE_ID') + 1;

            Configuration::updateValue('REES46_MODULE_ID', $id_module);

            $module_values['id_module'] = $id_module;
            $module_values['hook'] = 'displayHome';
            $module_values['type'] = 'interesting';
            $module_values['limit'] = '';
            $module_values['template'] = 'default';
            $module_values['image_type'] = 'home_default';
            $module_values['discount'] = 0;
            $module_values['manufacturers'] = array();
            $module_values['manufacturers_exclude'] = array();
            $module_values['position'] = 0;
            $module_values['status'] = 0;

            $languages = Language::getLanguages(false);

            foreach ($languages as $lang) {
                $module_values['title'][$lang['id_lang']] = '';
            }
        }

        return $module_values;
    }

    public function ajaxProcessExportOrders()
    {
        if (Configuration::get('REES46_STORE_KEY') != ''
            && Configuration::get('REES46_SECRET_KEY') != ''
        ) {
            $json = array();

            $next = (int)Tools::getValue('next');
            $limit = 1000;

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

                $params = array();

                $params['shop_id'] = Configuration::get('REES46_STORE_KEY');
                $params['shop_secret'] = Configuration::get('REES46_SECRET_KEY');
                $params['orders'] = $data;

                $url = 'http://api.rees46.com/import/orders';

                $return = $this->curl($url, Tools::jsonEncode($params));

                if ($return['info']['http_code'] < 200 || $return['info']['http_code'] >= 300) {
                    $json['error'] = 'Error code: ' . $return['info']['http_code'] . '!';

                    if (Configuration::get('REES46_LOG_STATUS')) {
                        if (version_compare(_PS_VERSION_, '1.6', '<')) {
                            Logger::addLog(
                                'REES46: Export orders (' . $results_total . ')',
                                3,
                                $return['info']['http_code'],
                                null,
                                null,
                                true
                            );
                        } else {
                            PrestaShopLogger::addLog(
                                'REES46: Export orders (' . $results_total . ')',
                                3,
                                $return['info']['http_code'],
                                null,
                                null,
                                true
                            );
                        }
                    }
                } else {
                    if ($results_total > $next * $limit) {
                        $json['next'] = $next + 1;

                        $json['success'] = sprintf(
                            $this->l('Processing: You have exported %s of %s selected orders into REES46!'),
                            $next * $limit,
                            $results_total
                        );
                    } else {
                        $json['success'] = sprintf(
                            $this->l('You have exported all %s selected orders into REES46!'),
                            $results_total
                        );

                        if (Configuration::get('REES46_LOG_STATUS')) {
                            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                                Logger::addLog(
                                    'REES46: Export orders (' . $results_total . ')',
                                    1,
                                    null,
                                    null,
                                    null,
                                    true
                                );
                            } else {
                                PrestaShopLogger::addLog(
                                    'REES46: Export orders (' . $results_total . ')',
                                    1,
                                    null,
                                    null,
                                    null,
                                    true
                                );
                            }
                        }
                    }
                }
            } else {
                $json['error'] = 'No data for export!';
            }
        } else {
            $json['error'] = 'Fields Store Key and Secret Key is required!';
        }

        echo Tools::jsonEncode($json);
    }

    public function ajaxProcessExportCustomers()
    {
        if (Configuration::get('REES46_STORE_KEY') != ''
            && Configuration::get('REES46_SECRET_KEY') != ''
        ) {
            $json = array();

            $next = (int)Tools::getValue('next');
            $limit = 1000;

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

                $params = array();

                $params['shop_id'] = Configuration::get('REES46_STORE_KEY');
                $params['shop_secret'] = Configuration::get('REES46_SECRET_KEY');
                $params['audience'] = $data;

                $url = 'http://api.rees46.com/import/audience';

                $return = $this->curl($url, Tools::jsonEncode($params));

                if ($return['info']['http_code'] < 200 || $return['info']['http_code'] >= 300) {
                    $json['error'] = 'Error code: ' . $return['info']['http_code'] . '!';

                    if (Configuration::get('REES46_LOG_STATUS')) {
                        if (version_compare(_PS_VERSION_, '1.6', '<')) {
                            Logger::addLog(
                                'REES46: Export customers (' . $results_total . ')',
                                3,
                                $return['info']['http_code'],
                                null,
                                null,
                                true
                            );
                        } else {
                            PrestaShopLogger::addLog(
                                'REES46: Export customers (' . $results_total . ')',
                                3,
                                $return['info']['http_code'],
                                null,
                                null,
                                true
                            );
                        }
                    }
                } else {
                    if ($results_total > $next * $limit) {
                        $json['next'] = $next + 1;

                        $json['success'] = sprintf(
                            $this->l('Processing: You have exported %s of %s selected customers into REES46!'),
                            $next * $limit,
                            $results_total
                        );
                    } else {
                        $json['success'] = sprintf(
                            $this->l('You have exported all %s selected customers into REES46!'),
                            $results_total
                        );

                        if (Configuration::get('REES46_LOG_STATUS')) {
                            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                                Logger::addLog(
                                    'REES46: Export customers (' . $results_total . ')',
                                    1,
                                    null,
                                    null,
                                    null,
                                    true
                                );
                            } else {
                                PrestaShopLogger::addLog(
                                    'REES46: Export customers (' . $results_total . ')',
                                    1,
                                    null,
                                    null,
                                    null,
                                    true
                                );
                            }
                        }
                    }
                }
            } else {
                $json['error'] = 'No data for export!';
            }
        } else {
            $json['error'] = 'Fields Store Key and Secret Key is required!';
        }

        echo Tools::jsonEncode($json);
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

                $url = 'https://raw.githubusercontent.com/rees46/web-push-files/master/' . $file;

                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $result = curl_exec($ch);
                $info = curl_getinfo($ch);

                curl_close($ch);

                if ($info['http_code'] < 200 || $info['http_code'] >= 300) {
                    if (Configuration::get('REES46_LOG_STATUS')) {
                        if (version_compare(_PS_VERSION_, '1.6', '<')) {
                            Logger::addLog(
                                'REES46: Not loading file ' . $file,
                                3,
                                $info['http_code'],
                                null,
                                null,
                                true
                            );
                        } else {
                            PrestaShopLogger::addLog(
                                'REES46: Not loading file ' . $file,
                                3,
                                $info['http_code'],
                                null,
                                null,
                                true
                            );
                        }
                    }
                } else {
                    file_put_contents($dir . $file, $result);

                    if (Configuration::get('REES46_LOG_STATUS')) {
                        if (version_compare(_PS_VERSION_, '1.6', '<')) {
                            Logger::addLog(
                                'REES46: Loading file ' . $file,
                                1,
                                null,
                                null,
                                null,
                                true
                            );
                        } else {
                            PrestaShopLogger::addLog(
                                'REES46: Loading file ' . $file,
                                1,
                                null,
                                null,
                                null,
                                true
                            );
                        }
                    }
                }
            }

            if (is_file($dir . $file)) {
                $json['success'][$key] = sprintf($this->l('File %s is loaded!'), $file);
            } else {
                $json['error'][$key] = sprintf($this->l('File %s is not loaded!'), $file);
            }
        }

        echo Tools::jsonEncode($json);
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

        $rees46_order_created = Tools::jsonDecode(Configuration::get('REES46_ORDER_CREATED'), true);
        $rees46_order_completed = Tools::jsonDecode(Configuration::get('REES46_ORDER_COMPLETED'), true);
        $rees46_order_cancelled = Tools::jsonDecode(Configuration::get('REES46_ORDER_CANCELLED'), true);

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

        $rees46_order_created = Tools::jsonDecode(Configuration::get('REES46_ORDER_CREATED'), true);
        $rees46_order_completed = Tools::jsonDecode(Configuration::get('REES46_ORDER_COMPLETED'), true);
        $rees46_order_cancelled = Tools::jsonDecode(Configuration::get('REES46_ORDER_CANCELLED'), true);

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

    private function curlDisable($product_id)
    {
        $url = 'http://api.rees46.com/import/disable';

        $params = array();

        $params['shop_id'] = Configuration::get('REES46_STORE_KEY');
        $params['shop_secret'] = Configuration::get('REES46_SECRET_KEY');
        $params['item_ids'] = $product_id;

        $return = $this->curl($url, Tools::jsonEncode($params));

        if (Configuration::get('REES46_LOG_STATUS')) {
            if ($return['info']['http_code'] < 200 || $return['info']['http_code'] >= 300) {
                if (version_compare(_PS_VERSION_, '1.6', '<')) {
                    Logger::addLog(
                        'REES46: Excluded of recomended product_id [' . $product_id . ']',
                        3,
                        $return['info']['http_code'],
                        null,
                        null,
                        true
                    );
                } else {
                    PrestaShopLogger::addLog(
                        'REES46: Excluded of recomended product_id [' . $product_id . ']',
                        3,
                        $return['info']['http_code'],
                        null,
                        null,
                        true
                    );
                }
            } else {
                if (version_compare(_PS_VERSION_, '1.6', '<')) {
                    Logger::addLog(
                        'REES46: Excluded of recomended product_id [' . $product_id . ']',
                        1,
                        null,
                        null,
                        null,
                        true
                    );
                } else {
                    PrestaShopLogger::addLog(
                        'REES46: Excluded of recomended product_id [' . $product_id . ']',
                        1,
                        null,
                        null,
                        null,
                        true
                    );
                }
            }
        }
    }

    private function curl($url, $params)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

        $data = array();

        $data['result'] = curl_exec($ch);
        $data['info'] = curl_getinfo($ch);

        curl_close($ch);

        return $data;
    }
}
