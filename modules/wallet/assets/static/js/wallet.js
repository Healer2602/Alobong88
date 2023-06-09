(function ($) {
	$(document).ready(function () {
		$('.modal-ajax').on('show.bs.modal', function (e) {
			var button = $(e.relatedTarget);
			var href = button.attr('href');
			if (!href && e.relatedTarget.localName.toLowerCase() != 'a') {
				href = button.find('a').first().attr('href');
			}

			if (typeof href !== 'undefined') {
				var modal = $(this);
				modal.find('.modal-body').html('<div class="spinner-grow text-primary" role="status">\n' +
					'        <span class="sr-only">Loading...</span>\n' +
					'    </div>');

				if (button.data('header')) {
					modal.find('.modal-header h5').text(button.data('header'));
				}

				if (button.data('size')) {
					modal.find('.modal-dialog').addClass(button.data('size'));
				}

				$.ajax({
					type: 'POST',
					url: href,
					success: function (result) {
						modal.find('.modal-body').html(result);
					}
				});
			}
		});

		$(document).on('hidden.bs.modal', '.modal', function () {
			$('.modal.in').length && $(document.body).addClass('modal-open');
		});
	});
})(jQuery);