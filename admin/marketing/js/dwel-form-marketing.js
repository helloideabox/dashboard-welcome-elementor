(function ($) {
	'use strict';

	function installPlugin($button) {
		var nonce = $button.data('nonce');
		var $wrapper = $button.closest('.dwel-dynamickit-wrp');

		$button.text('Installing...').prop('disabled', true);

		$.post(dwelMarketing.ajax_url, {
			action: 'dwel_install_dynamickit',
			_wpnonce: nonce
		}, function (response) {
			if (response && response.success) {
				$button
					.text('Activated')
					.removeClass('e-btn e-info e-btn-1')
					.addClass('elementor-disabled')
					.prop('disabled', true);

				$wrapper.find('.elementor-control-notice-success').remove();
				$wrapper.find('.elementor-control-notice-main-actions').after(
					'<div class="elementor-control-notice elementor-control-notice-success">' +
					'<div class="elementor-control-notice-content">' +
					'Save & reload the page to start using DynamicKit for Elementor.' +
					'</div></div>'
				);
			} else {
				var msg = (response && response.data && response.data.errorMessage)
					? response.data.errorMessage
					: 'The plugin could not be installed. Please install it manually from the Plugins menu.';

				$button.text('Install DynamicKit for Elementor').prop('disabled', false);

				$wrapper.find('.elementor-control-notice-success').remove();
				$wrapper.find('.elementor-control-notice-main-actions').after(
					'<div class="elementor-control-notice elementor-control-notice-success">' +
					'<div class="elementor-control-notice-content">' + msg + '</div></div>'
				);
			}
		}).fail(function () {
			$button.text('Install DynamicKit for Elementor').prop('disabled', false);
		});
	}

	function dismissNotice($button) {
		var nonce = $button.data('nonce');
		var $wrapper = $button.closest('.dwel-dynamickit-wrp');

		$.post(dwelMarketing.ajax_url, {
			action: 'dwel_dismiss_dynamickit_notice',
			nonce: nonce
		}, function (response) {
			if (response && response.success) {
				$wrapper.fadeOut();
			}
		});
	}

	if (typeof elementor !== 'undefined' && elementor) {
		var callbackfunction = elementor.modules.controls.BaseData.extend({
			onRender: function (data) {
				if (!data.el) return;

				var notice = data.el.querySelector('.dwel-dynamickit-wrp');
				if (!notice) return;

				var installBtn = notice.querySelector('button.dwel-install-dynamickit');
				var dismissBtn = notice.querySelector('button.dwel-dismiss-dynamickit');

				if (installBtn && !installBtn.dataset.dwelBound) {
					installBtn.dataset.dwelBound = '1';
					installBtn.addEventListener('click', function () {
						installPlugin($(installBtn));
					});
				}

				if (dismissBtn && !dismissBtn.dataset.dwelBound) {
					dismissBtn.dataset.dwelBound = '1';
					dismissBtn.addEventListener('click', function () {
						dismissNotice($(dismissBtn));
					});
				}
			}
		});

		$(window).on('elementor:init', function () {
			elementor.addControlView('raw_html', callbackfunction);
		});
	}
})(jQuery);
