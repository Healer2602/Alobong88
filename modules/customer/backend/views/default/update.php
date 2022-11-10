<?php
/**
 * @var \yii\web\View $this
 * @var \modules\customer\models\Customer $model
 * @var \yii\db\ActiveRecord $details
 */

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = Yii::t('customer', 'Update Player');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('customer', 'Players'),
	'url'   => ['index']
];

$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->can('customer upsert')){
	$this->params['primary_link'] = Html::a(Yii::t('customer', 'New Player'), ['create'],
		['class' => 'btn btn-new btn-primary lift']);
}
?>

<div class="row justify-content-center">
	<div class="col-lg-6">
		<div class="card">
			<div class="card-header">
				<h4 class="card-header-title"><?= Yii::t('customer',
						'Player Information') ?></h4>
			</div>
			<div class="card-body">
				<?= $this->render('_form', [
					'model' => $model
				]) ?>
			</div>
		</div>
	</div>
	<div class="col-lg-6">
		<div class="card">
			<div class="card-header">
				<h4 class="card-header-title"><?= Yii::t('customer',
						'Banks') ?></h4>
			</div>
			<?= GridView::widget([
				'dataProvider' => $details,
				'tableOptions' => ['class' => 'table table-nowrap card-table'],
				'summary'      => '',
				'columns'      => [
					[
						'attribute' => 'bank',
						'format'    => 'html',
						'value'     => function ($data){
							return Yii::$app->formatter->asImage($data->bank->logo,
									['style' => 'max-height: 50px; margin-right: 0.5rem']) . Html::encode($data->bank->name);
						}
					],
					'account_id',
					'account_name',
					'account_branch',
				]
			]) ?>
		</div>
	</div>
</div>