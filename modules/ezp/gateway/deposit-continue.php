<?php
/**
 * @var \yii\web\View $this
 * @var array $data
 */

use yii\bootstrap5\BootstrapAsset;
use yii\helpers\Html;
use yii\web\View;

BootstrapAsset::register($this);
?>

<?php $this->beginPage() ?>
	<!DOCTYPE html>
	<html lang="<?= Yii::$app->language ?>" class="checkout--root">
	<head>
		<meta charset="<?= Yii::$app->charset ?>">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="robots" content="noindex, nofollow">
		<?= Html::csrfMetaTags() ?>
		<title><?= Html::encode($this->title) ?></title>
		<?php $this->head() ?>
	</head>
	<body>
	<?php $this->beginBody() ?>
	<div class="container text-center">
		<div class="my-4">
			<div class="spinner-grow text-primary" role="status">
				<span class="visually-hidden">Loading...</span>
			</div>
		</div>

		<form method="POST" name="continue-form" action="<?= $data['url'] ?>" id="continue-form">
			<?php foreach ($data['data'] as $key => $datum){
				echo Html::hiddenInput($key, $datum);
			} ?>
			<p class="text-center"><?= Yii::t('ezp',
					'Please below button if you are not redirected within a few seconds.') ?></p>

			<button class="btn btn-primary btn-lg" type="submit"><?= Yii::t('ezp',
					'Continue') ?></button>
		</form>
	</div>
	<?php
	$js = <<<JS
	document.getElementById("continue-form").submit();
JS;
	$this->registerJs($js, View::POS_END);
	?>

	<?php $this->endBody() ?>
	</body>
	</html>
<?php $this->endPage() ?>