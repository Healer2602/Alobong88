<?php
/**
 * @var yii\web\View $this
 */

use frontend\widgets\Homepage;
use modules\game\widgets\Partners;
use modules\post\widgets\BannerSlider;

$this->title = Yii::t('common', 'Homepage');

echo BannerSlider::widget(['position' => 'homepage']);
?>


<div class="default-bg">
	<div class="container-fluid">
		<?= Homepage::widget() ?>
	</div>
	<?= Partners::widget() ?>
</div>
