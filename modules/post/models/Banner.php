<?php

namespace modules\post\models;

use modules\game\models\GameType;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class Banner
 *
 * @package modules\post\models
 *
 * @inheritDoc
 *
 * @property array $targets
 * @property array $positions
 * @property string $positionName
 */
class Banner extends Post{

	public static $post_type = 'banner';

	public static $alias = 'banner';

	/**
	 * @return array
	 */
	public function rules(){
		$rules   = parent::rules();
		$rules[] = ['thumbnail', 'required'];

		return $rules;
	}

	/**
	 * @return array
	 */
	public function attributeLabels(){
		$labels = [
			'thumbnail' => Yii::t('post', 'Banner Image Desktop')
		];

		return ArrayHelper::merge(parent::attributeLabels(), $labels);
	}

	/**
	 * @param bool $insert
	 *
	 * @return bool
	 */
	public function beforeSave($insert){
		if (is_array($this->content)){
			$this->content = Json::encode($this->content);
		}

		return parent::beforeSave($insert);
	}

	/**
	 * @param bool $insert
	 * @param array $changedAttributes
	 */
	public function afterSave($insert, $changedAttributes){
		if ($insert){
			static::updateAllCounters(['ordering' => 1], "id <> :id and type = :type",
				[':id' => $this->id, ':type' => static::$post_type]);
		}

		parent::afterSave($insert, $changedAttributes);
	}

	/**
	 * @inheritDoc
	 */
	public function afterFind(){
		if (is_string($this->content)){
			$this->content = Json::decode($this->content);
		}

		parent::afterFind();
	}

	/**
	 * @return array
	 */
	public function getTargets(){
		return [
			'_parent' => Yii::t('post', 'Current Tab'),
			'_blank'  => Yii::t('post', 'New Tab'),
		];
	}

	/**
	 * @return array
	 */
	public function getPositions(){
		$layout_custom = [
			'homepage'       => 'Homepage',
			'login_register' => 'Login/Register',
			'vendors'        => 'Vendors',
		];

		$games = GameType::find()
		                 ->select(['name', 'id'])
		                 ->indexBy('id')
		                 ->orderBy(['name' => SORT_ASC])
		                 ->asArray()
		                 ->all();

		if (!empty($games)){
			$games = ArrayHelper::map($games, function ($data){
				return "game_type_{$data['id']}";
			}, function ($data){
				return "Game Type: {$data['name']}";
			});
		}

		return ArrayHelper::merge($games, $layout_custom);
	}

	/**
	 * @return mixed|string
	 */
	public function getPositionName(){
		return $this->positions[$this->position] ?? $this->position;
	}
}