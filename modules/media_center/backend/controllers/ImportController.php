<?php

namespace modules\media_center\backend\controllers;

use backend\base\Controller;
use modules\media_center\backend\models\ImportForm;
use modules\media_center\base\ImportHelper;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * Class ImportController
 *
 * @package modules\import\backend\controllers
 */
class ImportController extends Controller{

	/**
	 * {@inheritdoc}
	 */
	public function behaviors(){
		$behaviors = [
			'access' => [
				'rules' => [
					[
						'actions'     => ['index', 'download'],
						'allow'       => TRUE,
						'permissions' => ['media_center import']
					],
				],
			]
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}


	/**
	 * @return \yii\web\Response|string
	 * @throws \Throwable
	 */
	public function actionIndex(){
		$model = new ImportForm();

		if ($model->load($this->request->post())){
			$model->file = UploadedFile::getInstance($model, 'file');

			if ($model->import()){
				$this->flash('success',
					"Import file is processing. Please refer to the import log after 5 minutes.");

				return $this->redirect(['default/index']);
			}
		}

		return $this->render('index', [
			'model' => $model
		]);
	}

	/**
	 * @param string $importer
	 *
	 * @return \yii\web\Response
	 */
	public function actionDownload(string $importer)
	: Response{
		if ($template = ImportHelper::importTemplate($importer)){
			$template_path = Yii::getAlias("@{$template}");
			if (file_exists($template_path)){
				return $this->response->sendFile($template_path);
			}
		}

		return $this->redirect(['index']);
	}
}