<?php
/**
 * @var \yii\web\View $this
 * @var \yii\data\ActiveDataProvider $games
 * @var array $filters
 * @var array $filtering
 */

use backend\base\GridView;
use modules\spider\lib\sortable\SortableColumn;
use yii\bootstrap5\ActiveForm;
use yii\grid\SerialColumn;
use yii\helpers\Html;

$this->title                   = Yii::t('game', 'Games');
$this->params['breadcrumbs'][] = Yii::t('game', 'Game Management');
$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->can('game upsert')){
	$this->params['primary_link'] = Html::a(Yii::t('game', 'New Game'), ['create'],
		['class' => 'btn btn-new btn-primary']);
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
				<label class="control-label"><?= Yii::t('game', 'Vendor') ?></label>
				<?= Html::dropDownList('vendor', $filtering['vendor'] ?? NULL,
					$filters['vendors'],
					['class' => 'form-select', 'data-toggle' => 'select', 'prompt' => Yii::t('common',
						'All')]) ?>
			</div>
			<div class="input-group">
				<label class="control-label"><?= Yii::t('game', 'Type') ?></label>
				<?= Html::dropDownList('type', $filtering['type'] ?? NULL,
					$filters['types'],
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
						<i class="fe fe-x" aria-hidden="true"></i></button>
				</div>
			</div>
		</form>
	</div>

<?php ActiveForm::begin(['id' => 'sortableList']); ?>
<?= GridView::widget([
	'dataProvider' => $games,
	'columns'      => [
		['class' => SortableColumn::class],
		['class' => SerialColumn::class],
		[
			'attribute' => 'icon',
			'format'    => 'html',
			'value'     => function ($data){
				return Yii::$app->formatter->asImage($data->icon, ['style' => 'max-height: 50px']);
			}
		],
		'name:text',
		[
			'label' => Yii::t('game', 'Chinese'),
			'value' => 'detailZh.name'
		],
		[
			'label' => Yii::t('game', 'Vietnamese'),
			'value' => 'detailVi.name'
		],
		'code:text',
		[
			'label' => Yii::t('game', 'Vendor'),
			'value' => 'vendor.name'
		],
		[
			'label' => Yii::t('game', 'Type'),
			'value' => 'type.name'
		],
		[
			'header'    => Yii::t('game', 'Feature'),
			'attribute' => 'feature',
			'class'     => 'common\base\grid\StatusColumn',
			'action'    => ['feature']
		],
		[
			'class'  => 'common\base\grid\StatusColumn',
			'action' => ['active']
		],
		'created_at:datetime',
		[
			'format'  => 'raw',
			'visible' => Yii::$app->user->can('game upsert'),
			'value'   => function ($data){
				$result = '<div class="btn-group btn-group-sm" role="group">';
				$result .= Html::a('<i class="fe fe-edit-2"></i>',
					['update', 'id' => $data['id']],
					['class' => 'btn btn-secondary']
				);

				if (Yii::$app->user->can('game delete')){
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