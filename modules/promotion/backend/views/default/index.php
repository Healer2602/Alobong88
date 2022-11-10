<?php
/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $data
 * @var array $filtering
 * @var array $filters
 */

use backend\base\GridView;
use modules\promotion\widgets\NewPromotionButtons;
use yii\grid\SerialColumn;
use yii\helpers\Html;

$this->title = Yii::t('promotion', 'Promotions');

$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->can('promotion upsert')){
	$this->params['primary_link'] = NewPromotionButtons::widget();
} ?>

	<div class="filter">
		<form class="form-inline" action="" method="get">
			<div class="input-group">
				<label class="control-label"><?= Yii::t('common', 'Keywords') ?></label>
				<?= Html::textInput('s', $filtering['s'] ?? NULL,
					['class' => 'form-control', 'placeholder' => Yii::t('common', 'Keywords')]) ?>
			</div>
			<div class="input-group">
				<label class="control-label"><?= Yii::t('promotion', 'Type') ?></label>
				<?= Html::dropDownList('type', $filtering['type'] ?? NULL,
					$filters['types'], [
						'class'       => 'form-select',
						'data-toggle' => 'select',
						'prompt'      => Yii::t('common', 'All')
					]) ?>
			</div>
			<div class="input-group">
				<label class="control-label"><?= Yii::t('promotion', 'Date') ?></label>
				<?= Html::textInput('date',
					$filtering['date'] ?? NULL,
					[
						'class'          => 'form-control',
						'data-flatpickr' => '{"mode": "range"}'
					]) ?>
			</div>
			<div class="input-group">
				<label class="control-label"><?= Yii::t('promotion', 'Status') ?></label>
				<?= Html::dropDownList('state', $filtering['state'] ?? NULL,
					$filters['states'], [
						'class'       => 'form-select',
						'data-toggle' => 'select',
						'prompt'      => Yii::t('common', 'All')
					]) ?>
			</div>
			<div class="input-group">
				<div class="input-group-btn">
					<button class="btn btn-outline-primary" type="submit">
						<i class="fe fe-search" aria-hidden="true"></i>
					</button>
					<button class="btn btn-outline-secondary clear" type="button">
						<i class="fe fe-x" aria-hidden="true"></i></button>
				</div>
			</div>
		</form>
	</div>

<?= GridView::widget([
	'dataProvider' => $data,
	'columns'      => [
		['class' => SerialColumn::class],
		'name:text',
		[
			'attribute' => 'type',
			'value'     => 'typeLabel'
		],
		'start_date:date',
		'end_date:date',
		[
			'attribute' => 'bonus_rate',
			'value'     => function ($data){
				return $data['bonus_rate'] / 100;
			},
			'format'    => 'percent'
		],
		[
			'attribute' => 'status',
			'value'     => 'statusLabel',
			'format'    => 'html'
		],
		[
			'format'  => 'raw',
			'visible' => Yii::$app->user->can('promotion upsert'),
			'value'   => function ($data){
				$result = '<div class="btn-group btn-group-sm" role="group">';
				$result .= Html::a('<i class="fe fe-edit-2"></i>',
					['update', 'id' => $data['id']],
					['class' => 'btn btn-secondary']
				);

				if (Yii::$app->user->can('promotion delete')){
					$result .= Html::a('<i class="fe fe-trash"></i>',
						['delete', 'id' => $data['id']],
						['class'        => 'btn btn-danger',
						 "data-confirm" => "Are you sure you want to delete this item?",
						 'data-method'  => 'post',]
					);
				}
				$result .= '</div>';

				return $result;
			}
		]
	]
]);