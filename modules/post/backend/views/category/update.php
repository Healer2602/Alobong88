<?php

/**
 * @var yii\web\View $this
 * @var \modules\post\models\Category $model
 */

use yii\bootstrap5\Html;

$this->title = Yii::t('post', 'Update Category: {0}', [$model->name]);

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('post', 'Press'),
	'url'   => ['/post/default/index']
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('post', 'Press Categories'),
	'url'   => ['index']
];
$this->params['breadcrumbs'][] = $this->title;

$this->params['primary_link'] = '';
if (Yii::$app->user->can('post category upsert')){
	$this->params['primary_link'] = Html::a(Yii::t('post', 'New Category'),
		['create'],
		['class' => 'btn btn-new btn-primary mr-2']);
}

if ($public_urls = $model->publicUrls){
	$this->params['primary_link'] .= Html::a($public_urls['label'], $public_urls['url'],
		['class' => 'btn btn-outline-primary ml-auto', 'target' => '_blank']);
}
?>

<div class="product-category-form">
	<?= $this->render('_form', [
		'model' => $model
	]) ?>
</div>
