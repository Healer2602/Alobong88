<?php
/**
 * @var yii\web\View $this
 * @var \modules\game\models\VendorContent[] $vendors
 */

use yii\helpers\Html;
use yii\helpers\Url;

?>

<div class="full-width py-4">
	<div class="play-vendor-game row g-3 row-cols-lg-3 row-cols-md-6 row-cols-1 justify-content-center align-items-end">
		<?php if (!empty($vendors)): foreach ($vendors as $vendor): ?>
			<div class="col">
				<div class="image text-center">
					<a class="d-block" href="<?= Url::to($vendor->url) ?>">
						<?= Html::img($vendor->icon,
							['alt' => $vendor->name, 'class' => 'img-fluid']) ?>
					</a>
				</div>
			</div>
		<?php endforeach; endif; ?>
	</div>
</div>
