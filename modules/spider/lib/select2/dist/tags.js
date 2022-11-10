//
// select2.js
// Theme module
//

'use strict';

(function () {

	//
	// Variables
	//

	var selects = document.querySelectorAll('[data-toggle="tags"]');

	//
	// Functions
	//

	function initTags(select) {
		var elementOptions = select.dataset.options ? JSON.parse(select.dataset.options) : {};

		var defaultOptions = {
			containerCssClass: select.getAttribute('class'),
			dropdownAutoWidth: true,
			dropdownCssClass: select.classList.contains('custom-select-sm') || select.classList.contains('form-control-sm') ? 'dropdown-menu dropdown-menu-sm show' : 'dropdown-menu show',
			dropdownParent: select.closest('.modal-body') || document.body,
			templateResult: formatTemplate,
			tags: true
		};

		var options = Object.assign(defaultOptions, elementOptions);

		// Init
		$(select).select2(options);
	}

	function formatTemplate(item) {

		// Quit if there's no avatar to display
		if (!item.id || !item.element || !item.element.dataset.avatarSrc) {
			return item.text;
		}

		var avatar = item.element.dataset.avatarSrc;
		var content = document.createElement('div');

		content.innerHTML = '<span class="avatar avatar-xs mr-3"><img class="avatar-img rounded-circle" src="' + avatar + '" alt="' + item.text + '"></span><span>' + item.text + '</span>';

		return content;
	}

	//
	// Events
	//

	if (jQuery().select2 && selects) {
		[].forEach.call(selects, function (select) {
			initTags(select);
		});
	}

	$.tag2 = function (elem) {
		if (jQuery().select2) {
			[].forEach.call(elem, function (select) {
				initTags(select);
			});
		}
	};

})();