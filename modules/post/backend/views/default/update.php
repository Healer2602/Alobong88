<?php

/**
 * @var $this yii\web\View
 * @var $model \modules\post\backend\models\Press
 */

use yii\helpers\Html;

$this->title = Yii::t('post', 'Update Press');

$this->params['breadcrumbs'][] = ['label' => Yii::t('post', 'Press'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->can('blog upsert')){
	$this->params['primary_link'] = Html::a(Yii::t('post', 'New blog'), ['create'],
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
