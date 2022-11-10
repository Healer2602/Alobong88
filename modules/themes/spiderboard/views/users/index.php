<?php
/**
 * @var \yii\web\View $this
 * @var \yii\data\ActiveDataProvider $users
 * @var array $filtering
 * @var array $filters
 */

use backend\base\GridView;
use common\base\StatusAttributeBehavior;
use yii\grid\SerialColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$this->title = Yii::t('common', 'Staffs');

$this->params['breadcrumbs'][] = $this->title;

$this->params['primary_link'] = '';
if (Yii::$app->user->can('user upsert')){
	$this->params['primary_link'] .= Html::a(Yii::t('common', 'New Staff'), ['create'],
		['class' => 'btn btn-new btn-primary']);
}

if (Yii::$app->user->can('role upsert')){
	$this->params['primary_link'] .= Html::a('<i class="fe fe-users"></i> ' . Yii::t('common',
			'Roles'), ['/role/index'],
		['class' => 'btn btn-primary btn-with-icon']);
}

if (Yii::$app->user->can('role access_control')){
	$this->params['primary_link'] .= Html::a('<i class="fe fe-shield"></i> ' . Yii::t('common',
			'Permissions'), ['/role/access-control'],
		['class' => 'btn btn-primary btn-with-icon']);
}

?>

<div class="filter">
	<form class="form-inline" action="" method="get">
		<div class="input-group">
			<label class="control-label"><?= Yii::t('common', 'Keywords') ?></label>
			<?= Html::textInput('s', $filtering['s'] ?? NULL,
				['class' => 'form-control', 'placeholder' => Yii::t('common', 'Keywords')]) ?>
		</div>
		<div class="input-group">
			<label class="control-label"><?= Yii::t('common', 'Role') ?></label>
			<?= Html::dropDownList('role', $filtering['role'] ?? NULL,
				$filters['roles'],
				['class' => 'form-select', 'data-toggle' => 'select', 'prompt' => Yii::t('common',
					'All')]) ?>
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
					<i class="fe fe-x" aria-hidden="true"></i>
				</button>
			</div>
		</div>
	</form>
</div>

<?= GridView::widget([
	'dataProvider' => $users,
	'columns'      => [
		[
			'class' => SerialColumn::class
		],
		[
			'attribute' => 'username',
			'format'    => 'html',
			'value'     => function ($data){
				return Html::a($data->username, ['update', 'id' => $data['id']]);
			}
		],
		'name:text',
		'email:email',
		[
			'attribute' => 'user_group_id',
			'format'    => 'raw',
			'value'     => function ($data){
				if ($groups = $data->groups){
					$groups = ArrayHelper::getColumn($groups, 'name');

					return implode('<br>', $groups);
				}

				return NULL;
			}
		],
		[
			'class'   => 'common\base\grid\StatusColumn',
			'action'  => ['active'],
			'visible' => function () use ($users, $filtering){
				return !((!isset($filtering['state']) || $filtering['state'] == StatusAttributeBehavior::STATUS_ACTIVE) && $users->getCount() == 1);
			}
		],
		[
			'format' => 'raw',
			'value'  => function ($data) use ($users, $filtering){
				$result = '<div class="btn-group btn-group-sm" role="group">';
				if (Yii::$app->user->can('user upsert')){
					$result .= Html::a('<i class="fe fe-edit-2"></i>',
						['update', 'id' => $data['id']],
						['class' => 'btn btn-secondary']
					);
				}

				if (Yii::$app->user->can('user delete') && !((!isset($filtering['state']) || $filtering['state'] == StatusAttributeBehavior::STATUS_ACTIVE) && $users->getCount() == 1)){
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
	]
]) ?>
