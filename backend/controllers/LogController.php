<?php

namespace backend\controllers;

use backend\base\Controller;
use backend\models\AuditTrailSearchModel;
use common\base\Spreadsheet;
use common\models\AuditTrail;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * Class LogController
 *
 * @package backend\controllers
 */
class LogController extends Controller{

	/**
	 * @return array
	 */
	public function behaviors(){

		$behaviors = [
			'access' => [
				'rules' => [
					[
						'allow'       => TRUE,
						'actions'     => ['index'],
						'permissions' => ['system logs'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['export'],
						'permissions' => ['system logs export'],
					],
				]
			]
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}

	/**
	 * @return string
	 */
	public function actionIndex(){
		$filtering    = $this->filtering();
		$search_model = AuditTrailSearchModel::findModels($filtering);

		$query = new ActiveDataProvider([
			'query' => $search_model,
			'sort'  => [
				'defaultOrder' => [
					'id' => SORT_DESC
				]
			]
		]);

		return $this->render('index', [
			'audit_trails' => $query,
			'filtering'    => $filtering,
			'search_model' => new AuditTrailSearchModel()
		]);
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	public function actionExport(){
		$query = AuditTrailSearchModel::findModels($this->filtering())
		                              ->orderBy(['id' => SORT_DESC]);

		$attributes = [
			'system'     => function ($model){
				return AuditTrail::getSystems()[$model->system] ?? NULL;
			},
			'module',
			'action',
			'message',
			'user_id'    => function ($model){
				return $model->author->name ?? $model->user_name ?? 'SYSTEM';
			},
			'ip_address',
			'created_at' => function ($model){
				return Yii::$app->formatter->asDatetime($model->created_at);
			},
		];

		$file_name  = 'AUDIT_TRAILS_EXPORT_' . time() . '.xlsx';
		$sheet_name = 'Audit Trails';

		return Spreadsheet::widget([
			'file_name'  => $file_name,
			'data'       => $query,
			'attributes' => $attributes,
			'title'      => $sheet_name,
			'serial'     => TRUE
		]);
	}
}
