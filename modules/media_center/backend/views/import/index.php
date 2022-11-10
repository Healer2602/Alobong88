<?php
/**
 * @var yii\web\View $this
 * @var \modules\media_center\backend\models\ImportForm $model
 */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = Yii::t('media_center', 'Import');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('media_center', 'Media Center'),
	'url'   => ['default/index']
];

$this->params['breadcrumbs'][] = $this->title;
?>

	<div class="row justify-content-center">
		<div class="col-lg-6 col-xl-4">
			<?php $form = ActiveForm::begin() ?>

			<?= $form->field($model, 'importer')->dropDownList($model->importers, [
				'data-toggle' => 'select',
				'id'          => 'importer'
			]) ?>

			<?= $form->field($model, 'file')->fileInput() ?>

			<?= Html::submitButton(Yii::t('media_center', 'Import'),
				['class' => 'btn btn-primary w-100']) ?>

			<?= Html::a(Yii::t('media_center', 'Cancel'), ['default/index'],
				['class' => 'btn btn-link text-muted w-100']) ?>

			<?php ActiveForm::end() ?>
		</div>
	</div>

<?php
$url = Url::to(['download']);
$js  = <<<JS
	$('#download-import-template').on('click', function (){
		let importer = $('#importer').val();
		let url = '{$url}?importer=' + importer;
		
		return window.open(url, '_blank');
	});
JS;

$this->registerJs($js);