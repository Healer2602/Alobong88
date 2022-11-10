<?php

use modules\customer\widgets\Menu;
use yii\bootstrap5\Html;
use yii\grid\GridView;

/**
 * @var \yii\web\View $this
 * @var \yii\data\ActiveDataProvider $data
 */

$this->title = Yii::t('wallet', 'Wallet transactions');
?>
<div class="customer-dashboard">
	<div class="container">
		<div class="row">
			<div class="col-xxl-auto col-lg-3 mb-xxl-0 mb-4">
				<div class="menu-account">
					<?= Menu::widget() ?>
				</div>
			</div>
			<div class="col-xxl col-lg-9">
				<h1 class="heading my-2 my-md-4"><?= Html::encode($this->title) ?></h1>
				<div class="history detail-list wallet-transactions px-0">
					<?= GridView::widget([
						'dataProvider' => $data,
						'showHeader'   => FALSE,
						'tableOptions' => ['class' => 'table mt-2'],
						'emptyText'    => Yii::t('common', 'No records found.'),
						'columns'      => [
							[
								'attribute'      => 'created_at',
								'format'         => 'datetime',
								'contentOptions' => ['class' => 'date-item']
							],
							[
								'format' => 'html',
								'value'  => 'typeIcon'
							],
							[
								'format' => 'html',
								'value'  => 'statusHtml'
							],
							[
								'format'         => 'html',
								'contentOptions' => ['class' => 'text-right'],
								'value'          => 'amountHtml'
							]
						]
					]) ?>
				</div>
			</div>
		</div>
	</div>
</div>