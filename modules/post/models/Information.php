<?php

namespace modules\post\models;

use common\base\AppHelper;
use Yii;
use yii\helpers\Url;

/**
 * Class Information
 *
 * @package modules\post\models
 *
 * @inheritDoc
 *
 * @property-read array $publicUrls
 */
class Information extends Post{

	public static $post_type = 'information';

	public static $alias = 'information';

	/**
	 * @return array
	 */
	public function getPublicUrls(){
		return [
			'url'   => AppHelper::homeUrl() . Url::to($this->url),
			'label' => Yii::t('common', 'View on website'),
		];
	}
}