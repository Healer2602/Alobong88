<?php

/**
 * @var \yii\web\View $this
 * @var \modules\internet_banking\models\Bank $model
 */

$this->title = Yii::t('internet_banking', 'New Bank');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Settings'),
	'url'   => ['/setting/index']
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('internet_banking', 'Bank Management'),
	'url'   => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('_form', [
	'model' => $model
]) ?>