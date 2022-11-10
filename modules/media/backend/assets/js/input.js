(function ($) {
	$(document).on('click', '.btn-remove', function () {
		$(this).parents('.media-input-group').find('input').val('');
		$(this).parents('.media-box').find('.media-preview').empty();
	});

	$(document).on('click', '.btn-browse', function (event) {
		event.preventDefault();
		let target = $(this).data('target');
		if (target.length) {
			$(target).addClass('show');
			$('.media-modal-backdrop').remove();
			$('body').append('<div class="media-modal-backdrop fade show"></div>');
			$.ajax({
				type: 'POST',
				url: $(this).attr('href'),
				success: function (result) {
					$(target).find('.modal-body').html(result);
				}
			});
		}

		return false;
	});

	$('.media-dialog').on('click', '.btn-close', function () {
		let field_id = $(this).prev('.media-input-group').find('input.media-input');
		let obj = JSON.parse(JSON.stringify({
			sender: 'mediaDialogModal',
			field: field_id
		}));

		window.postMessage(obj, window.location.origin);
	});

	$(".media-preview.multiple").sortable({
		cursor: "move"
	});

	$(".media-preview.multiple").disableSelection();

	$(".media-preview.multiple").on("sortstop", function (event, ui) {
		var target = $(event.target);
		var images = target.find('img').map(function () {
			return $(this).attr('src');
		}).get();

		target.parents('.media-box').find('.media-input').val(JSON.stringify(images));
	});

	if (window.addEventListener) {
		window.addEventListener('message', mediaOnMessage, false);
	}
	else {
		window.attachEvent('onmessage', mediaOnMessage);
	}

	function mediaOnMessage(event) {
		if (event.data.sender === 'mediaDialogModal') {
			if (typeof event.data.field !== "undefined" && event.data.field.length) {
				media_callback(event.data.field.attr('id'));
			}

			$('.media-dialog').removeClass('show');
			$('.media-modal-backdrop').remove();
		}
	}
})(jQuery);

function media_callback(field_id) {
	var field = $("#" + field_id);
	var files = field.val();
	var media_preview = field.parents('.media-box').find('.media-preview');
	var file_names = [];
	media_preview.empty();

	if (files.length) {
		try {
			files = $.parseJSON(files);
		}
		catch (e) {
			files = [files];
		}

		$.each(files, function (i, file) {
			file_name = file.split('/').pop();
			file_names.push(file_name);
			file_element = $('<span class="name"></span>').text(file_name);
			preview = $('<div class="file"></div>');

			if (isImage(file)) {
				img = $('<img>').attr('src', file);
				preview.append(img).addClass('image');
			}
			else {
				extension = file.split('.').pop();
				preview.html('<span class="type">' + extension + '</span>').addClass(extension);
				preview.append(file_element);
			}

			var elem = $('<div class="media-item"></div>').html(preview);

			media_preview.append(elem);
		});

		field.siblings('.form-message').val(files.length + ' file(s) selected');
	}

	var browse_button = field.parent('div').find('.btn-browse');
	browse_url = browse_button.attr('href');
	new_browse_url = changeBrowseURL(browse_url, file_names);
	browse_button.attr('href', new_browse_url);
	field.trigger('change');
}

function isImage(url) {
	return (url.match(/\.(jpeg|jpg|gif|png|svg)$/) != null);
}

function changeBrowseURL(browse_url, file_names) {
	var selected_file = JSON.stringify(file_names);

	var url = new URL(browse_url, location.origin);
	var query_string = url.search;
	var search_params = new URLSearchParams(query_string);
	search_params.set('selected', btoa(selected_file));
	url.search = search_params.toString();

	return url.toString();
}