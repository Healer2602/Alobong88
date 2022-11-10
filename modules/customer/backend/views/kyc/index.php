<?php
/**
 * @var \yii\web\View $this
 * @var \yii\data\ActiveDataProvider $kycs
 * @var array $filters
 * @var array $filtering
 */

use backend\base\GridView;
use common\base\Status;
use yii\grid\SerialColumn;
use yii\helpers\Html;

$this->title                   = Yii::t('customer', 'eKYC');
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('customer', 'Players'),
	'url'   => ['default/index']
];
$this->params['breadcrumbs'][] = $this->title;
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
	'dataProvider' => $kycs,
	'columns'      => [
		[
			'class' => SerialColumn::class
		],
		[
			'attribute' => 'name',
			'format'    => 'raw',
			'value'     => function ($data){
				if ($data->status == Status::STATUS_ACTIVE){
					return $data->customer->name;
				}

				return Html::a($data->customer->name, ['detail', 'id' => $data->id]);
			},
		],
		[
			'attribute' => 'email',
			'format'    => 'email',
			'value'     => 'customer.email'
		],
		[
			'header'    => Yii::t('customer', 'Phone Number'),
			'attribute' => 'phone_number',
			'value'     => 'customer.phone_number'
		],
		'created_at:datetime',
		[
			'format'  => 'raw',
			'visible' => Yii::$app->user->can('customer kyc upsert'),
			'value'   => function ($data){
				if ($data->status != Status::STATUS_ACTIVE){
					$result = '<div class="btn-group btn-group-sm" role="group">';
					$result .= Html::a('<i class="fe fe-eye"></i>',
						['detail', 'id' => $data['id']],
						['class' => 'btn btn-primary']
					);
					$result .= '</div>';

					return $result;
				}
			}
		]
	]
]) ?>