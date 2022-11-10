<?php
/**
 * @var yii\web\View $this
 * @var \modules\game\models\GameType $type
 */

use yii\helpers\Html;
use yii\helpers\Url;

?>

<div class="bordered-heading">
	<h2 class="heading text-largest icon">
		<?= Html::img($type->icon, ['alt' => '']) ?>
		<?= Html::encode(Yii::t('game', $type->name)) ?> </h2>

	<a href="<?= Url::to($type->url) ?>" class="btn btn-primary-darken btn-all btn-round"><?= Yii::t('common',
			'See all') ?></a>
</div>