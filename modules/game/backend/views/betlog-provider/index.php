<?php
/**
 * @var \yii\web\View $this
 * @var \yii\data\ActiveDataProvider $betlog_providers
 * @var array $filters
 * @var array $filtering
 */

use backend\base\GridView;
use yii\grid\SerialColumn;
use yii\helpers\Html;

$this->title = Yii::t('game', 'Betlog Provider');

$this->params['breadcrumbs'][] = [
	'url'   => ['/setting/index'],
	'label' => Yii::t('common', 'Settings')
];
$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->can('betlog_provider upsert')){
	$this->params['primary_link'] = Html::a(Yii::t('game', 'New Betlog Provider'), ['create'],
		[
			'class'          => 'btn btn-new btn-primary',
			'data-bs-toggle' => "modal",
			'data-bs-target' => "#global-modal",
			'data-header'    => Yii::t('game', 'New Betlog Provider')
		]);
}
?>

	<div class="filter">
		<form class="form-inline" action="" method="get">
			<div class="input-group">
				<label class="control-label"><?= Yii::t('common', 'Keyword') ?></label>
				<?= Html::textInput('s', $filtering['s'] ?? NULL,
					['class' => 'form-control', 'placeholder' => Yii::t('common', 'Keyword')]) ?>
			</div>
			<div class="input-group">
				<label class="control-label"><?= Yii::t('common', 'Status') ?></label>
				<?= Html::dropDownList('state', $filtering['state'] ?? NULL,
					$filters['states'], ['class' => 'form-select', 'data-toggle' => 'select']) ?>
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
	'dataProvider' => $betlog_providers,
	'columns'      => [
		['class' => SerialColumn::class],
		'product_wallet:text',
		'code:text',
		[
			'attribute' => 'vendor_id',
			'value'     => 'vendor.name'
		],
		[
			'class'  => 'common\base\grid\StatusColumn',
			'action' => ['active']
		],
		'created_at:datetime',
		[
			'format'  => 'raw',
			'visible' => Yii::$app->user->can('betlog_provider upsert'),
			'value'   => function ($data){
				$result = '<div class="btn-group btn-group-sm" role="group">';
				$result .= Html::a('<i class="fe fe-edit-2"></i>',
					['update', 'id' => $data['id']],
					[
						'class'          => 'btn btn-secondary',
						'data-bs-toggle' => "modal",
						'data-bs-target' => "#global-modal",
						'data-header'    => Yii::t('game', 'Update {0}',
							[$data['name']])
					]
				);

				if (Yii::$app->user->can('betlog_provider delete')){
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
]) ?>