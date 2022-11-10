<?php

use backend\base\GridView;
use modules\spider\lib\sortable\SortableColumn;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $data
 * @var array $filtering
 * @var array $filters
 */

$this->title                   = Yii::t('wallet', 'Withdraw Gateways');
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Settings'),
	'url'   => ['/setting/index']
];
$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->can('wallet_withdraw_gateway upsert')){
	$this->params['primary_link'] = Html::a(Yii::t('wallet', 'New Gateway'), ['create'],
		['class' => 'btn btn-new btn-primary']);
}
?>
<div class="post-index">

	<div class="filter">
		<form class="form-inline" action="" method="get">
			<div class="input-group">
				<label class="control-label"><?= Yii::t('common', 'Status') ?></label>
				<?= Html::dropDownList('state', $filtering['state'] ?? NULL,
					$filters['status'],
					['class' => 'form-select', 'data-toggle' => 'select']) ?>
			</div>
			<div class="input-group">
				<div class="input-group-btn">
					<button class="btn btn-outline-primary" type="submit">
						<i class="fe fe-search" aria-hidden="true"></i>
					</button>
					<button class="btn btn-outline-secondary clear" type="button">
						<i class="fe fe-x" aria-hidden="true"></i>
					</button>
				</div>
			</div>
		</form>
	</div>
	<?php ActiveForm::begin(['id' => 'sortableList']); ?>
	<?= GridView::widget([
		'dataProvider' => $data,
		'columns'      => [
			['class' => SortableColumn::class],
			['class' => 'yii\grid\SerialColumn'],
			[
				'attribute' => 'key',
				'format'    => 'html',
				'value'     => function ($data){
					return Html::a($data->gatewayName, ['update', 'id' => $data->id]);
				}
			],
			'title',
			[
				'attribute' => 'option',
				'value'     => 'optionName'
			],
			[
				'attribute' => 'currency',
				'format'    => 'html',
				'value'     => function ($data){
					return Html::ul($data->currency,
						['class' => 'list-inline h3 mb-0', 'itemOptions' => ['class' => 'list-inline-item badge badge-secondary']]);
				}
			],
			[
				'attribute' => 'fee',
				'label'     => Yii::t('wallet', 'Withdraw fee'),
				'value'     => function ($data){
					return $data->fee . '%';
				}
			],
			[
				'attribute' => 'ranks',
				'format'    => 'html',
				'value'     => function ($data){
					if (is_string($data->rankNames)){
						return $data->rankNames;
					}

					return Html::ul($data->rankNames,
						['class' => 'list-inline h3 mb-0', 'itemOptions' => ['class' => 'list-inline-item badge badge-soft-primary']]);
				}
			],
			[
				'class'  => 'common\base\grid\StatusColumn',
				'action' => ['active'],
				'header' => Yii::t('common', 'Status')
			],
			[
				'headerOptions' => ['class' => 'action'],
				'format'        => 'raw',
				'value'         => function ($data){
					$result = '<div class="btn-group btn-group-sm" role="group">';
					if (Yii::$app->user->can('wallet_withdraw_gateway upsert')){
						$result .= Html::a('<i class="fe fe-edit-2"></i>',
							['update', 'id' => $data['id']],
							['class' => 'btn btn-secondary']
						);
					}
					if (Yii::$app->user->can('wallet_withdraw_gateway delete')){
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
		],
	]); ?>
	<?php ActiveForm::end(); ?>
</div>