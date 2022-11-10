<?php

use backend\base\GridView;
use yii\helpers\Html;

/* @var yii\web\View $this */
/* @var yii\data\ActiveDataProvider $agents */
/* @var array $filtering */
/* @var array $filters */

$this->title = Yii::t('agent', 'Agents');

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
				<label class="control-label"><?= Yii::t('common', 'Status') ?></label>
				<?= Html::dropDownList('state', $filtering['state'] ?? NULL,
					$filters['states'],
					['data-toggle' => 'select', 'class' => 'form-select', 'prompt' => 'All']) ?>
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
		'dataProvider' => $agents,
		'columns'      => [
			['class' => 'yii\grid\SerialColumn'],
			[
				'attribute' => 'name',
				'format'    => 'html',
				'value'     => function ($data){
					return Html::a($data->name, ['details', 'id' => $data['id']]);
				}
			],
			'email:email',
			'code',
			'total',
			[
				'attribute' => 'status',
				'format'    => 'html',
				'value'     => 'statusHtml'
			],
			'updated_at:datetime',
			'id',
			[
				'headerOptions' => ['class' => 'action'],
				'format'        => 'raw',
				'value'         => function ($data){
					$result = '<div class="btn-group btn-group-sm" role="group">';
					$result .= Html::a('<i class="fe fe-edit-2"></i>',
						['details', 'id' => $data['id']],
						['class' => 'btn btn-secondary']
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
		],
	]); ?>
</div>
