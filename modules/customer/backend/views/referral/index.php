<?php

use backend\base\GridView;
use yii\helpers\Html;

/* @var yii\web\View $this */
/* @var yii\data\ActiveDataProvider $referrals */
/* @var array $filtering */
/* @var array $filters */

$this->title = Yii::t('customer', 'Referral');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('customer', 'Players'),
	'url'   => ['default/index']
];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="post-index">

	<div class="filter">
		<form class="form-inline" action="" method="get">
			<div class="input-group">
				<label class="control-label"><?= Yii::t('common', 'Keywords') ?></label>
				<?= Html::textInput('s', $filtering['s'] ?? NULL,
					['class' => 'form-control', 'placeholder' => Yii::t('common', 'Keywords')]) ?>
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
		'dataProvider' => $referrals,
		'columns'      => [
			['class' => 'yii\grid\SerialColumn'],
			[
				'attribute' => 'customer_id',
				'format'    => 'raw',
				'value'     => function ($data){
					return Html::a($data->customerDetail, ['view', 'id' => $data->id]);
				}
			],
			'code',
			'total',
			'updated_at:datetime',
			'id',
			[
				'headerOptions' => ['class' => 'action'],
				'format'        => 'raw',
				'value'         => function ($data){
					return Html::a('<i class="fe fe-eye"></i>',
						['view', 'id' => $data['id']],
						['class' => 'btn btn-secondary btn-sm']
					);
				}
			]
		],
	]); ?>
</div>
