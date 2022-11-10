<?php

namespace backend\base;

use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class Tree
 *
 * @package backend\base
 */
class Tree extends BaseObject{

	public $is_select = 0;
	public $sep = '- ';
	private $id = 'id';
	private $parent = 'parent_id';
	private $name = 'name';
	private $objects = [];
	private $cats = [];

	private $parents = [];

	/**
	 * Tree constructor.
	 *
	 * @param $objects
	 * @param array $params
	 */
	public function __construct($objects, $params = []){
		$this->__setParams($params);
		if (!empty($this->parents)){
			$this->parents = [$this->parents];
		}
		$this->_createArrayIndexParent($objects);
	}

	/**
	 * @return array
	 */
	public function getList(){
		$this->recurseTreeForeach();

		return $this->cats;
	}

	/**
	 * @param int $id
	 * @param int $level
	 */
	private function recurseTreeForeach($id = 0, $level = 0){
		if ($id === 0){
			$this->_clearCats();
		}
		if (isset($this->objects[$id])){
			foreach ($this->objects[$id] as $key => $value){
				if (is_array($value)){
					$value = Json::decode(Json::encode($value), FALSE);
				}

				if (empty($this->parents)){
					$this->_recurseTreeAdd($value, $level);
				}else{
					if (ArrayHelper::isIn($id,
							$this->parents) || ArrayHelper::isIn($value->{$this->parent},
							$this->parents)){
						$this->parents[] = $value->{$this->id};
						$this->_recurseTreeAdd($value, $level);
					}else{
						$this->recurseTreeForeach($value->{$this->id}, ++ $level);
					}
				}
			}
		}
	}

	/**
	 * @param $value
	 * @param $level
	 */
	private function _recurseTreeAdd($value, $level){
		$value->{'base_' . $this->name} = $value->{$this->name};

		$value->{$this->name} = ($this->_getSep($level) . $value->{$this->name});
		if ($this->is_select){
			$this->cats[] = '<option value="' . $value->{$this->id} . '" name="' . $value->{$this->name} . '"></option>';
		}else{
			$value->level = $level;
			$this->cats[] = $value;
		}
		$this->recurseTreeForeach($value->{$this->id}, ++ $level);
		$level --;
	}

	/**
	 * @param $level
	 *
	 * @return string
	 */
	private function _getSep($level){
		$sep = '';
		for ($i = 0; $i < $level; $i ++){
			$sep .= $this->sep;
		}

		return $sep;
	}

	/**
	 *
	 */
	private function _clearCats(){
		$this->cats = [];
	}

	/**
	 * @param $objects
	 */
	private function _createArrayIndexParent($objects){
		if (is_array($objects)){
			foreach ($objects as $key => $value){
				if (is_array($value)){
					$value = Json::decode(Json::encode($value), FALSE);
				}

				$this->objects[$value->{$this->parent}][$value->{$this->id}] = $value;
			}
		}
	}

	/**
	 * @param $params
	 */
	private function __setParams($params){
		foreach ($params as $k => $p){
			$this->_setParam($k, $p);
		}
	}

	/**
	 * @param $k
	 * @param $value
	 */
	private function _setParam($k, $value){
		if (isset($this->{$k})){
			$this->{$k} = $value;
		}
	}

}
