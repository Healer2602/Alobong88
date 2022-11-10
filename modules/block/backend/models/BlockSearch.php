<?php

namespace modules\block\backend\models;

use common\base\Status;
use common\models\Language;
use modules\block\models\Block;
use yii\data\ActiveDataProvider;

/**
 * WidgetSearch represents the model behind the search form of `Block`.
 *
 * @inheritDoc
 *
 * @property array $statuses
 * @property array $langs
 */
class BlockSearch extends Block{

	public $keywords;

	/**
	 * {@inheritdoc}
	 */
	public function rules(){
		return [
			[['status'], 'integer'],
			[['type', 'position', 'keywords', 'language'], 'string'],
		];
	}

	/**
	 * Creates data provider instance with search query applied
	 *
	 * @param array $params
	 *
	 * @return ActiveDataProvider
	 */
	public function search($params){
		$query = Block::find()->orderBy(['ordering' => SORT_ASC]);

		// add conditions that should always apply here

		$search = new ActiveDataProvider([
			'query' => $query,
		]);

		$this->setAttributes($params);

		if (!$this->validate()){
			return $search;
		}

		// grid filtering conditions
		$query->andFilterWhere([
			'type'     => $this->type,
			'position' => $this->position,
			'language' => $this->language
		]);

		if (isset($this->status) && $this->status != - 1){
			$query->andFilterWhere(['status' => $this->status ?? NULL]);
		}

		if (!empty($this->keywords)){
			$query->andWhere(['OR', ['LIKE', 'name', $this->keywords], ['like', 'title', $this->keywords], ['like', 'content', $this->keywords]]);
		}

		return $search;
	}

	/**
	 * @return array
	 */
	public function getStatuses(){
		return Status::states();
	}

	/**
	 * @return array
	 * @throws \Throwable
	 */
	public function getLangs(){
		return Language::listLanguage();
	}
}
