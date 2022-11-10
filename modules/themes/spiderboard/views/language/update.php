<?php

use yii\helpers\Html;

/**
 * @var \backend\base\View $this
 * @var \common\models\Language $model
 * @var \backend\models\StringTranslate $translate
 */

$this->title = Yii::t('common', 'Update Language: {0}', [$model->name]);

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Settings'),
	'url'   => ['/setting/index']
];
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Languages'),
	'url'   => ['index']
];
$this->params['breadcrumbs'][] = $this->title;

if (Yii::$app->user->can('language upsert')){
	$this->params['primary_link'] = Html::a(Yii::t('common', 'New Language'), ['create'],
		['class' => 'btn btn-new btn-primary lift']);
}
?>

	<div class="row justify-content-center">
		<div class="col-md-6">
			<div class="header-body pt-0 mb-4">
				<ul class="nav nav-tabs nav-overflow header-tabs">
					<li class="nav-item py-3 <?= (empty($translate) ? 'border-bottom border-primary' : '') ?>">
						<?= Html::a(Yii::t('common', 'Details'), ['update', 'id' => $model->id],
							['class' => 'btn btn-link text-nowrap font-weight-bold']) ?>
					</li>
					<li class="nav-item py-3 <?= (!empty($translate) ? 'border-bottom border-primary' : '') ?>">
						<?= Html::a(Yii::t('common', 'String Translate'),
							['translate', 'id' => $model->id],
							['class' => 'btn btn-link text-nowrap font-weight-bold']) ?>
					</li>
					<?php if (!empty($translate)): ?>
						<li class="nav-item py-3 ml-auto align-items-center d-inline-flex">
							<?= Html::a(Yii::t('common', 'Load Default'),
								['translate', 'id' => $model->id, 'force' => TRUE],
								['class' => 'btn btn-sm btn-white']) ?>
						</li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
	</div>


<?php if (empty($translate)){
	echo $this->render('_form', [
		'model' => $model
	]);
}else{
	echo $this->render('_translate', [
		'translate' => $translate,
		'model'     => $model
	]);
} ?>