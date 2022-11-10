<?php

/**
 * @var \yii\web\View $this
 * @var string $content
 */

use common\widgets\Alert;
use modules\themes\spiderboard\AppAsset;
use yii\helpers\Html;

$asset = AppAsset::register($this);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
	<meta charset="<?= Yii::$app->charset ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="msapplication-TileColor" content="#B5121B">
	<meta name="theme-color" content="#B5121B">
	<?= Html::csrfMetaTags() ?>
	<title><?= Html::encode($this->title) ?></title>
	<?php $this->head() ?>
</head>
<body class="d-flex align-items-center bg-auth border-top border-top-2 border-primary">
<?php $this->beginBody() ?>
<div class="container">
	<div class="row justify-content-center">
		<div class="col-12 col-md-5 col-xl-4 my-5">
			<?= Alert::widget(['options' => ['class' => 'mt-4']]) ?>
			<?= $content ?>
		</div>
	</div>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
