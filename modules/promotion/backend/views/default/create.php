<?php

/**
 * @var yii\web\View $this
 * @var \modules\promotion\models\Promotion $model
 */

$this->title = Yii::t('post', 'New Promotion');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('promotion', 'Promotions'),
	'url'   => ['index']
];

$this->params['breadcrumbs'][] = $this->title;
?>

<div class="post-create">
	<div class="row justify-content-center">
		<div class="col-md-8 col-xxl-7">
			<div class="m-portlet">
				<?= $this->render('_form_' . $model->type, [
					'model' => $model,
				]) ?>
			</div>
		</div>
	</div>
</div>