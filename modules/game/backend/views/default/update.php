<?php
/**
 * @var \yii\web\View $this
 * @var \modules\game\models\Game $model
 */

use yii\helpers\Html;

$this->title = Yii::t('game', 'Update Game');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('game', 'Games'),
	'url'   => ['index']
];

$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->can('game upsert')){
	$this->params['primary_link'] = Html::a(Yii::t('game', 'New Game'), ['create'],
		['class' => 'btn btn-new btn-primary lift']);
}
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