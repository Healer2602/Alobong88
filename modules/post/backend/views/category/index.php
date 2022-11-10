<?php

use backend\base\GridView;
use yii\helpers\Html;

/* @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $categories
 * @var array $filtering
 * @var array $filters
 */

$this->title = Yii::t('post', 'Press Categories');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('post', 'Press'),
	'url'   => ['/post/default/index']
];
$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->can('post category upsert')){
	$this->params['primary_link'] = Html::a(Yii::t('post', 'New Category'), ['create'],
		['class' => 'btn btn-new btn-primary']);
}

?>
<div class="post-category-index">

	<?= $this->render('_filter', ['filtering' => $filtering, 'filters' => $filters]) ?>

	<?= GridView::widget([
		'dataProvider' => $categories,
		'columns'      => [
			['class' => 'yii\grid\SerialColumn'],
			[
				'attribute' => 'name',
				'format'    => 'html',
				'value'     => function ($data){
					return Html::a($data->name, ['update', 'id' => $data->id]);
				}
			],
			'slug',
			[
				'class'  => 'common\base\grid\StatusColumn',
				'action' => ['active'],
				'header' => Yii::t('post', 'Status')
			],
			[
				'attribute' => 'language',
				'value'     => 'languageLabel'
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
						 'data-method'  => 'post',]
					);

					return $result . '</div>';
				}
			]
		],
	]); ?>
</div>
