<?php

namespace modules\game\widgets;

use common\base\Status;
use modules\BaseWidget;
use modules\game\models\GameType;

/**
 * Class GamesMenu
 *
 * @package modules\game\widgets
 */
class GamesMenu extends BaseWidget{

	/**
	 * @return string
	 */
	public function run(){
		parent::run();

		$data = GameType::find()
		                ->andWhere(['status' => Status::STATUS_ACTIVE])
		                ->addOrderBy(['ordering' => SORT_ASC])
		                ->all();

		if (empty($data)){
			return '';
		}

		return $this->render('games-menu', [
			'data' => $data
		]);
	}
}
