<?php
/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $posts
 * @var array $filtering
 * @var array $filters
 */

use backend\base\GridView;
use modules\spider\lib\sortable\SortableColumn;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('post', 'Banners');

$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->can('banner upsert')){
	$this->params['primary_link'] = Html::a(Yii::t('post', 'New Banner'), ['create'],
		['class' => 'btn btn-new btn-primary']);
}
?>
<div class="post-index">
	<?= $this->render('_filter', ['filtering' => $filtering, 'filters' => $filters]) ?>

	<?php ActiveForm::begin(['id' => 'sortableList']); ?>
	<?= GridView::widget([
		'dataProvider' => $posts,
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
			[
				'attribute' => 'thumbnail',
				'format'    => 'html',
				'value'     => function ($data){
					if (empty($data->thumbnail)){
						return NULL;
					}

					return Html::img($data->thumbnail, ['style' => 'height: 70px']);
				}
			],
			[
				'attribute' => 'position',
				'value'     => 'positionName'
			],
			[
				'attribute' => 'language',
				'value'     => 'languageLabel'
			],
			[
				'class'  => 'common\base\grid\StatusColumn',
				'action' => ['active'],
				'header' => Yii::t('post', 'Status')
			],
			[
				'attribute' => 'created_by',
				'value'     => 'author.name'
			],
			'updated_at:datetime',
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
					$result .= '</div>';

					return $result;
				}
			]
		],
	]); ?><?php ActiveForm::end(); ?>
</div>
