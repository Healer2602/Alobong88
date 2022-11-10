<?php
/**
 * @var \yii\web\View $this
 * @var \modules\customer\models\Kyc $model
 */

$this->title = Yii::t('customer', 'Detail Customer eKYC');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('customer', 'Players'),
	'url'   => ['default/index']
];

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('customer', 'eKYC'),
	'url'   => ['index']
];

$this->params['breadcrumbs'][] = $this->title;

?>

<div class="row justify-content-center">
	<div class="col-lg-7">
		<div class="m-portlet">
			<?= $this->render('_form', [
				'model' => $model
			]) ?>
		</div>
	</div>
</div>