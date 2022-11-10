(function ($) {
    $(document).ready(function () {
        $('.nav-tabs a').on('click', function () {
            if (!($(this).data('toggle') && $(this).data('toggle') == 'modal')) {
                location.href = $(this).attr('href');
            }
        });

        var hash = window.location.hash;
        if (hash.length > 0) {
            $('a[href="' + hash + '"]').tab('show');
        }

        $(document).on('click', 'button.clear', function (event) {
            event.preventDefault();
            var form = $(this).parents('form');
            form.find('input[type="text"], input[type="checkbox"]').attr('disabled', 'disabled');
            form.find('select').attr('disabled', 'disabled');
            form.find('.btn-filter').addClass('disabled');
            form.trigger('submit');
        });

        $('.filter form select, .filter form .auto').on('change', function () {
            $(this).parents('form').trigger('submit');
        });

        $('.select2').on('depdrop:afterChange', function (event, id, value, jqXHR, textStatus) {
            $(this).trigger('change');
        });

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

                        $('[data-toggle="select"]').on('depdrop:afterChange', function (event, id, value, jqXHR, textStatus) {
                            $(this).trigger('change');
                        });

                        tinyInit();
                    }
                });
            }
        });

        $(document).on('hidden.bs.modal', '.modal', function () {
            $('.modal.in').length && $(document.body).addClass('modal-open');
        });

        $(document).on('click', '.input-group-addon', function (event) {
            event.preventDefault();
            $(this).parents('.input-group').find('input').first().focus();
        });


        $('.filter .btn-filter.dropdown-toggle').each(function () {
            var selected_label = [];

            $(this).next(".dropdown-menu").find("input[type='checkbox']:checked").each(function (index) {
                selected_label[index] = $(this).parents('label').text();
            });

            if (selected_label.length) {
                $(this).find('span.selected').text(selected_label.join(', '));
            }
        });

        $('.filter .dropdown-menu input').on('change', function () {
            var selected_label = [];

            $(this).parents(".dropdown-menu").find("input[type='checkbox']:checked").each(function (index) {
                selected_label[index] = $(this).parents('label').text();
            });

            var label = $(this).parents(".input-group").find('span.selected');
            if (selected_label.length) {
                label.text(selected_label.join(', '));
            }
            else {
                label.text('All');
            }
        });

        $(document).on('click', '.filter .dropdown-menu', function (e) {
            e.stopPropagation();
        });
    });

})(jQuery);

var loadFile = function (event) {
    var output = document.getElementById('output');
    output.src = URL.createObjectURL(event.target.files[0]);
};

function uploadFile() {
    jQuery('#upload-image').click();
}