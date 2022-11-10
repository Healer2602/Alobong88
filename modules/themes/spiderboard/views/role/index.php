<?php
/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $groups
 * @var array $filters
 * @var array $filtering
 */

use backend\base\GridView;
use yii\helpers\Html;

$this->title = Yii::t('common', 'Roles');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Staffs'),
	'url'   => ['users/index']
];
$this->params['breadcrumbs'][] = $this->title;

$this->params['primary_link'] = '';
if (Yii::$app->user->can('role upsert')){
	$this->params['primary_link'] .= Html::a(Yii::t('common', 'New Role'), ['create'],
		['class' => 'btn btn-new btn-primary']);
}
if (Yii::$app->user->can('role access_control')){
	$this->params['primary_link'] .= Html::a('<i class="fe fe-shield"></i> ' . Yii::t('common',
			'Permissions'), ['access-control'],
		['class' => 'btn btn-primary btn-with-icon pull-right']);
}

?>
<div class="role-management">

	<div class="filter">
		<form class="form-inline" action="" method="get">
			<div class="input-group">
				<label class="control-label"><?= Yii::t('common', 'Name') ?></label>
				<?= Html::textInput('s', $filtering['s'] ?? NULL,
					['class' => 'form-control', 'placeholder' => Yii::t('common', 'Name')]) ?>
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

	<?= GridView::widget([
			'dataProvider' => $groups,
			'tableOptions' => ['class' => 'table table-striped'],
			'columns'      => [
				[
					'class'         => 'yii\grid\SerialColumn',
					'headerOptions' => [
						'style' => "width:20px;"
					],
				],
				[
					'attribute' => 'name',
					'format'    => 'html',
					'value'     => function ($data){
						return Html::a($data->name, ['update', 'id' => $data['id']]);
					}
				],
				[
					'class'   => 'common\base\grid\StatusColumn',
					'action'  => ['active'],
					'visible' => function ($data){
						return !$data->is_primary;
					}
				],
				[
					'headerOptions' => ['class' => 'action'],
					'format'        => 'raw',
					'value'         => function ($data){
						$result = '<div class="btn-group btn-group-sm" role="group">';
						$result .= Html::a('<i class="fe fe-edit-2"></i>',
							['update', 'id' => $data['id']],
							['class' => 'btn btn-secondary']
						);
						if (!$data->is_primary && !$data->isRelated){
							$result .= Html::a('<i class="fe fe-trash"></i>',
								['delete', 'id' => $data['id']],
								['class'        => 'btn btn-danger',
								 "data-confirm" => "Are you sure you want to delete this item?",
								 'data-method'  => 'post',]
							);
						}
						$result .= '</div>';

						return $result;
					}
				]
			],
		]
	); ?>
</div>