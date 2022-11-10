<?php
/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $data
 * @var array $filtering
 * @var array $filters
 */

use backend\base\GridView;
use yii\grid\SerialColumn;
use yii\helpers\Html;

$this->title = Yii::t('promotion', 'Player Promotions');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('promotion', 'Promotions'),
	'url'   => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
?>

	<div class="filter">
		<form class="form-inline" action="" method="get">
			<div class="input-group">
				<label class="control-label"><?= Yii::t('promotion', 'Promotion Title') ?></label>
				<?= Html::textInput('s', $filtering['s'] ?? NULL,
					['class' => 'form-control', 'placeholder' => Yii::t('common',
						'Promotion Title')]) ?>
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
		[
			'attribute' => 'player_id',
			'value'     => 'player.email'
		],
		[
			'attribute' => 'promotion_id',
			'value'     => 'promotion.name'
		],
		[
			'attribute' => 'promotion_type',
			'value'     => 'promotion.typeLabel'
		],
		'joined_at:datetime',
		'expired_at:datetime',
		[
			'attribute' => 'totalTurnover',
			'format'    => 'currency'
		],
		'round:integer',
		[
			'attribute' => 'status',
			'value'     => 'statusLabel',
			'format'    => 'html'
		],
		[
			'format'  => 'raw',
			'visible' => Yii::$app->user->can('promotion cancel'),
			'value'   => function ($data){
				if ($data->canCancel()){
					return Html::a(Yii::t('promotion', 'Cancel'),
						['cancel', 'id' => $data['id']],
						['class'        => 'btn btn-danger btn-sm',
						 "data-confirm" => "Are you sure you want to cancel this promotion?",
						 'data-method'  => 'post']
					);
				}

				return '';
			}
		]
	]
]);