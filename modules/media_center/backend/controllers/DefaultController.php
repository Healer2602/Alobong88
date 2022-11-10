<?php

namespace modules\media_center\backend\controllers;

use backend\base\Controller;
use modules\media_center\backend\models\Import;
use modules\media_center\base\ImportHelper;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Class DefaultController
 *
 * @package modules\import\backend\controllers
 */
class DefaultController extends Controller{

	/**
	 * {@inheritdoc}
	 */
	public function behaviors(){
		$behaviors = [
			'verbs'  => [
				'actions' => [
					'download' => ['post'],
				],
			],
			'access' => [
				'rules' => [
					[
						'actions'     => ['index', 'download'],
						'allow'       => TRUE,
						'permissions' => ['media_center view']
					],
					[
						'actions'     => ['download-error-log'],
						'allow'       => TRUE,
						'permissions' => ['media_center download_error_log']
					],
				],
			]
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}


	/**
	 * @return string
	 * @throws \Throwable
	 */
	public function actionIndex(){
		$filtering = $this->filtering();

		$import_logs = new ActiveDataProvider([
			'query' => Import::getLists($filtering),
			'sort'  => [
				'defaultOrder' => [
					'created_at' => SORT_DESC
				]
			]
		]);

		$filters = [
			'types'    => ImportHelper::list(),
			'statuses' => Import::statuses(),
		];

		return $this->render('index', [
			'import_logs' => $import_logs,
			'filtering'   => $filtering,
			'filters'     => $filters,
		]);
	}

	/**
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionDownloadErrorLog($id){
		$model = $this->findModel($id);
		if (file_exists($model->error_log)){
			return $this->response->sendFile($model->error_log, 'import_error_log.csv');
		}

		return $this->goBack();
	}

	/**
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionDownload($id){
		$model = $this->findModel($id);
		if (file_exists($model->filename)){
			return $this->response->sendFile($model->filename);
		}

		$this->flash('error', 'Import file is not existing');

		return $this->redirect(['index']);
	}

	/**
	 * @param $id
	 *
	 * @return null|Import
	 * @throws \yii\web\NotFoundHttpException
	 */
	protected function findModel($id){
		if (($model = Import::findOne($id)) !== NULL){
			return $model;
		}

		throw new NotFoundHttpException('Request not found');
	}
}
