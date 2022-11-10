<?php

use yii\helpers\Html;

/**
 * @var \backend\base\View $this
 * @var \modules\wallet\models\Gateway $model
 */

$this->title = Yii::t('wallet', 'Update Gateway: {0}', [$model->name]);

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Settings'),
	'url'   => ['/setting/index']
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('wallet', 'Deposit Gateways'),
	'url'   => ['index']
];
$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->can('wallet_withdraw_gateway upsert')){
	$this->params['primary_link'] = Html::a(Yii::t('wallet', 'New Gateway'), ['create'],
		['class' => 'btn btn-new btn-primary']);
}
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