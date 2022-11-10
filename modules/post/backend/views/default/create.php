<?php

/**
 * @var yii\web\View $this
 * @var \modules\post\models\Post $model
 */

$this->title = Yii::t('post', 'New Press');

$this->params['breadcrumbs'][] = ['label' => Yii::t('post', 'Press'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="post-create">
	<?= $this->render('_form', [
		'model' => $model,
	]) ?>
</div>
