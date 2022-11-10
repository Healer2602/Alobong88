<?php

/**
 * @var yii\web\View $this
 * @var \modules\block\models\Block $model
 */

use modules\media\widgets\MediaInputModal;
use yii\bootstrap5\Html;

?>

	<div class="block-form m-portlet">
		<div class="form-group">
			<label><?= Yii::t('block', 'Background Image') ?></label>
			<?= MediaInputModal::widget([
				'model'        => $model,
				'current_path' => 'page',
				'attribute'    => 'image'
			]) ?>
		</div>
		<div class="form-group">
			<?= Html::activeLabel($model, 'content') ?>
			<?= Html::activeTextarea($model, 'content',
				['class' => 'form-control editor', 'rows' => 15]) ?>
			<?= Html::error($model, 'content', ['class' => 'invalid-feedback']) ?>
		</div>
	</div>
<?php
$js = <<< JS
// call back modal
 $('.modal-ajax').on('shown.bs.modal', function (e) {
    var button = $(e.relatedTarget);
    var href = button.attr('href');

    if (!href && e.relatedTarget.localName.toLowerCase() != 'a') {
        href = button.find('a').first().attr('href');
    }

    if (typeof href !== 'undefined') {
        var modal = $(this);
        modal.find('.modal-body').html('<div class="loading"></div>.');

        if (button.data('header')) {
            modal.find('.modal-header h5').text(button.data('header'));
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

//call back editor
tinyInit();
JS;

$this->registerJs($js);
