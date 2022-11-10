'use strict';

(function () {
	const toggles = document.querySelectorAll('[data-flatpickr]');

	function initFlatpickr(toggle) {
		const _options = toggle.dataset.flatpickr ? JSON.parse(toggle.dataset.flatpickr) : {};
		let defaults = {};
		if (typeof flatpickrDefaults != "undefined") {
			defaults = flatpickrDefaults;
		}

		const options = Object.assign(defaults, _options);
		if (options.enableTime && options.hasOwnProperty('datetimeFormat')) {
			options.dateFormat = options.datetimeFormat;
		}

		toggle.flatpickr(options);
	}

	//
	// Events
	//

	if (jQuery().flatpickr && toggles) {
		[].forEach.call(toggles, function (select) {
			initFlatpickr(select);
		});
	}

	$.flatpickr = function (elem) {
		if (jQuery().flatpickr) {
			[].forEach.call(elem, function (select) {
				initFlatpickr(select);
			});
		}
	};
})();