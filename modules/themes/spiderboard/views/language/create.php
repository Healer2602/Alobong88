<?php

/**
 * @var \backend\base\View $this
 * @var \common\models\Language $model
 */

$this->title = Yii::t('common', 'New Language');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Settings'),
	'url'   => ['/setting/index']
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Languages'),
	'url'   => ['language/index']
];

$this->params['breadcrumbs'][] = $this->title;

?>

<?= $this->render('_form', [
	'model' => $model
]) ?>