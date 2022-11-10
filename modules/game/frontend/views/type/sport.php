<?php
/**
 * @var frontend\base\View $this
 * @var \modules\game\models\GameType $model
 * @var string $game
 */

use modules\post\widgets\BannerSlider;
use yii\web\View;

?>

<?= BannerSlider::widget(['position' => "game_type_{$model->id}"]) ?>

	<div class="sport-frame">
		<iframe id="sport_iframe" class="w-100" src="<?= $game ?>" title="Play Game"></iframe>
	</div>

<?php
$js = <<<JS
	// Selecting the iframe element
	var frame = document.getElementById("sport_iframe");
	
	// Adjusting the iframe height onload event
	// function execute while load the iframe
	frame.onload = function(){
		// set the height of the iframe as the height of the iframe content
		frame.style.height = frame.contentWindow.document.body.scrollHeight + 'px';
		// set the width of the iframe as the width of the iframe content
		frame.style.width  =  frame.contentWindow.document.body.scrollWidth+'px';
	}
JS;

$this->registerJs($js, View::POS_END);