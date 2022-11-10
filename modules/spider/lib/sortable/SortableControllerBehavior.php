<?php

namespace modules\spider\lib\sortable;

use Yii;
use yii\base\Behavior;
use yii\helpers\ArrayHelper;
use yii\web\Response;

/**
 * Class SortableControllerBehavior
 *
 * @package modules\spider\lib\sortable
 */
class SortableControllerBehavior extends Behavior{

	/**
	 * @var \common\models\BaseActiveRecord $model
	 */
	public $model;

	/**
	 * @return bool
	 */
	public function sortNode(){
		Yii::$app->response->format = Response::FORMAT_JSON;
		$sort_ids                   = Yii::$app->request->post('cid', []);

		$sorting = $this->model::find()->andWhere(['id' => $sort_ids])
		                       ->orderBy(['ordering' => SORT_ASC])
		                       ->indexBy('id')
		                       ->all();

		$old_sorting = ArrayHelper::getColumn($sorting, 'ordering', FALSE);

		foreach ($sort_ids as $key => $id){
			$sorting[$id]->detachBehaviors();
			$sorting[$id]->ordering = $old_sorting[$key];
			$sorting[$id]->save(FALSE);
		}

		return TRUE;
	}
}