<?php
/**
 * @var yii\web\View $this
 * @var \modules\game\models\Vendor[] $data
 */

use modules\themes\captain\AppAsset;
use yii\bootstrap5\Html;

$asset = AppAsset::register($this);
?>

<section class="py-4">
	<div class="bordered-heading">
		<h2 class="heading text-largest icon">
			<img src="<?= $asset->baseUrl ?>/img/icons/cup-icon.png" alt="">
			<?= Yii::t('game', 'Partners') ?>
		</h2>
	</div>
	<ul class="list-unstyled list-partner">
		<?php foreach ($data as $item){
			echo Html::beginTag('li');
			echo Html::a(Html::img($item->icon, ['alt' => '']), 'javascript:');
			echo Html::endTag('li');
		} ?>
	</ul>
</section>