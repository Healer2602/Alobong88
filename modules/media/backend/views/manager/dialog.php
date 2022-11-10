<?php
/**
 * @var \yii\web\View $this
 * @var array $request
 */

use yii\helpers\Url; ?>

<iframe src="<?= Url::to(['index'] + $request) ?>" frameborder="0" width="100%" height="100%" style="min-height: 500px"></iframe>