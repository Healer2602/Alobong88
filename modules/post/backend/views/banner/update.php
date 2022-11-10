<?php

/**
 * @var yii\web\View $this
 * @var \modules\post\backend\models\Banner $model
 */

use yii\helpers\Html;

$this->title = Yii::t('post', 'Update Banner');

$this->params['breadcrumbs'][] = ['label' => Yii::t('post', 'Banner'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->can('banner upsert')){
	$this->params['primary_link'] = Html::a(Yii::t('post', 'New Banner'), ['create'],
		['class' => 'btn btn-new btn-primary']);
}
?>
<div class="post-update">
	<?= $this->render('_form', [
		'model' => $model,
	]) ?>
</div>
