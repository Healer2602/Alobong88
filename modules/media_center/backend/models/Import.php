<?php

namespace modules\media_center\backend\models;

use modules\media_center\models\ImportLog;

/**
 * Class Import
 */
class Import extends ImportLog{

	/**
	 * @param $filtering
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public static function getLists($filtering){
		$query = self::find()->with('author')->distinct();

		$query->andFilterWhere(['LIKE', 'importer', $filtering['type'] ?? NULL]);
		$query->andFilterWhere(['status' => $filtering['status'] ?? NULL]);

		return $query;
	}
}
