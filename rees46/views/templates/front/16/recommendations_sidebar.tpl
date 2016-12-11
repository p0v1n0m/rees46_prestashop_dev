{*
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
 *}

<div class="block">
    <h4 class="title_block">{$rees46_title|escape:'htmlall':'UTF-8'}</h4>
    <div class="block_content products-block">
        <ul class="products clearfix">
            {foreach from=$rees46_products item='product' name=product}
                <li class="clearfix">
                    <a class="products-block-image" href="{$product.link|escape:'htmlall':'UTF-8'}">
                        <img src="{$product.image}" alt="{$product.name|htmlspecialchars}" title="{$product.name|htmlspecialchars}" class="replace-2x img-responsive"/>
                    </a>
                    <div class="product-content">
                        <h5>
                            <a class="product-name" href="{$product.link|escape:'htmlall':'UTF-8'}">
                                {$product.name|truncate:15:'...'|escape:'htmlall':'UTF-8'}
                            </a>
                        </h5>
                        <p class="product-description">{$product.description_short|strip_tags:'UTF-8'|truncate:44:'...'}</p>
                        {if (!$PS_CATALOG_MODE AND ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
                            {if isset($product.show_price) && $product.show_price && !isset($restricted_country_mode)}
                                <div class="price-box">
                                    <span class="price">
                                        {if !$priceDisplay}
                                            {convertPrice price=$product.price}
                                        {else}
                                            {convertPrice price=$product.price_tax_exc}
                                        {/if}
                                    </span>
                                </div>
                            {/if}
                        {/if}
                    </div>
                </li>
            {/foreach}
        </ul>
    </div>
</div>
