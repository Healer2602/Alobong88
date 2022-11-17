<?php
/**
 * @var yii\web\View $this
 * @var \modules\game\models\Vendor[] $data
 */

use modules\themes\captain\AppAsset;
use yii\bootstrap5\Html;

$asset = AppAsset::register($this);
?>

<section class="py-5 bg-grey">
	<div class="container">
		<h3 class="heading-lg text-uppercase text-secondary">
			<?= Yii::t('game', 'Partners') ?>
		</h3>
		<ul class="list-unstyled list-partner">
			<?php foreach ($data as $item){
				echo Html::beginTag('li');
				echo Html::a(Html::img($item->icon, ['alt' => '']), 'javascript:');
				echo Html::endTag('li');
			} ?>
		</ul>
	</div>
</section>