<?php

/**
 * @var \yii\web\View $this
 * @var \modules\wallet\models\Bank $model
 */

$this->title = Yii::t('wallet', 'New Bank');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Settings'),
	'url'   => ['/setting/index']
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('wallet', 'Banks'),
	'url'   => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('_form', [
	'model' => $model
]) ?>