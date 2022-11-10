<?php

/**
 * @var frontend\base\View $this
 * @var yii\bootstrap5\ActiveForm $form
 * @var yii\data\ActiveDataProvider $data
 * @var array $filtering
 */

use modules\customer\widgets\AccountHeader;
use modules\customer\widgets\Menu;
use yii\bootstrap5\Html;
use yii\grid\GridView;
use yii\grid\SerialColumn;

$this->title = Yii::t('wallet', 'Withdrawal/Deposit History');
?>
<div class="default-bg">
	<div class="container">
		<?= AccountHeader::widget() ?>

		<div class="row">
			<div class="col-xxl-auto col-lg-3 mb-xxl-0 mb-4">
				<div class="menu-account">
					<?= Menu::widget() ?>
				</div>
			</div>
			<div class="col-xxl col-lg-9">
				<div class="main-form">
					<h1 class="heading text-large"><?= Yii::t('wallet', 'History') ?></h1>
					<div class="custom-tabs">
						<?= $this->render('_menu-tabs') ?>
						<div class="filters mb-4" style="display: <?= empty($filtering) ? 'none' : 'block' ?>">
							<form action="" method="get">
								<div class="row align-items-end g-2">
									<div class="col-lg-4 mb-lg-0 mb-2">
										<label class="control-label mb-1"><?= Yii::t('wallet',
												'Transaction Date') ?></label>
										<?= Html::textInput('date',
											$filtering['date'] ?? NULL,
											[
												'class'          => 'form-control',
												'data-flatpickr' => ['mode' => 'range'],
											]) ?>
									</div>
									<div class="col-lg-2">
										<button type="submit" class="btn btn-round btn-primary">
											<i class="fas fa-search" aria-hidden="true"></i>
										</button>
									</div>
								</div>
							</form>
						</div>
						<div class="tab-content" id="tab-content">
							<div class="tab-pane fade show active">
								<?= GridView::widget([
									'dataProvider' => $data,
									'tableOptions' => ['class' => 'table table-nowrap history'],
									'options'      => ['class' => 'grid-view table-responsive'],
									'summary'      => FALSE,
									'columns'      => [
										['class' => SerialColumn::class],
										'created_at:datetime',
										'amount:currency',
										[
											'attribute' => 'gateway_id',
											'value'     => 'gateway.name',
										],
										[
											'attribute' => 'type',
											'value'     => 'typeLabel'
										],
										[
											'attribute' => 'status',
											'value'     => 'statusHtml',
											'format'    => 'html'
										],
									],
								]); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>