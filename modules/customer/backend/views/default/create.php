<?php
/**
 * @var \yii\web\View $this
 * @var \modules\customer\models\Customer $model
 */

$this->title = Yii::t('customer', 'New Player');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('customer', 'Players'),
	'url'   => ['index']
];

$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row justify-content-center">
	<div class="col-lg-7 col-xl-5">
		<?= $this->render('_form', [
			'model' => $model
		]) ?>
	</div>
</div>
