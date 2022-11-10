(function ($) {
	"use strict";

	$(document).ready(function () {
		$('.btn-cancel').on('click', function () {
			closeWindow();
		});
	});

	$.FileSize = function (bytes, si) {
		var si = typeof si !== 'undefined' ? si : false;
		var thresh = si ? 1000 : 1024;
		if (Math.abs(bytes) < thresh) {
			return bytes + ' B';
		}
		var units = si
			? ['kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
			: ['KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
		var u = -1;
		do {
			bytes /= thresh;
			++u;
		} while (Math.abs(bytes) >= thresh && u < units.length - 1);
		return bytes.toFixed(1) + ' ' + units[u];
	}

	$.FileIcon = function (name) {
		var ext = name.split('.').pop().substring(0, 3);
		var exts = ['doc', 'ppt', 'xls', 'pdf'];
		if ($.inArray(ext, exts) >= 0) {
			return ext;
		}

		return 'txt';
	}

	$.SendFile = function (url, type, field, multiple, index) {
		var index = typeof index !== 'undefined' ? index : -1;

		if (type == 'ckeditor') {
			var funcNum = getUrlParam('CKEditorFuncNum');
			window.opener.CKEDITOR.tools.callFunction(funcNum, url);
			window.close();
		}
		else if (type == 'tinymce') {
			window.parent.postMessage({
				sender: 'responsivefilemanager',
				url: url,
				field_id: null
			}, window.location.origin);
			parent.tinymce.activeEditor.windowManager.close();
		}
		else {
			var windowParent = window.parent;
			var target = $('#' + field, windowParent.document);

			if (multiple) {
				var current = target.val();
				var current_arr = [];
				if (current) {
					try {
						current_arr = $.parseJSON(current);
					}
					catch (e) {
						current_arr = [current];
					}
				}

				try {
					url = $.parseJSON(url);
				}
				catch (e) {
					url = [url];
				}

				if (index >= 0) {
					current_arr[index] = url[0];
					url = current_arr;
				}
				else {
					url = current_arr.concat(url);
				}

				url = JSON.stringify(url);
			}

			target.val(url).trigger('change');
			if (typeof windowParent.media_callback == 'function') {
				windowParent.media_callback(field);
			}

			closeWindow();
		}
	};

	function closeWindow() {
		window.parent.postMessage({
			sender: 'mediaDialogModal'
		}, window.location.origin);
	}

	function getUrlParam(paramName) {
		var reParam = new RegExp('(?:[\?&]|&)' + paramName + '=([^&]+)', 'i');
		var match = window.location.search.match(reParam);

		return (match && match.length > 1) ? match[1] : null;
	}

})(jQuery);