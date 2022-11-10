<?php

use backend\base\GridView;
use modules\spider\lib\sortable\SortableColumn;
use yii\bootstrap5\Dropdown;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var yii\web\View $this */
/* @var \modules\block\backend\models\BlockSearch $searchModel */
/* @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('block', 'Blocks');

$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->can('block upsert')){
	$primary_link = Html::beginTag('div', ['class' => 'dropdown btn-group']);
	$primary_link .= Html::a(Yii::t('block', 'New Block'), ['create'],
		['class' => 'btn btn-new btn-primary']);
	$primary_link .= Html::button('',
		['class' => 'btn btn-primary dropdown-toggle dropdown-toggle-split', 'data-bs-toggle' => "dropdown"]);

	$types = [];
	foreach ($searchModel->types as $type => $label){
		$types[] = [
			'label' => $label,
			'url'   => ['create', 'type' => $type]
		];
	}

	$primary_link .= Dropdown::widget([
		'options' => [
			'class' => 'dropdown-menu-right',
		],
		'items'   => $types
	]);

	$primary_link .= Html::endTag('div');

	$this->params['primary_link'] = $primary_link;
}

?>

<div class="block-index">

	<?= $this->render('_filter', ['searchModel' => $searchModel]) ?>

	<?php ActiveForm::begin(['id' => 'sortableList']) ?>

	<?= GridView::widget([
		'dataProvider' => $dataProvider,
		'columns'      => [
			['class' => SortableColumn::class],
			['class' => 'yii\grid\SerialColumn'],
			[
				'attribute' => 'name',
				'format'    => 'raw',
				'value'     => function ($data){
					return Html::a($data->name, ['update', 'id' => $data->id]);
				}
			],
			'title',
			[
				'attribute' => 'type',
				'value'     => 'typeLabel',
			],
			[
				'attribute' => 'position',
				'value'     => 'positionLabel',
			],
			[
				'attribute' => 'language',
				'value'     => 'languageLabel'
			],
			[
				'attribute' => 'status',
				'class'     => 'common\base\grid\StatusColumn',
				'action'    => ['active'],
			],

			'id',
			[
				'headerOptions' => ['class' => 'action'],
				'format'        => 'raw',
				'value'         => function ($data){
					$result = '<div class="btn-group btn-group-sm" role="group">';
					$result .= Html::a('<i class="fe fe-edit-2"></i>',
						['update', 'id' => $data['id']],
						['class' => 'btn btn-secondary']
					);
					$result .= Html::a('<i class="fe fe-trash"></i>',
						['delete', 'id' => $data['id']],
						['class'        => 'btn btn-danger',
						 "data-confirm" => "Are you sure you want to delete this item?",
						 'data-method'  => 'post']
					);
					$result .= '</div>';

					return $result;
				}
			]
		],
	]); ?>

	<?php ActiveForm::end(); ?>
</div>
