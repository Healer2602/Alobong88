<?php

use yii\helpers\Html;

/**
 * @var \yii\web\View $this
 * @var \modules\internet_banking\models\Bank $model
 */


$this->title = Yii::t('internet_banking', 'Update Bank: {0}', [$model->name]);

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Settings'),
	'url'   => ['/setting/index']
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('internet_banking', 'Bank Management'),
	'url'   => ['index']
];
$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->can('internet_banking bank upsert')){
	$this->params['primary_link'] = Html::a(Yii::t('internet_banking', 'New Bank'), ['create'],
		[
			'class'       => 'btn btn-new btn-primary lift',
			'data-toggle' => "modal",
			'data-target' => "#global-modal",
			'data-header' => Yii::t('internet_banking', 'New Bank')
		]);
}

echo $this->render('_form', [
	'model' => $model
]);