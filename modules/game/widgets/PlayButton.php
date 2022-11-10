<?php

namespace modules\game\widgets;

use Yii;
use yii\bootstrap5\Html;
use yii\bootstrap5\Widget;

/**
 * Play Button
 */
class PlayButton extends Widget{

	/**
	 * @var \modules\game\models\Game
	 */
	public $game;

	public function run(){
		parent::run();

		$html = Html::beginTag('div', ['class' => 'group-btn']);

		$html .= Html::a(Yii::t('game', 'Play'), $this->game->url,
			['class' => 'btn btn-info btn-play']);

		if ($this->game->free_to_play){
			$html .= Html::a(Yii::t('game', 'Demo'), $this->game->tryUrl,
				['class' => 'btn btn-primary btn-play mt-2']);
		}

		return $html . Html::endTag('div');
	}

}