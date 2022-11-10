<?php
/**
 * @var \yii\web\View $this
 * @var array $request
 * @var string $field_id
 * @var string $editor
 * @var string $startup
 */


use modules\media\MediaAssets;
use yii\web\View;

MediaAssets::register($this);

$this->title = Yii::t('media', 'Media Manager');

$multiple = !empty($request['multiple']) ? 1 : 0;
$js       = <<<JS
	var finder;
	var multiple = $multiple;
	CKFinder.start({
		chooseFiles: true,
		rememberLastFolder: false,
		startupPath: '{$startup}',
		startupFolderExpanded: true,
		onInit: function(instance){
		    finder = instance;
		    
			finder.on('files:choose', function( evt ) {
				var files = evt.data.files;
				if(!multiple){
				    var selected = files.first().getUrl();
				}else{
					var selected = [];
					for (i = 0; i < files.length; i++){
					    selected.push(files.models[i].getUrl());
					}
					
					selected = JSON.stringify(selected);
				}
				
				$.SendFile(selected, '$editor', '$field_id', false);
			});

			finder.on('file:choose:resizedImage', function( evt ) {
				files = [evt.data.resizedUrl];
				$.SendFile(files, '$editor', '$field_id', false);
			});
		}
	});
JS;
$this->registerJs($js, View::POS_END);