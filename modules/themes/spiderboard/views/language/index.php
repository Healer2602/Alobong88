<?php

use backend\base\GridView;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/* @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $languages
 * @var array $filtering
 * @var array $filters
 */

$this->title = Yii::t('common', 'Languages');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Settings'),
	'url'   => ['/setting/index']
];
$this->params['breadcrumbs'][] = $this->title;

$this->params['primary_link'] = '';
if (Yii::$app->user->can('language upsert')){
	$this->params['primary_link'] .= Html::a(Yii::t('common', 'New Language'), ['create'],
		['class' => 'btn btn-new btn-primary']);
}

?>
<div class="language-index">

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
					$filters['states'], ['class' => 'form-select', 'data-toggle' => 'select']) ?>
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

	<?php ActiveForm::begin(); ?>
	<?= GridView::widget([
		'dataProvider' => $languages,
		'columns'      => [
			['class' => 'yii\grid\SerialColumn'],
			[
				'attribute' => 'name',
				'format'    => 'raw',
				'value'     => function ($data){
					$name = $data->name;
					if ($data->is_default){
						$name .= Html::tag('span', '<i class="fe fe-check" aria-hidden="true"></i>',
							['class' => 'badge badge-success rounded-circle p-1 ml-2', 'title' => Yii::t('common',
								'Default')]);
					}

					return Html::a($name, ['update', 'id' => $data->id]);
				}
			],
			[
				'attribute' => 'key',
				'value'     => 'systemKey',
				'format'    => 'html'
			],
			[
				'class'   => 'common\base\grid\StatusColumn',
				'action'  => ['active'],
				'header'  => Yii::t('common', 'Status'),
				'visible' => function ($data){
					return !$data->is_default;
				}
			],
			[
				'attribute' => 'created_by',
				'label'     => Yii::t('common', 'Author'),
				'value'     => 'author.name'
			],
			[
				'label'     => Yii::t('common', 'Updated At'),
				'attribute' => 'updated_at',
				'value'     => 'updated_at',
				'format'    => 'datetime'
			],
			'id',
			[
				'headerOptions' => ['class' => 'action'],
				'format'        => 'raw',
				'value'         => function ($data){
					$result = '<div class="btn-group btn-group-sm" role="group">';

					if (Yii::$app->user->can('language upsert')){
						$result .= Html::a('<i class="fe fe-edit-2"></i>',
							['update', 'id' => $data['id']],
							['class' => 'btn btn-secondary']
						);
					}

					if (Yii::$app->user->can('language delete') && !$data['is_default']){
						$result .= Html::a('<i class="fe fe-trash"></i>',
							['delete', 'id' => $data['id']],
							['class'        => 'btn btn-danger',
							 "data-confirm" => "Are you sure you want to delete this item?",
							 'data-method'  => 'post',
							]
						);
					}
					$result .= '</div>';

					return $result;
				}
			]
		],
	]); ?>
	<?php ActiveForm::end(); ?>
</div>
