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

$(document).ready(function() {
	$('#submitCheckFiles').click(function() {
		$.ajax({
			url: admin_modules_link,
			data: {
				ajax: true,
				configure: 'rees46',
				action: 'checkFiles',
			},
			type: 'post',
			dataType: 'json',
			beforeSend: function() {
				$('#submitCheckFiles').button('loading');
			},
			success: function(json) {
				$('#submitCheckFiles').button('reset');

				if (json['success']) {
					$.map(json['success'], function(success) {
						showSuccessMessage(success);
					});
				}

				if (json['error']) {
					$.map(json['error'], function(error) {
						showErrorMessage(error);
					});
				}
			}
		});
	});
});
