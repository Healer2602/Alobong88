<?php

namespace modules\game\widgets;

use common\base\Status;
use modules\BaseWidget;
use modules\game\models\GameType;
use modules\game\models\Vendor;
use yii\db\ActiveQuery;

/**
 * Class GamesVendor
 *
 * @package modules\game\widgets
 */
class GamesVendor extends BaseWidget{

	public $type = NULL;
	public $limit = 10;

	/**
	 * @return string
	 */
	public function run(){
		parent::run();

		if ($data = $this->findVendors()){
			return $this->render('games-vendor', [
				'data'  => $data,
				'model' => $this->findType()
			]);
		}

		return '';
	}

	private $_type = NULL;

	/**
	 * @return array|\modules\game\models\GameType|\yii\db\ActiveRecord
	 */
	protected function findType(){
		if ($this->_type === NULL){
			$this->_type = GameType::find()
			                       ->default()
			                       ->andWhere(['id' => $this->type])
			                       ->one() ?? [];
		}

		return $this->_type;
	}

	/**
	 * @return Vendor[]
	 */
	protected function findVendors()
	: array{

		$query = Vendor::find()
		               ->distinct()
		               ->andWhere(['vendor.status' => Status::STATUS_ACTIVE])
		               ->andWhere(['NOT', ['vendor.icon' => [NULL, '']]])
		               ->addOrderBy(['ordering' => SORT_ASC]);

		if (!empty($this->type)){
			$query->joinWith(['games.type type' => function (ActiveQuery $query){
				$query->andOnCondition(['type.id' => $this->type, 'type.status' => Status::STATUS_ACTIVE]);
			}], FALSE)
			      ->andWhere(['game.status' => Status::STATUS_ACTIVE])
			      ->andWhere(['game.type_id' => $this->type])
			      ->groupBy('vendor.id')
			      ->andHaving(['>', 'COUNT(game.id)', 0]);
		}

		if (!empty($this->limit) && $this->limit > 0){
			$query->limit($this->limit);
		}

		return $query->all();
	}
}