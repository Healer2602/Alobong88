<?php

use backend\base\GridView;
use yii\grid\SerialColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $permissions yii\data\ArrayDataProvider */
/* @var $user_groups array */

?>

<?php
$columns = [
	[
		'class'          => SerialColumn::class,
		'contentOptions' => function ($data){
			if (count(explode(" ", $data['name'])) == 1){
				return ['style' => 'font-weight: bold; font-size:1.3em'];
			}

			return [];
		},
		'headerOptions'  => [
			'style' => "width:20px;"
		],
	],
	[
		'attribute'      => 'description',
		'label'          => Yii::t('common', 'Name'),
		'contentOptions' => function ($data){
			$level = count(explode(" ", $data['name']));

			if ($level == 1){
				return ['style' => 'font-weight: bold; font-size:1.2em'];
			}

			if ($level > 2){
				return ['style' => 'font-size:0.9em;'];
			}

			return ['class' => 'level-' . $level];
		},
		'format'         => 'raw',
		'value'          => function ($data){
			$level = count(explode(" ", $data['name']));
			$value = '';
			if ($level > 2){
				$value .= '|' . str_repeat('&mdash;', ($level - 2));
			}

			return $value . ' ' . $data['description'];
		}
	],
];

$group_permission_list = ArrayHelper::getColumn($permissions->allModels, 'id');

foreach ($user_groups as $id => $user_group){
	$header            = $user_group['name'];
	$group_permissions = ArrayHelper::getColumn($user_group['permissions'],
		'user_permission_id');

	$same_permissions = array_intersect($group_permission_list, $group_permissions);
	$check_all        = FALSE;
	if (count($same_permissions) == $permissions->getCount()){
		$check_all = TRUE;
	}

	if (empty($user_group['is_primary'])){
		$header = Html::tag('div',
			Html::checkbox('check-all', $check_all,
				['class' => 'check-all form-check-input', 'id' => "{$id}"]) .
			Html::label($user_group['name'], "{$id}", ['class' => 'form-check-label']),
			['class' => 'form-check']);
	}

	$columns[] = [
		'header'         => $header,
		'format'         => 'raw',
		'headerOptions'  => ['class' => 'text-center'],
		'contentOptions' => ['class' => 'text-center'],
		'value'          => function ($data) use ($user_group, $group_permissions){
			if (!empty($user_group['is_primary'])){
				return Html::tag('div',
					Html::checkbox('', TRUE,
						['class' => 'form-check-input', 'disabled' => TRUE]) .
					Html::label('', '', ['class' => 'form-check-label']),
					['class' => 'form-check']);
			}

			return Html::tag('div',
				Html::checkbox("Permission[{$user_group['id']}][]",
					ArrayHelper::isIn($data['id'], $group_permissions),
					['value' => $data['id'],
					 'class' => 'form-check-input',
					 'id'    => "Permission_{$user_group['id']}_{$data['id']}"]) .
				Html::label('', "Permission_{$user_group['id']}_{$data['id']}",
					['class' => 'form-check-label']),
				['class' => 'form-check']);
		}
	];
}
?>

<?= GridView::widget([
	'dataProvider' => $permissions,
	'tableOptions' => ['class' => 'table table-striped'],
	'layout'       => "{items}",
	'columns'      => $columns
]); ?>
