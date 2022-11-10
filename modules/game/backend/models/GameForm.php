<?php

namespace modules\game\backend\models;

use modules\game\models\Game;
use modules\game\models\GameDetail;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%game}}".
 */
class GameForm extends Game{

	public $data_detail = [];

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		$rules = [
			[['data_detail'], 'safe'],
		];

		return ArrayHelper::merge(parent::rules(), $rules);
	}

	/**
	 * @param $insert
	 * @param $changedAttributes
	 *
	 * @return void
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	public function afterSave($insert, $changedAttributes){
		if (!empty($this->data_detail)){
			$detail = [];
			foreach ($this->data_detail as $item => $value){
				$detail[] = new GameDetail([
					'game_id'  => $this->id,
					'language' => $item,
					'name'     => $value['name'] ?? '',
					'icon'     => $value['icon'] ?? '',
				]);
			}

			GameDetail::upsert($detail);
		}

		parent::afterSave($insert, $changedAttributes);
	}

	/**
	 * @return bool
	 */
	public function beforeDelete(){
		GameDetail::deleteAll(['game_id' => $this->id]);

		return parent::beforeDelete();
	}

	/**
	 * @return void
	 */
	public function getDataDetails(){
		$details = $this->details;
		foreach ($details as $detail){
			$this->data_detail[$detail['language']] = [
				'name' => $detail['name'],
				'icon' => $detail['icon'],
			];
		}
	}
}