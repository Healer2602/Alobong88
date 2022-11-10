<?php
/**
 * @var \yii\web\View $this
 * @var \yii\data\ActiveDataProvider $data
 * @var array $filtering
 * @var array $filters
 */

use backend\base\GridView;
use yii\grid\SerialColumn;
use yii\helpers\Html;

$this->title = Yii::t('wallet', 'Transactions');

$this->params['breadcrumbs'][] = [
	'url'   => ['default/index'],
	'label' => Yii::t('wallet', 'eWallets')
];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="filter">
	<?= $this->render('_filter', ['filtering' => $filtering, 'filters' => $filters]) ?>
</div>
<?= GridView::widget([
	'dataProvider' => $data,
	'columns'      => [
		[
			'class' => SerialColumn::class
		],
		[
			'attribute' => 'customer.name',
			'format'    => 'html',
			'value' => 'customerName'
		],
		[
			'attribute' => 'transaction_id',
			'format'    => 'raw',
			'value' => function ($data) use ($filtering){
				if (Yii::$app->user->can('wallet transaction detail')){
					return Html::a($data->transaction_id,
						['transaction/view', 'id' => $data['id'], 'wallet' => $filtering['wallet'] ?? NULL],
						[
							'data-bs-toggle' => "modal",
							'data-bs-target' => "#global-modal",
							'data-header'    => Yii::t('wallet', 'Activities - {0}',
								[$data['typeLabel']])
						]);
				}

				return $data->transaction_id;
			}
		],
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
			'value' => function ($data) use ($filtering){
				$result = '<div class="btn-group btn-group-sm" role="group">';

				$result .= Html::a('<i class="fe fe-eye"></i>',
					['transaction/view', 'id' => $data['id'], 'wallet' => $filtering['wallet'] ?? NULL],
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
