<?php
/**
 * @var \yii\web\View $this
 * @var \yii\data\ActiveDataProvider $email_templates
 * @var array $filters
 */

use backend\base\GridView;
use yii\grid\SerialColumn;
use yii\helpers\Html;

$this->title = Yii::t('common', 'Email Templates');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Settings'),
	'url'   => ['/setting/index']
];
$this->params['breadcrumbs'][] = $this->title;

$this->params['primary_link'] = '';
if (Yii::$app->user->can('notification email_template upsert')){
	$this->params['primary_link'] .= Html::a(Yii::t('common', 'New Template'), ['create'],
		[
			'class' => 'btn btn-new btn-primary'
		]);
}

if (Yii::$app->user->can('setting email')){
	$this->params['primary_link'] .= Html::a(Html::tag('i', '',
			['class' => 'fas fa-cog me-2']) . Yii::t('common',
			'Email Settings'), ['email/setting'],
		['class' => 'btn btn-primary']);
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
				<label class="control-label"><?= Yii::t('common', 'Trigger') ?></label>
				<?= Html::dropDownList('trigger', $filtering['trigger'] ?? NULL,
					$filters['triggers'],
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
	'dataProvider' => $email_templates,
	'columns'      => [
		[
			'class' => SerialColumn::class
		],
		'name:text',
		[
			'attribute' => 'language',
			'value'     => 'languageLabel',
		],
		'subject:text',
		[
			'attribute' => 'trigger_key',
			'value'     => 'appTrigger.name'
		],
		['class' => 'common\base\grid\StatusColumn', 'action' => ['active']],
		[
			'format'  => 'raw',
			'visible' => Yii::$app->user->can('notification email_template upsert'),
			'value'   => function ($data){
				$result = '<div class="btn-group btn-group-sm" role="group">';
				$result .= Html::a('<i class="fe fe-edit-2"></i>',
					['create', 'id' => $data['id']],
					[
						'class' => 'btn btn-secondary'
					]
				);
				$result .= Html::a('<i class="fe fe-trash"></i>',
					['delete', 'id' => $data['id']],
					[
						'class'        => 'btn btn-danger',
						"data-confirm" => "Are you sure you want to delete this item?",
						'data-method'  => 'post'
					]
				);
				$result .= '</div>';

				return $result;
			}
		]
	]
]) ?>