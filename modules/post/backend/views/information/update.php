<?php

/**
 * @var $this yii\web\View
 * @var $model \modules\post\backend\models\Information
 */

use yii\helpers\Html;

$this->title = Yii::t('post', 'Update Information');

$this->params['breadcrumbs'][] = ['label' => Yii::t('post', 'Information'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->can('information upsert')){
	$this->params['primary_link'] = Html::a(Yii::t('post', 'New Information'), ['create'],
		['class' => 'btn btn-new btn-primary']);
}

if ($public_urls = $model->publicUrls){
	$this->params['primary_link'] .= Html::a($public_urls['label'], $public_urls['url'],
		['class' => 'btn btn-outline-primary ml-auto', 'target' => '_blank']);
}
?>
<div class="post-update">
	<?= $this->render('_form', [
		'model' => $model,
	]) ?>
</div>
