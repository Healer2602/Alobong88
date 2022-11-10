<?php

use backend\base\GridView;
use yii\bootstrap5\ActiveForm;
use yii\grid\SerialColumn;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var \modules\customer\models\Referral $model
 * @var \modules\customer\models\AssignCustomer $assign
 * @var \yii\data\ActiveDataProvider $customers
 */

$this->title = Yii::t('customer', 'Referral: {0}', [$model->customerDetail]);

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('customer', 'Players'),
	'url'   => ['default/index']
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('customer', 'Referral'),
	'url'   => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="coupon-update">
	<div class="row">
		<div class="col-lg-4">
			<div class="card">
				<div class="card-header">
					<h4 class="card-header-title"><?= Yii::t('customer', 'Information') ?></h4>
				</div>
				<div class="card-body pb-0">
					<?= $this->render('_form', [
						'model' => $model,
					]) ?>
				</div>
			</div>
		</div>
		<div class="col-lg-8">
			<div class="card">
				<div class="card-header">
					<h4 class="card-header-title"><?= Yii::t('customer', 'Assign Player') ?></h4>
				</div>
				<div class="card-body pb-0">
					<?php $form = ActiveForm::begin(); ?>

					<div class="row">
						<div class="col">
							<?= $form->field($assign, 'email')
							         ->textInput(['placeholder' => $assign->getAttributeLabel('email')])
							         ->label(FALSE) ?>
						</div>
						<div class="col-auto">
							<?= Html::submitButton(Yii::t('common', 'Add'),
								['class' => 'btn btn-primary w-100']) ?>
						</div>
					</div>

					<?php ActiveForm::end(); ?>
				</div>
			</div>
			<div class="card">
				<div class="card-header">
					<h4 class="card-header-title"><?= Yii::t('customer', 'Players') ?></h4>
				</div>
				<div class="card-header card-filter">
					<div class="filter mb-0 pt-2">
						<form class="form-inline" action="" method="get">
							<div class="input-group">
								<?= Html::textInput('s', Yii::$app->request->get('s'),
									['class' => 'form-control', 'placeholder' => Yii::t('wallet',
										'Search by player')]) ?>
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
				</div>
				<?= GridView::widget([
					'options'      => ['class' => 'table-responsive'],
					'layout'       => "{items}<div class='p-3 d-flex justify-content-end'>{pager}</div>{summary}",
					'dataProvider' => $customers,
					'tableOptions' => ['class' => 'table table-nowrap table-hover card-table'],
					'columns'      => [
						[
							'class' => SerialColumn::class
						],
						[
							'attribute' => 'customer.name',
							'format'    => 'html',
							'value'     => function ($data){
								$name = $data->customer->name ?? NULL;

								if (Yii::$app->user->can('wallet detail')){
									return Html::a($name,
										['/wallet/default/view', 'id' => $data->id]);
								}

								return $name;
							}
						],
						'customer.email',
						[
							'attribute' => 'balance',
							'format'    => 'currency'
						],
						[
							'attribute' => 'turnover',
							'format'    => 'currency'
						]
					]
				]) ?>
			</div>
		</div>
	</div>
</div>
