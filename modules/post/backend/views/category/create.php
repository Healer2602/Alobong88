<?php

/**
 * @var yii\web\View $this
 * @var \modules\post\models\Category $model
 */

$this->title = Yii::t('post', 'Press Categories');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('post', 'Press'),
	'url'   => ['/post/default/index']
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('post', 'Press Categories'),
	'url'   => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="new-category">
	<?= $this->render('_form', [
		'model' => $model
	]) ?>
</div>
