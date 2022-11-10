<?php
/**
 * @var \yii\web\View $this
 * @var \modules\wallet\models\Wallet $model
 * @var \yii\data\ActiveDataProvider $activities
 * @var \yii\data\ActiveDataProvider $sub_wallets
 * @var array $filtering
 * @var array $filters
 */

use backend\base\GridView;
use yii\bootstrap5\Html;
use yii\grid\SerialColumn;
use yii\helpers\ArrayHelper;

$this->title = Yii::t('wallet', 'Wallet details for {0}', [$model->customer->name]);

$this->params['breadcrumbs'][] = [
	'url'   => ['index'],
	'label' => Yii::t('wallet', 'eWallets')
];
$this->params['breadcrumbs'][] = Yii::t('wallet', 'Details');

$total_subwallets = array_sum(ArrayHelper::getColumn($model->subWallets, 'balance'));
?>

<div class="row">
	<div class="col-12 col-lg-3 col-xl">
		<div class="card">
			<div class="card-body">
				<div class="row align-items-center">
					<div class="col">
						<h6 class="text-uppercase text-muted mb-2"><?= Yii::t('wallet',
								'Balance') ?></h6>
						<span class="h2 mb-0"><?= Yii::$app->formatter->asCurrency($total_subwallets + $model->balance,
								FALSE) ?></span>
					</div>
					<div class="col-auto">
						<span class="h2 fe fe-dollar-sign text-muted mb-0"></span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-12 col-lg-3 col-xl">
		<div class="card">
			<div class="card-body">
				<div class="row align-items-center">
					<div class="col">
						<h6 class="text-uppercase text-muted mb-2"><?= Yii::t('wallet',
								'Main Wallet') ?></h6>
						<span class="h2 mb-0"><?= Yii::$app->formatter->asCurrency($model->balance,
								FALSE) ?></span>
					</div>
					<div class="col-auto">
						<span class="h2 fe fe-dollar-sign text-muted mb-0"></span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-12 col-lg-3 col-xl">
		<div class="card">
			<div class="card-body">
				<div class="row align-items-center">
					<div class="col">
						<h6 class="text-uppercase text-muted mb-2"><?= Yii::t('wallet',
								'Sub-Wallets') ?></h6>
						<span class="h2 mb-0"><?= Yii::$app->formatter->asCurrency($total_subwallets,
								FALSE) ?></span>
					</div>
					<div class="col-auto">
						<span class="h2 fe fe-dollar-sign text-muted mb-0"></span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-12 col-lg-3 col-xl">
		<div class="card">
			<div class="card-body">
				<div class="row align-items-center">
					<div class="col">
						<h6 class="text-uppercase text-muted mb-2"><?= Yii::t('wallet',
								'Total deposit') ?></h6>
						<span class="h2 mb-0"><?= Yii::$app->formatter->asCurrency($model->report->totalTopups) ?></span>
					</div>
					<div class="col-auto">
						<span class="h2 fe fe-plus-circle text-muted mb-0"></span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-12 col-lg-3 col-xl">
		<div class="card">
			<div class="card-body">
				<div class="row align-items-center">
					<div class="col">
						<h6 class="text-uppercase text-muted mb-2"><?= Yii::t('wallet',
								'Total withdraw') ?></h6>
						<span class="h2 mb-0"><?= Yii::$app->formatter->asCurrency(abs($model->report->totalWithdraws)) ?></span>
					</div>
					<div class="col-auto">
						<span class="h2 fe fe-minus-circle text-muted mb-0"></span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="card">
	<div class="card-header">
		<h4 class="card-header-title"><?= Yii::t('wallet', 'Sub-Wallets') ?></h4>
	</div>
	<div class="card-header card-filter">
		<?= $this->render('_wallet_sub_filter', ['filtering' => $filtering]) ?>
	</div>
	<?= GridView::widget([
		'options'      => ['class' => 'table-responsive'],
		'layout'       => "{items}<div class='p-3 d-flex justify-content-end'>{pager}</div>{summary}",
		'dataProvider' => $sub_wallets,
		'tableOptions' => ['class' => 'table table-nowrap table-hover card-table'],
		'columns'      => [
			[
				'attribute' => 'wallet_code',
				'value'     => 'product.name'
			],
			'product_code',
			'balance:currency',
			'last_update:datetime',
			[
				'attribute' => 'status',
				'value'     => 'statusLabel'
			],
			[
				'format' => 'html',
				'value'  => function ($data){
					return Html::a(Yii::t('wallet', 'View transactions'),
						['transaction/index', 'wallet' => $data->product_code, 'id' => $data->id],
						['class' => 'btn btn-link btn-sm']);
				}
			]
		],
	]) ?>
</div>

<div class="card">
	<div class="card-header">
		<h4 class="card-header-title"><?= Yii::t('wallet', 'Activities') ?></h4>
	</div>
	<div class="card-header card-filter">
		<?= $this->render('_filter', ['filtering' => $filtering, 'filters' => $filters]) ?>
	</div>
	<?= GridView::widget([
		'options'      => ['class' => 'table-responsive'],
		'layout'       => "{items}<div class='p-3 d-flex justify-content-end'>{pager}</div>{summary}",
		'dataProvider' => $activities,
		'tableOptions' => ['class' => 'table table-nowrap table-hover card-table'],
		'columns'      => [
			[
				'class' => SerialColumn::class
			],
			'transaction_id',
			[
				'attribute' => 'type',
				'value'     => 'typeHtml',
				'format'    => 'html'
			],
			'amount:currency',
			'balance:currency',
			'reference_id',
			[
				'attribute' => 'status',
				'value'     => 'statusHtml',
				'format'    => 'html'
			],
			'created_at:datetime',
			[
				'format'  => 'raw',
				'visible' => Yii::$app->user->can('wallet transaction detail'),
				'value'   => function ($data){
					$result = '<div class="btn-group btn-group-sm" role="group">';

					$result .= Html::a('<i class="fe fe-eye"></i>',
						['transaction/view', 'id' => $data['id']],
						[
							'class'          => 'btn btn-secondary',
							'data-bs-toggle' => "modal",
							'data-bs-target' => "#global-modal",
							'data-header'    => Yii::t('wallet', 'Activities - {0}',
								[$data['typeLabel']])
						]
					);

					return $result . '</div>';
				}
			]
		]
	]) ?>
</div>