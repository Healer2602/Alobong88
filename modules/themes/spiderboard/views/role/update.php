<?php

use yii\helpers\Html;

/** @var \backend\models\UserGroup $model */
/** @var \yii\web\View $this */

$this->title = Yii::t('common', 'Update Role: {0}', [$model->name]);

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Staffs'),
	'url'   => ['users/index']
];

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Roles'),
	'url'   => ['index']
];

$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->can('role upsert')){
	$this->params['primary_link'] = Html::a(Yii::t('common', 'New Role'), ['create'],
		['class' => 'btn btn-new btn-primary']);
}

?>

<?= $this->render('_form', [
	'model'       => $model,
	'permissions' => $permissions
]) ?>