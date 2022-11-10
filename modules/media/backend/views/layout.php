<?php

/**
 * @var \yii\web\View $this
 * @var string $content
 */

use yii\helpers\Html;
use yii\helpers\Url;

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
	<meta charset="<?= Yii::$app->charset ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="msapplication-TileColor" content="#14215a">
	<meta name="theme-color" content="#14215a">
	<?= Html::csrfMetaTags() ?>
	<title><?= Html::encode($this->title) ?></title>
	<?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<div class="media-manager">
	<?= $content ?>
</div>
<?php $this->registerJsVar('baseUrl', Url::to(['/'])) ?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
