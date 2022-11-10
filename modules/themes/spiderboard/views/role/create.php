<?php

/** @var \backend\models\UserGroup $model */
/** @var \yii\web\View $this */

$this->title = Yii::t('common', 'New Role');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Users'),
	'url'   => ['users/index']
];

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Roles'),
	'url'   => ['index']
];

$this->params['breadcrumbs'][] = $this->title;

?>

<?= $this->render('_form', [
	'model'       => $model,
	'permissions' => $permissions
]) ?>