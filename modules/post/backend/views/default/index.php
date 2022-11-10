<?php
/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $posts
 * @var array $filtering
 * @var array $filters
 */

use backend\base\GridView;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('post', 'Press');

$this->params['breadcrumbs'][] = $this->title;

$this->params['primary_link'] = '';
if (Yii::$app->user->can('blog upsert')){
	$this->params['primary_link'] .= Html::a(Yii::t('post', 'New Press'), ['create'],
		['class' => 'btn btn-new btn-primary']);
}

if (Yii::$app->user->can('post category')){
	$this->params['primary_link'] .= Html::a(Html::tag('i', '',
			['class' => 'fe fe-align-right ']) . Yii::t('post',
			'Categories'), ['/post/category/index'],
		['class' => 'btn btn-primary btn-with-icon']);
}

?>
<div class="post-index">

	<?= $this->render('_filter', ['filtering' => $filtering, 'filters' => $filters]) ?>

	<?php ActiveForm::begin(['id' => 'sortableList']); ?>
	<?= GridView::widget([
		'dataProvider' => $posts,
		'batch_action' => Yii::$app->user->can('blog delete'),
		'columns'      => [
			[
				'class'           => 'backend\base\CheckboxColumn',
				'visible'         => Yii::$app->user->can('blog delete'),
				'checkboxOptions' => function ($model){
					return ['value' => $model->id];
				},
			],
			['class' => 'yii\grid\SerialColumn'],
			[
				'attribute' => 'name',
				'format'    => 'raw',
				'value'     => function ($data){
					return Html::a($data->name, ['update', 'id' => $data->id]);
				}
			],
			'slug',
			[
				'attribute' => 'category_id',
				'value'     => 'category.name'
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
