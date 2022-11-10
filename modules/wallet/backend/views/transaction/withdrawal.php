<?php
/**
 * @var \yii\web\View $this
 * @var \yii\data\ActiveDataProvider $data
 * @var array $filtering
 * @var array $filters
 */

use backend\base\GridView;
use modules\wallet\models\Transaction;
use yii\grid\SerialColumn;
use yii\helpers\Html;
use yii\web\View;

$this->title = Yii::t('wallet', 'Withdrawals');

$this->params['breadcrumbs'][] = [
	'url'   => ['default/index'],
	'label' => Yii::t('wallet', 'eWallets')
];
$this->params['breadcrumbs'][] = $this->title;
?>

	<div class="filter">
		<?= $this->render('_filter', ['filtering' => $filtering, 'filters' => $filters]) ?>
	</div>

<?= GridView::widget([
	'dataProvider' => $data,
	'columns'      => [
		[
			'class' => SerialColumn::class
		],
		[
			'attribute' => 'customer.name',
			'format'    => 'html',
			'value'     => 'wallet.customerDetail'
		],
		[
			'attribute' => 'transaction_id',
			'format'    => 'raw',
			'value'     => function (Transaction $data){
				if (Yii::$app->user->can('wallet transaction detail')){
					return Html::a($data->transaction_id, ['transaction/view', 'id' => $data['id']],
						[
							'data-bs-toggle' => "modal",
							'data-bs-target' => "#global-modal",
							'data-header'    => Yii::t('wallet', 'Activities - {0}',
								[$data['typeLabel']])
						]);
				}

				return $data->transaction_id;
			}
		],
		[
			'attribute' => 'type',
			'value'     => 'typeHtml',
			'format'    => 'html'
		],
		'amount:currency',
		'balance:currency',
		'reference_id',
		[
			'attribute' => 'status',
			'value'     => 'statusHtml',
			'format'    => 'html'
		],
		'created_at:datetime',
		[
			'format' => 'raw',
			'value'  => function ($data){
				$result = '<div class="btn-group btn-group-sm" role="group">';
				if (Yii::$app->user->can('wallet transaction detail')){
					$result .= Html::a('<i class="fe fe-eye"></i>',
						['transaction/view', 'id' => $data['id']],
						[
							'class'          => 'btn btn-secondary',
							'data-bs-toggle' => "modal",
							'data-bs-target' => "#global-modal",
							'data-header'    => Yii::t('wallet', 'Activities - {0}',
								[$data['typeLabel']])
						]
					);
				}

				if (Yii::$app->user->can('wallet transaction approve')){
					$result .= Html::a('<i class="fe fe-thumbs-up"></i>',
						['transaction/operator', 'id' => $data['id'], 'action' => 'approve'],
						[
							'class'          => 'btn btn-success',
							'data-bs-toggle' => "modal",
							'data-bs-target' => "#confirm-modal",
							'data-header'    => Yii::t('wallet', 'Approve this transaction')
						]
					);
					$result .= Html::a('<i class="fe fe-thumbs-down"></i>',
						['transaction/operator', 'id' => $data['id'], 'action' => 'reject'],
						[
							'class'          => 'btn btn-danger',
							'data-bs-toggle' => "modal",
							'data-bs-target' => "#confirm-modal",
							'data-header'    => Yii::t('wallet', 'Reject this transaction')
						]
					);
				}

				return $result . '</div>';
			}
		]
	]
]) ?>

<?php
$js = <<<JS
	$('#confirm-modal').on('show.bs.modal', function (e) {
	    var button = $(e.relatedTarget);
	    var href = button.attr('href');
	
	    if (typeof href !== 'undefined') {
	        var modal = $(this);
	        modal.find('.modal-body').html('<div class="spinner-grow text-primary" role="status">' +
	            '        <span class="sr-only">Loading...</span>' +
	            '    </div>');
	
	        if (button.data('header')) {
	            modal.find('.modal-header .modal-title').text(button.data('header'));
	        }
	
	        $.ajax({
	            type: 'POST',
	            url: href,
	            success: function (result) {
	                modal.find('.modal-body').html(result);
	            }
	        });
	    }
	});
JS;
$this->registerJs($js, View::POS_READY, 'approve-modal');