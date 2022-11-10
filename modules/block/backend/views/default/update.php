<?php

/**
 * @var yii\web\View $this
 * @var \modules\block\backend\models\BlockModel $model
 */

$this->title = Yii::t('block', 'Update: {0}', [$model->name]);

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('block', 'Blocks'),
	'url'   => ['index']
];

$this->params['breadcrumbs'][] = $this->title;
?>

<div class="block-update">
	<div class="row justify-content-center">
		<div class="col-lg-7">
			<?= $this->render('_form', [
				'model' => $model,
			]) ?>
		</div>
	</div>
</div>
