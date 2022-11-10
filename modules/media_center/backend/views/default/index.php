<?php
/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $import_logs
 * @var array $filtering
 * @var array $filters
 */

use backend\base\GridView;
use modules\media_center\backend\models\Import;
use yii\grid\SerialColumn;
use yii\helpers\Html;

$this->title = Yii::t('media_center', 'Import Logs');

$this->params['breadcrumbs'][] = Yii::t('media_center', 'Media Center');
$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->can('media_center import')){
	$this->params['primary_link'] = Html::a('<i class="fe fe-upload"></i> ' . Yii::t('media_center',
			'Import'), ['import/index'], [
		'class' => 'btn btn-primary lift'
	]);
}
?>

<div class="import-index">
	<div class="filter">
		<form class="form-inline" action="" method="get">
			<div class="input-group">
				<label class="control-label"><?= Yii::t('media_center', 'Importer') ?></label>
				<?= Html::dropDownList('type', $filtering['type'] ?? NULL,
					$filters['types'],
					['class' => 'form-select', 'data-toggle' => 'select', 'prompt' => Yii::t('common',
						'All')]) ?>
			</div>
			<div class="input-group">
				<label class="control-label"><?= Yii::t('common', 'Status') ?></label>
				<?= Html::dropDownList('status', $filtering['status'] ?? NULL,
					$filters['statuses'],
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
		'dataProvider' => $import_logs,
		'columns'      => [
			[
				'class' => SerialColumn::class
			],
			[
				'attribute' => 'importer',
				'value'     => 'name'
			],
			[
				'attribute' => 'filename',
				'format'    => 'raw',
				'value'     => function ($data){
					return Html::a(basename($data->filename),
						['download', 'id' => $data->id],
						['download' => TRUE, 'data-method' => 'post']);
				}
			],
			[
				'attribute' => 'created_at',
				'format'    => 'datetime'
			],
			[
				'attribute' => 'created_by',
				'value'     => 'author.name',
			],
			[
				'attribute' => 'status',
				'value'     => 'statusLabel',
			],
			[
				'label'  => 'Result',
				'format' => 'html',
				'value'  => function (Import $data){
					$html = Yii::t('media_center', 'Imported: {0} <br>',
						$data->description['total_imports'] ?? 0);
					$html .= Yii::t('media_center', 'Skipped: {0} <br>',
						$data->description['total_skips'] ?? 0);

					return $html . Yii::t('media_center', 'Failed: {0}',
							$data->description['total_errors'] ?? 0);
				}
			],
			[
				'attribute' => 'error_log',
				'visible'   => Yii::$app->user->can('import download_error_log'),
				'format'    => 'html',
				'value'     => function ($data){
					if (empty($data->error_log)){
						return NULL;
					}

					return Html::a('Download', ['download-error-log', 'id' => $data->id],
						['class' => 'text-danger']);
				}
			]
		]
	]) ?>
</div>
