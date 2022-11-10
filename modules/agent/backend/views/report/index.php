<?php

use backend\base\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var yii\web\View $this */
/* @var yii\data\ActiveDataProvider $agents */
/* @var array $filtering */
/* @var array $filters */

$this->title                   = Yii::t('agent', 'Agent Report');
$this->params['breadcrumbs'][] = $this->title;

?>
	<div class="post-index">

		<div class="filter">
			<form class="form-inline" action="" method="get">
				<div class="input-group">
					<label class="control-label"><?= Yii::t('common', 'From') ?></label>
					<?= Html::textInput('from', $filtering['from'] ?? NULL,
						['class' => 'form-control month']) ?>
				</div>
				<div class="input-group">
					<label class="control-label"><?= Yii::t('common', 'To') ?></label>
					<?= Html::textInput('to', $filtering['to'] ?? NULL,
						['class' => 'form-control month']) ?>
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

		<?= GridView::widget([
			'dataProvider' => $agents,
			'columns'      => [
				['class' => 'yii\grid\SerialColumn'],
				[
					'attribute' => 'customer_id',
					'value'     => 'customerDetail'
				],
				'code',
				[
					'label' => Yii::t('agent', 'Active Users'),
					'value' => function ($data){
						if (empty($data->reports)){
							return 0;
						}

						return array_sum(ArrayHelper::getColumn($data->reports,
							'active_users'));
					}
				],
				[
					'label' => Yii::t('agent', 'Total Invests'),
					'value' => function ($data){
						if (empty($data->reports)){
							return 0;
						}

						return array_sum(ArrayHelper::getColumn($data->reports,
							'total_invests'));
					}
				],
				[
					'label'  => Yii::t('agent', 'Profits'),
					'format' => 'currency',
					'value'  => function ($data){
						if (empty($data->reports)){
							return 0;
						}

						return array_sum(ArrayHelper::getColumn($data->reports,
							'profits'));
					}
				],
				[
					'label'  => Yii::t('agent', 'Commissions'),
					'format' => 'currency',
					'value'  => function ($data){
						if (empty($data->reports)){
							return 0;
						}

						return array_sum(ArrayHelper::getColumn($data->reports,
							'commissions'));
					}
				],
				[
					'label'  => Yii::t('agent', 'Company profit'),
					'format' => 'currency',
					'value'  => function ($data){
						if (empty($data->reports)){
							return 0;
						}

						return array_sum(ArrayHelper::getColumn($data->reports,
								'profits')) - array_sum(ArrayHelper::getColumn($data->reports,
								'commissions'));
					}
				],
				[
					'label'   => Yii::t('agent', 'Reported At'),
					'format'  => 'datetime',
					'value'   => 'report.reported_at',
					'visible' => empty($filtering['to'])
				]
			],
		]); ?>
	</div>

<?php
$js = <<<JS
    $('input.month').flatpickr({
        plugins: [
            new monthSelectPlugin({
                shorthand: true,
                dateFormat: "Y-m",
                altFormat: "Y-m"
            })
        ]
    });
JS;
$this->registerJs($js);