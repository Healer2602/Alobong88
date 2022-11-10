<?php

namespace modules\media\backend\controllers;

require_once __DIR__ . '/../../ckfinder/core/autoload.php';

use backend\base\Controller;
use CKSource\CKFinder\CKFinder;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * Class MediaController
 *
 * @package backend\controllers
 */
class ManagerController extends Controller{

	public $layout = '@modules/media/backend/views/layout.php';

	public $config = [];

	/**
	 * @return array
	 */
	public function behaviors(){
		$permissions = $this->module->permission;
		if (!ArrayHelper::isAssociative($permissions)){
			$permissions = [$permissions];
		}

		$behaviors = [
			'access' => [
				'rules' => [
					[
						'allow'       => TRUE,
						'permissions' => $permissions,
					],
				]
			]
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}


	/**
	 * @param $action
	 *
	 * @return bool
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function beforeAction($action){
		$this->config = $this->module->params['config'] ?? [];

		if ($action->id !== 'index'){
			$this->enableCsrfValidation = FALSE;
		}

		return parent::beforeAction($action);
	}

	/**
	 * @inheritDoc
	 */
	public function actionConnector(){
		$options = require __DIR__ . '/../../ckfinder/config.php';

		$access_control = [
			'FOLDER_RENAME' => Yii::$app->user->can('media rename_folder'),
			'FOLDER_DELETE' => Yii::$app->user->can('media delete_folder'),

			'FILE_RENAME' => Yii::$app->user->can('media rename_file'),
			'FILE_DELETE' => Yii::$app->user->can('media delete_file'),
		];

		$options['accessControl'][0] = ArrayHelper::merge($options['accessControl'][0],
			$access_control);

		$ckfinder = new CKFinder($options);

		$ckfinder->run();
		exit();
	}

	/**
	 * @return string
	 * @throws \yii\base\Exception
	 */
	public function actionIndex(){
		$request = $this->request->get();
		$fldr    = $request['fldr'] ?? NULL;
		if (!empty($fldr)){
			$type      = $request['type'] ?? 'files';
			$fldr_path = strtolower($type) . '/' . $fldr;
			$path      = Yii::getAlias("@files/{$fldr_path}");
			FileHelper::createDirectory($path);
			$startup = ucfirst($type) . ':/' . $fldr . '/';
		}

		return $this->render('index', [
			'field_id' => $request['field_id'] ?? '_media__',
			'editor'   => $request['editor'] ?? '',
			'request'  => $request,
			'startup'  => $startup ?? ''
		]);
	}

	/**
	 * @return string
	 */
	public function actionDialog(){
		$request = $this->request->get();

		return $this->renderAjax('dialog', [
			'request' => $request
		]);
	}
}