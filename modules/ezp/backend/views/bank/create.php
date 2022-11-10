<?php

/**
 * @var \yii\web\View $this
 * @var \modules\ezp\models\Bank $model
 */

$this->title = Yii::t('ezp', 'New Bank');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Settings'),
	'url'   => ['/setting/index']
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('ezp', 'EeziePay Banks'),
	'url'   => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('_form', [
	'model' => $model
]) ?>