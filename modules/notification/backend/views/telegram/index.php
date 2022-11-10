<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var \modules\notification\models\TelegramSetting $model
 */

$this->title = Yii::t('common', 'Telegram Settings');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Settings'),
	'url'   => ['/setting/index']
];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $form = ActiveForm::begin() ?>
	<div class="row justify-content-center">
		<div class="col-lg-6">

			<?= $form->field($model, 'telegram_url')->textarea(['rows' => 3]) ?>

			<?= $form->field($model, 'telegram_withdraw_mention') ?>

			<div class="form-group">
				<?= Html::submitButton(Yii::t('common', 'Update'),
					['class' => 'btn btn-primary']) ?>
				<?= Html::a(Yii::t('common', 'Cancel'), [''],
					['class' => 'btn btn-secondary']) ?>
			</div>
		</div>
	</div>
<?php ActiveForm::end() ?>