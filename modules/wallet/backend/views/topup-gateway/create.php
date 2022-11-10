<?php

/**
 * @var \backend\base\View $this
 * @var \backend\models\UserForm $model
 */

$this->title = Yii::t('wallet', 'New Deposit Gateway');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Settings'),
	'url'   => ['/setting/index']
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('wallet', 'Deposit Gateways'),
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