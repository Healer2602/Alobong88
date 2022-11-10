<?php

/**
 * @var yii\web\View $this
 * @var string $name
 * @var string $message
 * @var Exception $exception
 */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $name;
?>

<div class="text-center">
	<!-- Preheading -->
	<h6 class="text-uppercase text-muted mb-4"><?= Html::encode($name) ?></h6>

	<!-- Heading -->
	<h1 class="mb-3 h2"><?= nl2br(Html::encode($message)) ?></h1>

	<!-- Subheading -->
	<p class="text-muted mb-4">Looks like you ended up here by accident? </p>

	<!-- Button -->
	<a href="<?= Url::to(['/']) ?>" class="btn btn-lg btn-primary">
		Return to your dashboard
	</a>
</div>
