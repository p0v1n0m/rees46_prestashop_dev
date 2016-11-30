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
	$('#desc-module-preview').click(function() {
		checkFiles();
	});

	$('#desc-module-new').click(function() {
		exportData('orders');
	});

	$('#desc-module-newAttributes').click(function() {
		exportData('customers');
	});
});

function exportData(type, next = 1) {
	var rees46_token;

	$('#module_form').attr('action').split('&').forEach(function(pair) {
		var parts = pair.split('=');

		if (parts[0] == 'token') {
			rees46_token = parts[1];
		}
	});

	$.ajax({
		url: module_dir + 'rees46/old_ajax.php',
		data: {
			ajax: 1,
			token: rees46_token,
			action: 'checkFiles',
			action: 'export' + type,
			type: type,
			next: next,
		},
		type: 'post',
		dataType: 'json',
		beforeSend: function() {
			$('.module_confirmation.rees46, .module_error.rees46').remove();
		},
		success: function(json) {
			if (json['success']) {
				$('#module_toolbar').before('<div class="module_confirmation conf confirm rees46">' + json['success'] + '</div>');
			}

			if (json['next']) {
				exportData(type, json['next']);
			}

			if (json['error']) {
				$('#module_toolbar').before('<div class="module_error alert error rees46">' + json['error'] + '</div>');
			}
		}
	});
}

function checkFiles() {
	var rees46_token;

	$('#module_form').attr('action').split('&').forEach(function(pair) {
		var parts = pair.split('=');

		if (parts[0] == 'token') {
			rees46_token = parts[1];
		}
	});

	$.ajax({
		url: module_dir + 'rees46/old_ajax.php',
		data: {
			ajax: 1,
			token: rees46_token,
			action: 'checkFiles',
		},
		type: 'post',
		dataType: 'json',
		beforeSend: function() {
			$('.module_confirmation.rees46, .module_error.rees46').remove();
		},
		success: function(json) {
			if (json['success']) {
				$.map(json['success'], function(success) {
					$('#module_toolbar').before('<div class="module_confirmation conf confirm rees46">' + success + '</div>');
				});
			}

			if (json['error']) {
				$.map(json['error'], function(error) {
					$('#module_toolbar').before('<div class="module_error alert error rees46">' + error + '</div>');
				});
			}
		}
	});
}
