<?php
/**
 * @var \frontend\base\View $this
 */

use modules\post\widgets\BannerSlider;
use modules\themes\captain\AppAsset;

$asset = AppAsset::register($this);
?>

<?= BannerSlider::widget(['position' => "login_register"]) ?>