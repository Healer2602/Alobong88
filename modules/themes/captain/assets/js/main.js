(function ($) {
	"use strict";

	var fn = {
		Slick: function () {
			$('.slider').slick({
				slidesToShow: 3,
				slidesToScroll: 1,
				arrows: true,
				cssEase: 'ease',
				edgeFriction: 0.5,
				speed: 500,
				infinite: true,
				responsive: [
					{
						breakpoint: 991,
						settings: {
							slidesToShow: 1,
							arrows: false,
							dots: true
						},
					},
				],
			});
		},

		CustomSlick: function () {
			$('.custom-slider').slick({
				slidesToScroll: 1,
				arrows: true,
				cssEase: 'ease',
				edgeFriction: 0.5,
				speed: 500,
				infinite: false,
				variableWidth: true,
				responsive: [
					{
						breakpoint: 1600,
						settings: {
							slidesToShow: 10,
						},
					},
					{
						breakpoint: 767,
						settings: {
							slidesToShow: 3,
						},
					},
				],
			});

			$('.custom-slider .slick-prev').hide();
			var slideno = $('.custom-slider .nav-link.active').parents('.slick-slide').attr('data-slick-index');
			$('.custom-slider').slick('slickGoTo', slideno);
		},

		Select2: function () {
			$('.select2').select2();
		},

		ShowFilter: function () {
			$('.btn-filter').click(function () {
				$(this).parents('.custom-tabs').find('.filters').slideToggle();
			})
		},

		QuickChose: function () {
			$(document).on('click', ".quick-chose label", function () {
				let amount = $(this).find("input[name='quick_options']").val();
				$(this).parents('form').find('#deposit-amount').val(parseInt(amount));
			});
		},

		Copy: function (text) {
			$(document).on('click', "input.icon-copy", function () {
				$(this).select();
				document.execCommand("copy");
				alert(text);
			});
		},

		CopyCode: function () {
			$(document).on('click', ".copy-code", function () {
				let text = $(this).parents('.input-group').find('.ref-code').val();
				navigator.clipboard.writeText(text);
			});
		},

		PreviewImage: function () {
			$(".custom-file-input").on('change', function () {
				readURL(this);
			});

			function readURL(input) {
				let preview = $(input).parents('.input-group').find('.preview-image');

				if (input.files && input.files[0]) {
					var reader = new FileReader();

					reader.onload = function (e) {
						let img = $('<img>').attr('src', e.target.result);
						preview.html(img);
					};

					reader.readAsDataURL(input.files[0]);
				}
			}

			$('input[type="file"]').change(function (e) {
				var fileName = e.target.files[0].name;
				var hint = $(".hint");
				hint.html(fileName)
			});
		},

		MenuLeft: function () {
			let menu = $('.menu-left');
			let item = $('.my-menu-item');
			$('.nav-link.active').parents('.my-menu-item').addClass('active');

			item.click(function () {
				$(this).addClass('active').siblings().removeClass("active");
			});
		},

		FlatPickr: function () {
			var e = document.querySelectorAll('[data-toggle="flatpickr"]');
			"undefined" != typeof flatpickr && e && [].forEach.call(e, function (e) {
				var t, a;
				a = (a = (t = e).dataset.options) ? JSON.parse(a) :
					{}, flatpickr(t, a)
			})
		},

		ReorderMenu: function () {
			let menu = $('.custom-menu:not(.vendors)');

			menu.find('.active').parent('.nav-item').css('order', -1);

			$('.custom-menu .nav-item:first').css('order', -2);
		},

		ShowMore: function () {
			let element = $('.bank-listing .list-show .item:nth-child(6)');
			if ($(window).width() < 767) {
				element = $('.bank-listing .list-show .item:nth-child(4)');
			}
			let hide_item = element.nextAll('.item').hide();
			$('.more-bank').click(function () {
				hide_item.show();
				$(this).hide();
			});
		},

		MMenu: function () {
			var imgFullURL = $('.navbar-brand img')[0].src;

			$("#menu-top-menu").mmenu({
				navbars: [
					{
						content: ["<div><img src=" + imgFullURL + "></div>"]
					}
				],
			}, {
				offCanvas: {
					clone: true,
					page: {
						selector: "#page"
					}
				}
			});
		},
	};

	$(document).ready(function () {
		if ($('.slider').length) {
			fn.Slick();
		}

		if ($('.custom-slider').length) {
			fn.CustomSlick();
		}

		if ($('.select2').length) {
			fn.Select2();
		}

		fn.ShowFilter();
		fn.QuickChose();
		if (typeof textCopy !== 'undefined') {
			fn.Copy(textCopy);
		}
		fn.CopyCode();
		fn.PreviewImage();
		fn.FlatPickr();
		fn.ShowMore();
		fn.MMenu();

		if ($(window).width() < 991) {
			fn.MenuLeft();
			fn.ReorderMenu();
		}
	});

	$(document).on("pjax:success", "#bank-transfer", function () {
		fn.ShowMore();
	});

})(jQuery);