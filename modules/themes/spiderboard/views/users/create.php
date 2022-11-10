<?php

/**
 * @var \backend\base\View $this
 * @var \backend\models\UserForm $model
 */

$this->title = Yii::t('common', 'New Staff');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Staffs'),
	'url'   => ['users/index']
];

$this->params['breadcrumbs'][] = $this->title;

?>

<?= $this->render('_form', [
	'model' => $model
]) ?>