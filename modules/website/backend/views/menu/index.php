<?php

use backend\base\GridView;
use common\base\grid\StatusColumn;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $menus
 */

$this->title = Yii::t('website', 'Menu Management');

$this->params['breadcrumbs'][] = $this->title;

$this->params['primary_link'] = Html::a(Yii::t('website', 'New Menu'), ['create'],
	[
		'class'          => 'btn btn-new btn-primary', 'data-bs-toggle' => "modal",
		'data-bs-target' => "#global-modal",
		'data-header'    => Yii::t('website', 'New Menu')
	]);
?>

<div class="menu-index">
	<?= GridView::widget([
		'dataProvider' => $menus,
		'tableOptions' => ['class' => 'table table-striped'],
		'columns'      => [
			['class' => 'yii\grid\SerialColumn'],
			[
				'attribute' => 'name',
				'format'    => 'raw',
				'value'     => function ($data){
					return Html::a($data->name, ['list', 'id' => $data->id]);
				}
			],
			[
				'attribute' => 'position',
				'value'     => 'positionLabel'
			],
			[
				'class'  => StatusColumn::class,
				'action' => ['active']
			],
			[
				'attribute' => 'language',
				'value'     => 'languageLabel'
			],
			[
				'format' => 'raw',
				'value'  => function ($data){
					$html = '<div class="btn-group btn-group-sm" role="group">';
					$html .= Html::a('<i class="fe fe-edit-2"></i>',
						['create', 'id' => $data->id], [
							'class'          => 'btn btn-secondary',
							'data-bs-toggle' => "modal",
							'data-bs-target' => "#global-modal",
							'data-header'    => Yii::t('website', 'Update Menu')
						]);
					$html .= Html::a('<i class="fe fe-menu"></i>',
						['list', 'id' => $data->id], [
							'class' => 'btn btn-primary'
						]);

					if (!$data->isRelated()){
						$html .= Html::a('<i class="fe fe-trash"></i>',
							['delete', 'id' => $data->id], [
								'class'        => 'btn btn-danger',
								'data-method'  => "post",
								'data-confirm' => Yii::t('common',
									'Are you sure you want to delete this item?')
							]);
					}

					$html .= '</div>';

					return $html;
				}
			]
		],
	]); ?>

</div>