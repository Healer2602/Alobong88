<?php
/**
 * @var \yii\web\View $this
 * @var \modules\game\models\Game $model
 */

$this->title = Yii::t('game', 'New Game');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('game', 'Games'),
	'url'   => ['index']
];

$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row justify-content-center">
	<div class="col-lg-7">
		<div class="m-portlet">
			<?= $this->render('_form', [
				'model' => $model
			]) ?>
		</div>
	</div>
</div>