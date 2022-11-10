<?php
/**
 * @var \yii\web\View $this
 * @var \yii\data\ActiveDataProvider $audit_trails
 * @var array $filtering
 * @var \backend\models\AuditTrailSearchModel $search_model
 */

use backend\base\GridView;
use yii\helpers\Html;

$this->title = Yii::t('common', 'Audit Trails');

$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->can('system logs export')){
	$this->params['primary_link'] = Html::a('<i class="fe fe-download"></i>' . Yii::t('common',
			'Export'), ['export'] + $filtering,
		['class' => 'btn btn-primary btn-with-icon ml-auto', 'data-method' => 'POST']);
}
?>

	<div class="filter">
		<form class="form-inline" action="" method="get">
			<?php if ($systems = $search_model->getSystems()): ?>
				<div class="input-group">
					<label class="control-label"><?= $search_model->getAttributeLabel('system') ?></label>
					<?= Html::dropDownList('system', $filtering['system'] ?? NULL,
						$systems,
						['class' => 'form-select', 'data-toggle' => 'select', 'prompt' => Yii::t('common',
							'All')]) ?>
				</div>
			<?php endif; ?>
			<?php if ($modules = $search_model->getModules()): ?>
				<div class="input-group">
					<label class="control-label"><?= $search_model->getAttributeLabel('module') ?></label>
					<?= Html::dropDownList('module', $filtering['module'] ?? NULL,
						$modules,
						['class' => 'form-select', 'data-toggle' => 'select', 'prompt' => Yii::t('common',
							'All')]) ?>
				</div>
			<?php endif; ?>
			<div class="input-group">
				<label class="control-label"><?= $search_model->getAttributeLabel('user') ?></label>
				<?= Html::dropDownList('user', $filtering['user'] ?? NULL,
					$search_model->getUsers(),
					['class' => 'form-select', 'data-toggle' => 'select', 'prompt' => Yii::t('common',
						'All')]) ?>
			</div>
			<div class="input-group">
				<label class="control-label"><?= $search_model->getAttributeLabel('from') ?></label>
				<?= Html::textInput('from', $filtering['from'] ?? NULL,
					['class' => 'form-control', 'data-flatpickr' => ['enableTime' => TRUE]]) ?>
			</div>
			<div class="input-group">
				<label class="control-label"><?= $search_model->getAttributeLabel('to') ?></label>
				<?= Html::textInput('to', $filtering['to'] ?? NULL,
					['class' => 'form-control', 'data-flatpickr' => ['enableTime' => TRUE]]) ?>
			</div>
			<div class="input-group">
				<label class="control-label"><?= $search_model->getAttributeLabel('keywords') ?></label>
				<?= Html::textInput('keywords', $filtering['keywords'] ?? NULL,
					['class' => 'form-control']) ?>
			</div>
			<div class="input-group">
				<label class="control-label"><?= $search_model->getAttributeLabel('ip') ?></label>
				<?= Html::textInput('ip', $filtering['ip'] ?? NULL,
					['class' => 'form-control']) ?>
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
	'dataProvider' => $audit_trails,
	'columns'      => [
		'id',
		[
			'attribute' => 'system',
			'value'     => 'systemLabel'
		],
		'module',
		[
			'attribute' => 'action',
		],
		[
			'attribute'      => 'message',
			'format'         => 'html',
			'value'          => function ($data){
				return nl2br($data->message);
			},
			'contentOptions' => ['style' => 'max-width: 350px;  overflow-wrap: break-word;']
		],
		[
			'attribute' => 'user_id',
			'value'     => function ($data){
				return $data->author->name ?? $data->user_name ?? 'SYSTEM';
			},
		],
		'ip_address',
		'created_at:datetime',
	]
]) ?>