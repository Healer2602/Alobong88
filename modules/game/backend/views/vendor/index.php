<?php
/**
 * @var \yii\web\View $this
 * @var \yii\data\ActiveDataProvider $vendors
 * @var array $filters
 * @var array $filtering
 */

use backend\base\GridView;
use modules\media\widgets\MediaModal;
use modules\spider\lib\sortable\SortableColumn;
use yii\bootstrap5\ActiveForm;
use yii\grid\SerialColumn;
use yii\helpers\Html;

$this->title = Yii::t('game', 'Vendors');

$this->params['breadcrumbs'][] = [
	'url'   => ['default/index'],
	'label' => Yii::t('game', 'Game Management')
];
$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->can('game vendor upsert')){
	$this->params['primary_link'] = Html::a(Yii::t('game', 'New Vendor'), ['create'],
		[
			'class'          => 'btn btn-new btn-primary',
			'data-bs-toggle' => "modal",
			'data-bs-target' => "#global-modal",
			'data-header'    => Yii::t('game', 'New Vendor')
		]);
}
?>

	<div class="filter">
		<form class="form-inline" action="" method="get">
			<div class="input-group">
				<label class="control-label"><?= Yii::t('common', 'Keyword') ?></label>
				<?= Html::textInput('s', $filtering['s'] ?? NULL,
					['class' => 'form-control', 'placeholder' => Yii::t('common', 'Keyword')]) ?>
			</div>
			<div class="input-group">
				<label class="control-label"><?= Yii::t('common', 'Status') ?></label>
				<?= Html::dropDownList('state', $filtering['state'] ?? NULL,
					$filters['states'], ['class' => 'form-select', 'data-toggle' => 'select']) ?>
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

<?php ActiveForm::begin(['id' => 'sortableList']); ?>
<?= GridView::widget([
	'dataProvider' => $vendors,
	'columns'      => [
		['class' => SortableColumn::class],
		['class' => SerialColumn::class],
		'name:text',
		'icon:image',
		[
			'class'  => 'common\base\grid\StatusColumn',
			'action' => ['active']
		],
		'created_at:datetime',
		[
			'format'  => 'raw',
			'visible' => Yii::$app->user->can('game vendor upsert'),
			'value'   => function ($data){
				$result = '<div class="btn-group btn-group-sm" role="group">';
				$result .= Html::a('<i class="fe fe-edit-2"></i>',
					['update', 'id' => $data['id']],
					[
						'class'          => 'btn btn-secondary',
						'data-bs-toggle' => "modal",
						'data-bs-target' => "#global-modal",
						'data-header'    => Yii::t('game', 'Update {0}',
							[$data['name']])
					]
				);

				if (Yii::$app->user->can('game vendor delete')){
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
]) ?><?php ActiveForm::end(); ?>
<?= MediaModal::widget([
	'id' => 'my-media-modal'
]) ?>