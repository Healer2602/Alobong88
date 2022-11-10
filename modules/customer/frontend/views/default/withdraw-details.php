<?php

/**
 * @var frontend\base\View $this
 * @var yii\bootstrap5\ActiveForm $form
 * @var array $details
 * @var \modules\customer\models\CustomerBank $model
 */

use common\widgets\Alert;
use modules\customer\widgets\Menu;
use modules\themes\captain\AppAsset;
use yii\bootstrap5\Modal;
use yii\bootstrap5\Nav;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = Yii::t('customer', 'Withdraw Details');
AppAsset::register($this);
?>

	<div class="customer-dashboard kyc py-4 default-bg">
		<div class="container">
			<div class="row">
				<div class="col-xxl-auto col-lg-3 mb-xxl-0 mb-4">
					<div class="menu-account">
						<?= Menu::widget() ?>
					</div>
				</div>
				<div class="col-xxl col-lg-9">
					<div class="main-form">
						<h1 class="heading text-large"><?= Html::encode($this->title) ?></h1>

						<div class="custom-tabs">
							<button type="button" class="btn btn-round btn-outline-primary btn-filter" data-bs-toggle="modal" data-bs-target="#modal-add-bank">
								<i class="fas fa-plus" aria-hidden="true"></i> <?= Yii::t('customer',
									'Add bank') ?>
							</button>
							<?= Nav::widget([
								'items'        => [
									[
										'label' => Yii::t('customer', 'Bank Accounts'),
										'url'   => ['/customer/default/withdraw-details'],
									],
									[
										'label' => Yii::t('customer', 'Crypto Address'),
										'url'   => ['#'],
										'options' => ['class' => 'd-none']
									],
								],
								'options'      => [
									'class' => 'nav custom'
								],
								'encodeLabels' => FALSE
							]); ?>
							<?= Alert::widget() ?>
							<?= GridView::widget([
								'dataProvider' => $details,
								'tableOptions' => [
									'class' => 'table table-responsive'
								],
								'summary'      => '',
								'emptyText'    => Yii::t('common', 'No results found.'),
								'columns'      => [
									[
										'attribute' => 'bank',
										'format'    => 'html',
										'value'     => function ($data){
											return Yii::$app->formatter->asImage($data->bank->logo,
													['style' => 'max-height: 20px; margin-right: 0.5rem']) . $data->bank->name;
										}
									],
									'account_branch',
									'account_name',
									'account_id',
									[
										'format' => 'raw',
										'value' => function ($data){
											return Html::a("<i class='fas fa-trash-alt' aria-hidden='true'></i>",
												['delete-detail', 'id' => $data['id']],
												[
													"data-confirm" => Yii::t('common',
														"Are you sure you want to delete this item?"),
													'data-method'  => 'post',
													'class'        => 'btn btn-outline-primary btn-sm'
												]
											);
										}
									]
								]
							]) ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

<?php
Modal::begin([
	'id'      => 'modal-add-bank',
	'title'   => Yii::t('customer', 'Add Bank'),
	'options' => ['tabindex' => NULL],
	'size'    => 'modal-lg'
]);

echo $this->render('_form-add-bank', ['model' => $model]);
Modal::end();