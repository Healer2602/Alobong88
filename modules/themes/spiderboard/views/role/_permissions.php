<?php

use backend\base\CheckboxColumn;
use backend\base\GridView;
use yii\grid\SerialColumn;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $permissions yii\data\ArrayDataProvider */
/* @var $model \backend\models\UserGroup */
/* @var $group_permissions array */
?>

<?= GridView::widget([
		'dataProvider' => $permissions,
		'tableOptions' => ['class' => 'table table-striped'],
		'layout'       => "{items}",
		'columns'      => [
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
			[
				'class'           => CheckboxColumn::class,
				'name'            => "Permissions",
				'checkboxOptions' => function ($data) use ($model, $group_permissions){
					$options['value'] = $data['id'];
					if ($model->is_primary || ArrayHelper::isIn($data['id'],
							$group_permissions)){
						$options['checked'] = TRUE;
					}

					return $options;
				},
			],
		]
	]
); ?>

<?php
if (count($group_permissions) == $permissions->getCount() || $model->is_primary){
	$js = <<< JS
    $('.select-on-check-all').prop('checked', true);
JS;
	if ($model->is_primary){
		$js .= <<< JS
    $('.select-on-check-all').attr('disabled', true);
JS;
	}

	$this->registerJs($js);
}
