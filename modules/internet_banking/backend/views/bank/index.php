<?php

use backend\base\GridView;
use yii\bootstrap5\Modal;
use yii\helpers\Html;

/* @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $data
 * @var array $filtering
 * @var array $filters
 */


$this->title = Yii::t('internet_banking', 'Internet Banking Banks');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Settings'),
	'url'   => ['/setting/index']
];

$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->can('internet_banking bank upsert')){
	$this->params['primary_link'] = Html::a(Yii::t('internet_banking', 'New Bank'), ['create'],
		[
			'class'          => 'btn btn-new btn-primary',
			'data-bs-toggle' => "modal",
			'data-bs-target' => "#global-modal",
			'data-header'    => Yii::t('internet_banking', 'New Bank')
		]);
}
?>
	<div class="bank-index">

		<div class="filter">
			<form class="form-inline" action="" method="get">
				<div class="input-group">
					<label class="control-label"><?= Yii::t('common', 'Keywords') ?></label>
					<?= Html::textInput('s', $filtering['s'] ?? NULL,
						['class' => 'form-control', 'placeholder' => Yii::t('common',
							'Keywords')]) ?>
				</div>
				<div class="input-group">
					<label class="control-label"><?= Yii::t('common', 'Status') ?></label>
					<?= Html::dropDownList('state', $filtering['state'] ?? NULL,
						$filters['states'],
						['class' => 'form-select', 'data-toggle' => 'select', 'prompt' => Yii::t('common',
							'All')]) ?>
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
			'dataProvider' => $data,
			'columns'      => [
				['class' => 'yii\grid\SerialColumn'],
				[
					'attribute' => 'name',
					'value'     => 'title'
				],
				[
					'attribute' => 'logo',
					'format'    => 'html',
					'value'     => function ($data){
						if (!empty($data->logoUrl)){
							return Html::img($data->logoUrl, ['style' => 'height: 50px']);
						}

						return NULL;
					}
				],
				'code',
				'currency_code',
				[
					'class'  => 'common\base\grid\StatusColumn',
					'action' => ['active'],
					'header' => Yii::t('common', 'Status'),
				],
				'id',
				[
					'headerOptions' => ['class' => 'action'],
					'format'        => 'raw',
					'value'         => function ($data){
						$result = '<div class="btn-group btn-group-sm" role="group">';

						if (Yii::$app->user->can('internet_banking bank upsert')){
							$result .= Html::a('<i class="fe fe-edit-2"></i>',
								['update', 'id' => $data['id']],
								[
									'class'          => 'btn btn-secondary',
									'data-bs-toggle' => "modal",
									'data-bs-target' => "#global-modal",
									'data-header'    => Yii::t('internet_banking', 'Update Bank')
								]
							);
						}

						if (Yii::$app->user->can('internet_banking bank delete')){
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
	</div>

<?php Modal::begin([
	'id'      => 'media-modal',
	'title'   => 'Media Manager',
	'options' => ['class' => 'modal-ajax fade media-dialog', 'tabindex' => NULL],
	'size'    => 'modal-xl'
]); ?>

<?php Modal::end(); ?>