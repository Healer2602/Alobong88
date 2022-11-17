<?php

use backend\base\GridView;
use common\base\grid\StatusColumn;
use modules\spider\lib\sortable\SortableColumn;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $menus
 * @var \modules\website\models\Menu $menu
 * @var array $filtering
 */

$this->title = Yii::t('website', 'Menu Items of {0}', [$menu->name]);

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('website', 'Menu'),
	'url'   => ['index']
];

$this->params['breadcrumbs'][] = $menu->name;

$this->params['primary_link'] = Html::a(Yii::t('website', 'New Menu Item'),
	['item', 'menu' => $menu->id],
	[
		'class'          => 'btn btn-new btn-primary',
		'data-bs-toggle' => "modal",
		'data-bs-target' => "#global-modal",
		'data-header'    => Yii::t('website', 'New Menu Item')
	]);

$this->params['primary_link'] .= Html::a(Yii::t('common', 'Back'), ['index'],
	['class' => 'btn btn-back btn-outline-secondary']);
?>

	<div class="menu-index">
		<div class="filter">
			<form class="form-inline" action="" method="get">
				<div class="input-group">
					<label class="control-label"><?= Yii::t('common', 'Keywords') ?></label>
					<?= Html::textInput('s', $filtering['s'] ?? NULL,
						['class' => 'form-control', 'placeholder' => Yii::t('common',
							'Keywords')]) ?>
				</div>
				<div class="input-group">
					<div class="input-group-btn">
						<button class="btn btn-outline-primary" type="submit">
							<i class="fe fe-search"></i>
						</button>
						<button class="btn btn-outline-secondary clear" type="button">
							<i class="fe fe-x"></i></button>
					</div>
				</div>
			</form>
		</div>

		<?php $form = ActiveForm::begin(['id' => 'sortableList']); ?>
		<?= GridView::widget([
			'dataProvider' => $menus,
			'tableOptions' => ['class' => 'table table-striped'],
			'rowOptions'   => function ($data){
				$parents = implode(' ', ArrayHelper::getColumn($data->parents, 'id'));

				return [
					'sortable-group-id' => $data->parent_id,
					'item-id'           => $data->id,
					'parents'           => $parents,
					'level'             => $data->depth
				];
			},
			'columns'      => [
				[
					'class'   => SortableColumn::class,
					'visible' => empty($filtering['s'])
				],
				[
					'attribute' => 'icon',
					'format'    => 'image',
					'value'     => 'iconLink'
				],
				[
					'attribute' => 'name',
					'format'    => 'raw',
					'value'     => function ($data) use ($menu){
						$result = str_repeat('<span class="muted">|-</span>', $data->depth - 1);
						$result .= Html::a($data->name,
							['item', 'id' => $data->id, 'menu' => $menu->id], [
								'data-bs-toggle' => "modal",
								'data-bs-target' => "#global-modal",
								'data-header'    => Yii::t('website', 'Update Menu {0}',
									$data->name)
							]);
						if (!empty($data->coreMenu)){
							$result .= '<br>';
							$result .= str_repeat('<span class="muted">--</span>',
								$data->depth - 1);
							$result .= Html::tag('small', $data->coreMenu);
						}

						return $result;
					}
				],
				'menu_path',
				[
					'class'  => StatusColumn::class,
					'action' => ['active-item']
				],
				[
					'format' => 'raw',
					'value'  => function ($data) use ($menu){
						$html = '<div class="btn-group btn-group-sm" role="group">';
						$html .= Html::a('<i class="fe fe-edit-2"></i>',
							['item', 'id' => $data->id, 'menu' => $menu->id], [
								'class'          => 'btn btn-secondary',
								'data-bs-toggle' => "modal",
								'data-bs-target' => "#global-modal",
								'data-header'    => Yii::t('website', 'Update Menu {0}',
									$data->name)
							]);
						if (!$data->isRelated()){
							$html .= Html::a('<i class="fe fe-trash"></i>',
								['delete', 'id' => $data->id], [
									'class'        => 'btn btn-danger',
									'data-method'  => "post",
									'data-confirm' => Yii::t('website',
										'Are you sure you want to delete this item?')
								]);
						}

						$html .= '</div>';

						return $html;
					}
				]
			],
		]); ?><?php ActiveForm::end(); ?>

	</div>

<?php Modal::begin([
	'id'      => 'media-modal',
	'title'   => 'Media Manager',
	'options' => ['class' => 'modal-ajax fade media-dialog', 'tabindex' => NULL],
	'size'    => 'modal-xl'
]); ?>

<?php Modal::end(); ?>