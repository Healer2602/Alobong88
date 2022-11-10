<?php

use yii\helpers\Html;

/**
 * @var \yii\web\View $this
 * @var \modules\wallet\models\Bank $model
 */


$this->title = Yii::t('wallet', 'Update Bank: {0}', [$model->name]);

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Settings'),
	'url'   => ['/setting/index']
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('wallet', 'Banks'),
	'url'   => ['index']
];
$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->can('wallet bank upsert')){
	$this->params['primary_link'] = Html::a(Yii::t('wallet', 'New Bank'), ['create'],
		[
			'class'       => 'btn btn-new btn-primary lift',
			'data-toggle' => "modal",
			'data-target' => "#global-modal",
			'data-header' => Yii::t('wallet', 'New Bank')
		]);
}

echo $this->render('_form', [
	'model' => $model
]);