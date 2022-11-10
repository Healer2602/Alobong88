<?php

use yii\helpers\Html;

/**
 * @var \backend\base\View $this
 * @var \backend\models\UserForm $model
 */

$this->title = Yii::t('common', 'Update Staff: {0}', [$model->username]);

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Staffs'),
	'url'   => ['users/index']
];

$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->can('user upsert')){
	$this->params['primary_link'] = Html::a(Yii::t('common', 'New Staff'), ['create'],
		['class' => 'btn btn-new btn-primary lift']);
}
?>

<?= $this->render('_form', [
	'model' => $model
]) ?>