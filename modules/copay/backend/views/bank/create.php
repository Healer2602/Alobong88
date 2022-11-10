<?php

/**
 * @var \yii\web\View $this
 * @var \modules\copay\models\Bank $model
 */

$this->title = Yii::t('copay', 'New Bank');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Settings'),
	'url'   => ['/setting/index']
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('copay', 'Copay Banks'),
	'url'   => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('_form', [
	'model' => $model
]) ?>