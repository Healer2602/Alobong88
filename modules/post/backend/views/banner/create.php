<?php

/**
 * @var yii\web\View $this
 * @var \modules\post\backend\models\Banner $model
 */

$this->title = Yii::t('post', 'New Banner');

$this->params['breadcrumbs'][] = ['label' => Yii::t('post', 'Banners'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="post-create">
	<?= $this->render('_form', [
		'model' => $model,
	]) ?>
</div>
