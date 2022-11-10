<?php
/**
 * @var \yii\web\View $this
 * @var \yii\data\ActiveDataProvider $ranks
 * @var array $filters
 * @var array $filtering
 */

use backend\base\GridView;
use yii\grid\SerialColumn;
use yii\helpers\Html;

$this->title                   = Yii::t('customer', 'Player Ranks');
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('customer', 'Players'),
	'url'   => ['default/index']
];
$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->can('customer rank upsert')){
	$this->params['primary_link'] = Html::a(Yii::t('customer', 'New Rank'), ['create'], [
		'class'          => 'btn btn-new btn-primary',
		'data-bs-toggle' => "modal",
		'data-bs-target' => "#global-modal",
		'data-header'    => Yii::t('customer', 'New Customer Rank')
	]);
}
?>

	<div class="filter">
		<form class="form-inline" action="" method="get">
			<div class="input-group">
				<label class="control-label"><?= Yii::t('common', 'Keywords') ?></label>
				<?= Html::textInput('s', $filtering['s'] ?? NULL,
					['class' => 'form-control', 'placeholder' => Yii::t('common', 'Keywords')]) ?>
			</div>
			<div class="input-group">
				<label class="control-label"><?= Yii::t('common', 'Status') ?></label>
				<?= Html::dropDownList('status', $filtering['status'] ?? NULL, $filters['statuses'],
					['class' => 'form-select', 'data-toggle' => 'select']) ?>
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
	'dataProvider' => $ranks,
	'columns'      => [
		[
			'class' => SerialColumn::class
		],
		[
			'attribute' => 'name',
			'format'    => 'raw',
			'value'     => function ($data){
				$name = $data->name;
				if ($data->is_default){
					$name .= Html::tag('span', '<i class="fe fe-check" aria-hidden="true"></i>',
						['class' => 'badge badge-success rounded-circle p-1 ml-2', 'title' => Yii::t('common',
							'Verified')]);
				}

				return Html::a($name, ['create', 'id' => $data['id']], [
					'data-bs-toggle' => "modal",
					'data-bs-target' => "#global-modal",
					'data-header'    => Yii::t('customer', 'Update Customer Rank {0}',
						[$data['name']])
				]);
			},
		],
		'description:text',
		[
			'attribute' => 'type',
			'value'     => 'typeLabel'
		],
		['class' => 'common\base\grid\StatusColumn', 'action' => ['active']],
		[
			'format'  => 'raw',
			'visible' => Yii::$app->user->can('customer rank upsert'),
			'value'   => function ($data){
				$result = '<div class="btn-group btn-group-sm" role="group">';
				$result .= Html::a('<i class="fe fe-edit-2"></i>',
					['create', 'id' => $data['id']],
					[
						'class'          => 'btn btn-secondary',
						'data-bs-toggle' => "modal",
						'data-bs-target' => "#global-modal",
						'data-header'    => Yii::t('customer', 'Update Customer Rank: {0}',
							[$data['name']])
					]
				);
				$result .= Html::a('<i class="fe fe-trash"></i>',
					['delete', 'id' => $data['id']],
					['class'        => 'btn btn-danger',
					 "data-confirm" => "Are you sure you want to delete this item?",
					 'data-method'  => 'post',]
				);
				$result .= '</div>';

				return $result;
			}
		]
	]
]) ?>