<?php

namespace modules\game\widgets;

use common\base\Status;
use modules\BaseWidget;
use modules\game\models\GameType;
use yii\db\ActiveQuery;

/**
 * Class Games
 *
 * @package modules\game\widgets
 */
class Games extends BaseWidget{

	/**
	 * @return string
	 */
	public function run(){
		parent::run();

		$data = GameType::find()
		                ->joinWith(['games game' => function (ActiveQuery $query){
			                $query->andOnCondition(['game.status' => Status::STATUS_ACTIVE, 'game.feature' => TRUE])
			                      ->with(['detailZh', 'detailVi'])
			                      ->addOrderBy(['game.ordering' => SORT_ASC]);
		                }])
		                ->with(['vendorContents' => function (ActiveQuery $query){
			                $query->andWhere(['status' => Status::STATUS_ACTIVE]);
			                $query->inverseOf('type')
			                      ->with('vendor');
		                }])
		                ->andWhere(['type.status' => Status::STATUS_ACTIVE])
		                ->addOrderBy(['type.ordering' => SORT_ASC])
		                ->groupBy(['type.id'])
		                ->all();

		return $this->render('games', [
			'data' => $data
		]);
	}
}
