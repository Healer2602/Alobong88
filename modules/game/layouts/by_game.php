<?php
/**
 * @var yii\web\View $this
 * @var \modules\game\models\Game[] $games
 */

use modules\game\widgets\PlayButton;
use yii\bootstrap5\Html;

?>

<div class="listing full-width">
	<div class="row row-cols-lg-5 row-cols-2">
		<?php if (!empty($games)): foreach ($games as $game): ?>
			<div class="col mb-4">
				<div class="box-game">
					<div class="image">
						<?= Html::img($game->iconUrl, ['alt' => '']) ?>
						<?= PlayButton::widget(['game' => $game]) ?>
					</div>
					<?= Html::tag('h4', Html::encode($game->nameLabel),
						['class' => 'title']) ?>
				</div>
			</div>
		<?php endforeach; endif; ?>
	</div>
</div>
