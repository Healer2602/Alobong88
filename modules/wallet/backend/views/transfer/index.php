<?php

use backend\base\GridView;
use yii\helpers\Html;

/* @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $data
 * @var array $filtering
 * @var array $filters
 */


$this->title = Yii::t('wallet', 'Transfers');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'eWallets'),
	'url' => ['index']
];

$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bank-index">
	<div class="filter">
		<form class="form-inline" action="" method="get">
			<div class="input-group">
				<label class="control-label"><?= Yii::t('wallet', 'Keywords') ?></label>
				<?= Html::textInput('s', $filtering['s'] ?? NULL,
					['class' => 'form-control', 'placeholder' => Yii::t('wallet',
						'Player or Transaction ID')]) ?>
			</div>
			<div class="input-group">
				<label class="control-label"><?= Yii::t('wallet', 'Player') ?></label>
				<?= Html::dropDownList('player', $filtering['player'] ?? NULL,
					$filters['players'], [
						'class'       => 'form-select',
						'data-toggle' => 'select',
						'prompt'      => Yii::t('common', 'All')
					]) ?>
			</div>
			<div class="input-group">
				<label class="control-label"><?= Yii::t('wallet',
						'Transaction Date') ?></label>
				<?= Html::textInput('date',
					$filtering['date'] ?? NULL,
					[
						'class'          => 'form-control',
						'data-flatpickr' => '{"mode": "range"}'
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
			['class' => 'yii\grid\SerialColumn'],
			'transaction_id',
			[
				'attribute' => 'customer_id',
				'value'     => 'customer.name'
			],
			'amount:currency',
			[
				'attribute' => 'from',
				'value'     => 'fromName',
			],
			[
				'attribute' => 'to',
				'value'     => 'toName',
			],
			'id',
			'created_at:datetime',
		],
	]); ?>
</div>