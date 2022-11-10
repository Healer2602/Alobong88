var _skin = 'oxide';
if ($('body').hasClass('darkmode')) {
	_skin = 'oxide-dark';
}

function tinyInit() {
	tinymce.remove();

	module_path = 'editor';
	if (typeof mediaModule !== 'undefined') {
		module_path = mediaModule;
	}

	let base_url = '/';
	if (typeof baseUrl !== 'undefined') {
		base_url = baseUrl;
	}

	tinymce.init({
		selector: '.editor',
		skin: _skin,
		height: 500,
		plugins: [
			'advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker',
			'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
			'save table directionality emoticons template paste',
			'toc filemanager'
		],
		toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor | toc wpadblock',
		toc_class: "our-toc",
		external_filemanager_path: base_url + "media",
		filemanager_title: "Media Manager",
		filemanager_subfolder: module_path,
		setup: function (editor) {
			if ($('#' + editor.id).prop('disabled') || $('#' + editor.id).prop('readonly')) {
				editor.settings.readonly = true;
			}

			editor.ui.registry.addButton('wpadblock', {
				tooltip: 'WPAdBlock',
				icon: 'stop',
				onAction: function () {
					tinymce.activeEditor.formatter.toggle('wpadblock_format');
				}
			});
		},
		extended_valid_elements: "i[class|style],span[itemscope|itemtype|id|class|style],[style]",
		content_css: [tinymce.baseURL + "/LineAwesome/css/line-awesome.min.css", tinymce.baseURL + "/skins/content/default/content.min.css"],
		formats: {
			wpadblock_format: {
				inline: 'span',
				attributes: {
					'itemscope': '0',
					'itemtype': 'https://schema.org/WPAdBlock',
					'id': 'wpadblock-' + Math.floor((1 + Math.random()) * 0x10000)
				}
			}
		},
	});
}