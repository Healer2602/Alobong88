<?php

namespace modules\post\widgets;

use modules\BaseWidget;
use modules\post\models\Banner;
use Yii;
use yii\caching\TagDependency;

/**
 * Class BannerSlider
 *
 * @package modules\post\widgets
 */
class BannerSlider extends BaseWidget{

	public $position;

	/**
	 * @return string
	 * @throws \Exception
	 */
	public function run(){
		parent::run();

		if (empty($this->position)){
			return '';
		}

		$data = Yii::$app->db->cache(function (){
			return Banner::find()
			             ->translate()
			             ->andWhere(['position' => $this->position])
			             ->distinct()
			             ->default()
			             ->orderBy(['ordering' => SORT_ASC])
			             ->all();
		}, 0, new TagDependency([
			'tags' => ['banner_' . $this->position, Yii::$app->language]
		]));

		if (empty($data)){
			return '';
		}

		return $this->render('banner-slider', [
			'data' => $data
		]);
	}
}