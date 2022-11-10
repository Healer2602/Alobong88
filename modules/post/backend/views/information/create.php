<?php

/**
 * @var yii\web\View $this
 * @var \modules\post\models\Information $model
 */

$this->title = Yii::t('post', 'New Information');

$this->params['breadcrumbs'][] = ['label' => Yii::t('post', 'Information'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="post-create">
	<?= $this->render('_form', [
		'model' => $model,
	]) ?>
</div>
