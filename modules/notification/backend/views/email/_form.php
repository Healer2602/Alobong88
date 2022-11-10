<?php
/**
 * @var \yii\web\View $this
 * @var \modules\notification\models\EmailTemplate $model
 */

use common\models\Language;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('common', 'Email Templates');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Settings'),
	'url'   => ['/setting/index']
];
$this->params['breadcrumbs'][] = [
	'label' => $this->title,
	'url'   => ['index']
];

$this->params['primary_link'] = '';
if (Yii::$app->user->can('notification email_template upsert')){
	$this->params['primary_link'] .= Html::a(Yii::t('common', 'New Template'), ['create'],
		[
			'class' => 'btn btn-new btn-primary'
		]);
}
?>

	<div class="row justify-content-center">
		<div class="col-lg-8 col-xl-6">
			<div class="row">

				<?php $form = ActiveForm::begin([
					'id' => 'template_form',
				]); ?>

				<?= $form->field($model, 'trigger_key')
				         ->dropDownList($model->triggers, ['id' => 'template_trigger']) ?>

				<?= $form->field($model, 'name') ?>

				<?= $form->field($model, 'subject') ?>

				<?= $form->field($model, 'language')
				         ->dropDownList(Language::listLanguage(), ['id' => 'language']) ?>

				<?= $form->field($model, 'content')
				         ->textarea(['rows' => 4, 'class' => 'form-control editor']) ?>

				<div class="form-group">
					<?= Html::submitButton(Yii::t('common',
						$model->isNewRecord ? 'Create' : 'Update'),
						['class' => 'btn btn-primary']) ?>
					<?= Html::a(Yii::t('common', 'Cancel'), ['index'],
						['class' => 'btn btn-secondary', 'data-bs-dismiss' => "modal"]) ?>
				</div>

				<?= Html::hiddenInput('id', $model->id,
					['class' => 'template_id']) ?>

				<?php ActiveForm::end(); ?>

			</div>
		</div>
	</div>


<?php
$action_url = Url::to(['create']);

$js = <<< JS
    $(document).on('change', '#template_trigger', function(event){
       location.href = '$action_url' + '?key=' + $(this).val() + '&id={$model->id}'
    });
JS;

$this->registerJs($js);
