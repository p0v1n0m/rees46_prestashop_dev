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

<section class="featured-products clearfix">
  <h3 class="products-section-title text-uppercase">{$rees46_title|escape:html:'UTF-8'}</h3>
  <div class="products">
    {foreach from=$rees46_products item="product"}

        <article class="product-miniature js-product-miniature" data-id-product="{$product.id_product}" itemscope itemtype="http://schema.org/Product">
          <div class="thumbnail-container">
            {block name='product_thumbnail'}
              <a href="{$product.link|escape:'html':'UTF-8'}" class="thumbnail product-thumbnail">
                <img
                  src = "{$product.image}"
                >
              </a>
            {/block}

            <div class="product-description">
              {block name='product_name'}
                <h1 class="h3 product-title" itemprop="name"><a href="{$product.link|escape:'html':'UTF-8'}">{$product.name|truncate:30:'...'}</a></h1>
              {/block}

              {block name='product_price_and_shipping'}
                {if $product.show_price}
                  <div class="product-price-and-shipping">
                    <span itemprop="price" class="price">{$product.price}</span>

                    {hook h='displayProductPriceBlock' product=$product type='unit_price'}

                    {hook h='displayProductPriceBlock' product=$product type='weight'}
                  </div>
                {/if}
              {/block}
            </div>

          </div>
        </article>

    {/foreach}
  </div>
</section>
