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

class Rees46XmlModuleFrontController extends ModuleFrontController
{
    public $ssl = false;
    public $display_header = false;
    public $display_column_left = false;
    public $display_column_right = false;
    public $display_footer = false;

    public function initContent()
    {
        parent::initContent();

        if (Configuration::get('REES46_STORE_KEY') != ''
            && Configuration::get('REES46_SECRET_KEY') != ''
        ) {
            if (Tools::getValue('step')) {
                if (Tools::getValue('step') == 1) {
                    $this->generateCurrencies();

                    Tools::redirect('index.php?fc=module&module=rees46&controller=xml&step=2');
                } elseif (Tools::getValue('step') == 2) {
                    $this->generateCategories();

                    Tools::redirect('index.php?fc=module&module=rees46&controller=xml&prev=1');
                }
            } elseif (Tools::getValue('prev')) {
                if (Tools::getValue('prev') != 'finish') {
                    $prev = $this->generateOffers(Tools::getValue('prev'));

                    $redirect = '<meta http-equiv="refresh" content="0;';
                    $redirect .= 'index.php?fc=module&module=rees46&controller=xml&prev=' . $prev .'">';

                    die($redirect);
                } elseif (Tools::getValue('prev') == 'finish') {
                    $xml  = '    </offers>' . "\n";
                    $xml .= '  </shop>' . "\n";
                    $xml .= '</yml_catalog>';

                    $this->recorder($xml, 'a');

                    header('Content-Type: application/xml; charset=utf-8');
                    echo Tools::file_get_contents(_PS_DOWNLOAD_DIR_ . 'rees46.xml');
                }
            } else {
                if (is_file(_PS_DOWNLOAD_DIR_ . 'rees46_cron.xml')) {
                    header('Content-Type: application/xml; charset=utf-8');
                    echo Tools::file_get_contents(_PS_DOWNLOAD_DIR_ . 'rees46_cron.xml');
                } else {
                    $this->recorder('', 'w+');

                    $this->generateShop();

                    Tools::redirect('index.php?fc=module&module=rees46&controller=xml&step=1');
                }
            }
        } else {
            Tools::redirect('index.php?controller=404');
        }
    }

    protected function generateShop()
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<!DOCTYPE yml_catalog SYSTEM "shops.dtd">' . "\n";
        $xml .= '<yml_catalog date="' . date('Y-m-d H:i') . '">' . "\n";
        $xml .= '  <shop>' . "\n";
        $xml .= '    <name>' . Configuration::get('PS_SHOP_NAME') . '</name>' . "\n";
        $xml .= '    <company>' . Configuration::get('PS_SHOP_NAME') . '</company>' . "\n";
        $xml .= '    <url>' . _PS_BASE_URL_ . __PS_BASE_URI__ . '</url>' . "\n";
        $xml .= '    <platform>PrestaShop</platform>' . "\n";
        $xml .= '    <version>' . _PS_VERSION_ . '</version>' . "\n";

        $this->recorder($xml, 'a');
    }

    protected function generateCurrencies()
    {
        $currencies = Currency::getCurrencies();

        $xml = '    <currencies>';

        foreach ($currencies as $currency) {
            if ($currency['id_currency'] == Configuration::get('REES46_XML_CURRENCY')) {
                $xml .= "\n" . '      <currency id="' . $currency['iso_code'] . '" rate="1"/>';
            } elseif ($currency['active'] == 1) {
                $xml .= "\n" . '      <currency id="' . $currency['iso_code'] . '" ';
                $xml .= 'rate="' . number_format(1 / $currency['conversion_rate'], 4, '.', '') . '"/>';
            }
        }

        $xml .= "\n" . '    </currencies>' . "\n";

        $this->recorder($xml, 'a');
    }

    protected function generateCategories()
    {
        $categories = Category::getCategories((int)Configuration::get('PS_LANG_DEFAULT'));

        if (!empty($categories)) {
            $xml = '    <categories>';

            foreach ($categories as $id_parent) {
                foreach ($id_parent as $category) {
                    if ($category['infos']['id_parent']) {
                        $parent = ' parentId="' . $category['infos']['id_parent'] . '"';
                    } else {
                        $parent = '';
                    }

                    $xml .= "\n" . '      <category id="' . $category['infos']['id_category'] . '"' . $parent . '>';
                    $xml .= $this->replacer($category['infos']['name']) . '</category>';
                }
            }

            $xml .= "\n" . '    </categories>' . "\n";

            $this->recorder($xml, 'a');
        }
    }

    protected function generateOffers($prev)
    {
        if ($prev == 1) {
            $xml = '    <offers>' . "\n";
        } else {
            $xml = '';
        }

        $id_product = $this->getIdProduct($prev);

        if (isset($id_product)) {
            $product = new Product(
                (int)$id_product,
                true,
                (int)Configuration::get('PS_LANG_DEFAULT')
            );

            $xml .= '      <offer id="' . $product->id . '" ';
            $xml .= 'available="' . ($product->quantity > 0 ? 'true' : 'false') . '">' . "\n";
            $xml .= '        <url>'.$this->replacer($this->context->link->getProductLink($product->id)).'</url>' . "\n";

            $price = $product->getPrice(!Tax::excludeTaxeOption());
            $currency = new Currency((int)Configuration::get('REES46_XML_CURRENCY'));
            $price *= $currency->conversion_rate;

            $xml .= '        <price>' . number_format($price, 2, '.', '') . '</price>' . "\n";
            $xml .= '        <currencyId>' . $currency->iso_code . '</currencyId>' . "\n";

            $categories = $product->getCategories();

            if (!empty($categories)) {
                foreach ($categories as $category) {
                    $xml .= '        <categoryId>' . $category . '</categoryId>' . "\n";
                }
            }

            $img = Product::getCover($product->id);

            if ($img['id_image']) {
                $image = $this->context->link->getImageLink(
                    $product->link_rewrite[(int)Configuration::get('PS_LANG_DEFAULT')],
                    $img['id_image'],
                    'home_default'
                );

                $xml .= '        <picture>' . $image . '</picture>' . "\n";
            }

            $xml .= '        <name>' . $this->replacer($product->name) . '</name>' . "\n";

            if ($product->manufacturer_name) {
                $xml .= '        <vendor>' . $this->replacer($product->manufacturer_name) . '</vendor>' . "\n";
            }

            $xml .= '        <model>' . $this->replacer($product->reference) . '</model>' . "\n";
            $xml .= '        <description><![CDATA[' . strip_tags(
                htmlspecialchars_decode($product->description),
                '<h3>, <ul>, <li>, <p>, <br>'
            ) . ']]></description>' . "\n";
            $xml .= '      </offer>' . "\n";

            $this->recorder($xml, 'a');

            $prev = $product->id;
        } else {
            $prev = 'finish';
        }

        return $prev;
    }

    protected function replacer($str)
    {
        return trim(str_replace('&#039;', '&apos;', htmlspecialchars(
            htmlspecialchars_decode($str, ENT_QUOTES),
            ENT_QUOTES
        )));
    }

    protected function recorder($xml, $mode)
    {
        if (!$fp = fopen(_PS_DOWNLOAD_DIR_ . 'rees46.xml', $mode)) {
            if (Configuration::get('REES46_LOG_STATUS')) {
                $this->log->write('REES46 log: Could not open xml file [ERROR]');
                Logger::addLog('REES46: Could not open xml file', 3, null, null, null, true);
            }
        } elseif (fwrite($fp, $xml) === false) {
            if (Configuration::get('REES46_LOG_STATUS')) {
                Logger::addLog('REES46: XML file not writable', 3, null, null, null, true);
            }
        }

        fclose($fp);
    }

    protected function getIdProduct($prev)
    {
        $query = new DbQuery();
        $query->select('p.`id_product`');
        $query->from('product', 'p');
        $query->where('p.`id_product` > ' . (int)$prev);
        $query->where('p.`active` = 1');
        $query->orderBy('p.`id_product` ASC');
        $query->limit(1);

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query->build());

        if (isset($result[0])) {
            return $result[0]['id_product'];
        }
    }
}
