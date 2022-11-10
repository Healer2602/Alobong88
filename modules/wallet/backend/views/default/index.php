<?php
/**
 * @var \yii\web\View $this
 * @var \yii\data\ActiveDataProvider $data
 * @var array $filtering
 * @var array $statuses
 */

use backend\base\GridView;
use modules\wallet\models\Wallet;
use yii\grid\SerialColumn;
use yii\helpers\Html;

$this->title = Yii::t('wallet', 'eWallets');

$this->params['breadcrumbs'][] = $this->title;
?>

<div class="filter">
	<form class="form-inline" action="" method="get">
		<div class="input-group">
			<label class="control-label"><?= Yii::t('common', 'Keywords') ?></label>
			<?= Html::textInput('s', $filtering['s'] ?? NULL,
				['class' => 'form-control', 'placeholder' => Yii::t('wallet',
					'Search by customer')]) ?>
		</div>
		<div class="input-group">
			<label class="control-label"><?= Yii::t('common', 'Status') ?></label>
			<?= Html::dropDownList('state', $filtering['state'] ?? NULL,
				$statuses, ['class' => 'form-select', 'data-toggle' => 'select']) ?>
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

<?= GridView::widget([
	'dataProvider' => $data,
	'columns'      => [
		[
			'class' => SerialColumn::class
		],
		[
			'attribute' => 'customer.name',
			'format'    => 'html',
			'value'     => function (Wallet $data){
				$name = $data->customer->name ?? NULL;

				if (Yii::$app->user->can('wallet detail')){
					return Html::a($name, ['view', 'id' => $data->id]);
				}

				return $name;
			}
		],
		'customer.email',
		[
			'attribute' => 'balance_total',
			'format'    => 'currency'
		],
		[
			'attribute' => 'balance',
			'label'     => Yii::t('wallet', 'Main Wallet'),
			'format'    => 'currency'
		],
		[
			'attribute' => 'balance_subwallet',
			'format'    => 'currency'
		],
		[
			'attribute' => 'turnover',
			'format'    => 'currency'
		],
		[
			'attribute'      => 'last_update',
			'format'         => 'relativeTime',
			'contentOptions' => ['class' => 'text-nowrap']
		],
		[
			'attribute' => 'status',
			'format'    => 'html',
			'value'     => 'statusHtml'
		],
		[
			'format'  => 'html',
			'visible' => Yii::$app->user->can('wallet detail'),
			'value'   => function ($data){
				$result = '<div class="btn-group btn-group-sm" role="group">';

				$result .= Html::a('<i class="fe fe-eye"></i>',
					['view', 'id' => $data['id']],
					['class' => 'btn btn-secondary']
				);

				return $result . '</div>';
			}
		]
	]
]) ?>
