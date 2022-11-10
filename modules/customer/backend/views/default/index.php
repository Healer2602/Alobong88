<?php
/**
 * @var \yii\web\View $this
 * @var \yii\data\ActiveDataProvider $customers
 * @var array $filters
 * @var array $filtering
 */

use backend\base\GridView;
use yii\grid\SerialColumn;
use yii\helpers\Html;

$this->title                   = Yii::t('customer', 'Players');
$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->can('customer upsert')){
	$this->params['primary_link'] = Html::a(Yii::t('customer', 'New Player'), ['create'],
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
                <label class="control-label"><?= Yii::t('customer', 'Rank') ?></label>
				<?= Html::dropDownList('rank', $filtering['rank'] ?? NULL,
					$filters['ranks'],
					['data-toggle' => 'select', 'class' => 'form-select', 'prompt' => Yii::t('common',
						'All')]) ?>
            </div>
            <div class="input-group">
                <label class="control-label"><?= Yii::t('common', 'Status') ?></label>
				<?= Html::dropDownList('state', $filtering['state'] ?? NULL,
					$filters['states'], ['data-toggle' => 'select', 'class' => 'form-select']) ?>
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
	'dataProvider' => $customers,
	'columns'      => [
		[
			'class' => SerialColumn::class
		],
		[
			'attribute' => 'name',
			'format'    => 'raw',
			'value'     => function ($data){
				$name = $data->name;
				if ($data->isVerified){
					$name .= Html::tag('span', '<i class="fe fe-check" aria-hidden="true"></i>',
						['class' => 'badge bg-success rounded-circle p-1 ms-2', 'title' => Yii::t('common',
							'Verified')]);
				}

				return Html::a($name, ['update', 'id' => $data['id']]);
			},
		],
		'username:text',
		'email:email',
		'phone_number:text',
		'currency',
		'ip_address',
		[
			'attribute' => 'referral_id',
			'format'    => 'email',
			'value'     => 'referralCustomer.email'
		],
		[
			'attribute' => 'agent_id',
			'format'    => 'email',
			'value'     => 'agent.email'
		],
		[
			'class'  => 'common\base\grid\StatusColumn',
			'action' => ['active'],
			'header' => Yii::t('customer', 'Activation')
		],
		'created_at:datetime',
		[
			'format'  => 'raw',
			'visible' => Yii::$app->user->can('user upsert'),
			'value'   => function ($data){
				$result = '<div class="btn-group btn-group-sm" role="group">';
				$result .= Html::a('<i class="fe fe-edit-2"></i>',
					['update', 'id' => $data['id']],
					['class' => 'btn btn-secondary']
				);

				if (!$data->isRelated){
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
]) ?>