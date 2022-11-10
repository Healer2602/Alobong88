<?php
/**
 * @var yii\web\View $this
 * @var \modules\game\models\Vendor[] $data
 */

use modules\post\widgets\BannerSlider;
use yii\bootstrap5\Html;

echo BannerSlider::widget(['position' => 'vendors']);
?>
<div class="default-bg">
	<div class="container">
		<ul class="list-unstyled list-partner">
			<?php foreach ($data as $item){
				echo Html::beginTag('li');
				echo Html::a(Html::img($item->icon, ['alt' => '']), $item->url);
				echo Html::endTag('li');
			} ?>
		</ul>
	</div>
</div>