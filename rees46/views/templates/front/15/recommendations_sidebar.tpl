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

<div id="featured-products_block_center" class="block products_block clearfix">
    <h4 class="title_block">{$rees46_title|escape:'htmlall':'UTF-8'}</h4>
    <div class="block_content">
        {assign var='liHeight' value=250}
        {assign var='nbItemsPerLine' value=4}
        {assign var='nbLi' value=$rees46_products|@count}
        {math equation="nbLi/nbItemsPerLine" nbLi=$nbLi nbItemsPerLine=$nbItemsPerLine assign=nbLines}
        {math equation="nbLines*liHeight" nbLines=$nbLines|ceil liHeight=$liHeight assign=ulHeight}
        <ul style="height:{$ulHeight}px;">
        {foreach from=$rees46_products item=product name=product}
            {math equation="(total%perLine)" total=$smarty.foreach.product.total perLine=$nbItemsPerLine assign=totModulo}
            {if $totModulo == 0}{assign var='totModulo' value=$nbItemsPerLine}{/if}
            <li class="ajax_block_product {if $smarty.foreach.product.first}first_item{elseif $smarty.foreach.product.last}last_item{else}item{/if} {if $smarty.foreach.product.iteration%$nbItemsPerLine == 0}last_item_of_line{elseif $smarty.foreach.product.iteration%$nbItemsPerLine == 1} {/if} {if $smarty.foreach.product.iteration > ($smarty.foreach.product.total - $totModulo)}last_line{/if}">
                <a href="{$product.link|escape:'htmlall':'UTF-8'}" title="{$product.name|escape:'htmlall':'UTF-8'}" class="product_image"><img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'htmlall':'UTF-8'}" alt="{$product.name|escape:'htmlall':'UTF-8'}" />
                </a>
                <h5 class="s_title_block"><a href="{$product.link|escape:'htmlall':'UTF-8'}" title="{$product.name|truncate:50:'...'|escape:'htmlall':'UTF-8'}">{$product.name|truncate:35:'...'|escape:'htmlall':'UTF-8'}</a></h5>
                <div class="product_desc"><a href="{$product.link|escape:'htmlall':'UTF-8'}" title="{$rees46_more|escape:'htmlall':'UTF-8'}">{$product.description_short|strip_tags|truncate:65:'...'}</a></div>
                <div>
                    <a class="lnk_more" href="{$product.link|escape:'htmlall':'UTF-8'}" title="{$rees46_more|escape:'htmlall':'UTF-8'}">{$rees46_more|escape:'htmlall':'UTF-8'}</a>
                    {if $product.show_price AND !isset($restricted_country_mode) AND !$PS_CATALOG_MODE}<p class="price_container"><span class="price">{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}</span></p>{else}<div style="height:21px;"></div>{/if}
                        <div style="height:23px;"></div>
                </div>
            </li>
        {/foreach}
        </ul>
    </div>
</div>
