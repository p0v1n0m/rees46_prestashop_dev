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

<script type="text/javascript">
(function(r){ window.r46=window.r46||function(){ (r46.q=r46.q||[]).push(arguments) };var s=document.getElementsByTagName(r)[0],rs=document.createElement(r);rs.async=1;rs.src='//cdn.rees46.com/v3.js';s.parentNode.insertBefore(rs,s); })('script');
r46('init', '{$store_id}');
{if isset($customer_id)}
r46('profile', 'set', { id: '{$customer_id}', email: '{$customer_email}', gender: '{$customer_gender}', birthday: '{$customer_birthday}' });
{/if}
{if isset($guest_email)}
r46('profile', 'set', { email: '{$guest_email}' });
{/if}
{if isset($product_id)}
r46('track', 'view', { id: '{$product_id}', stock: '{$product_stock}', price: '{$product_price}', name: '{$product_name}', categories: {$product_categories}, image: '{$product_image}', url: '{$product_url}' });
{/if}
</script>
