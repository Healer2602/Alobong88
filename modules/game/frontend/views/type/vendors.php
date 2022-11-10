<?php
/**
 * @var frontend\base\View $this
 * @var \modules\game\models\GameType $model
 * @var \modules\game\models\VendorContent[] $vendors
 */

use modules\post\widgets\BannerSlider;

?>

<?= BannerSlider::widget(['position' => "game_type_{$model->id}"]) ?>

<div class="default-bg">
	<div class="container">
		<?php if ($vendors): ?>

			<?= $this->render("@modules/game/layouts/by_vendor", ['vendors' => $vendors]) ?>

		<?php else: ?>
			<p class="lead text-muted"><?= Yii::t('common', 'No record found.') ?></p>
		<?php endif ?>
	</div>
</div>