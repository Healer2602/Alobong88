<?php

namespace modules\notification\backend\controllers;

use backend\base\Controller;
use common\base\StatusControllerBehavior;
use modules\notification\Mailer;
use modules\notification\models\EmailSetting;
use modules\notification\models\EmailTemplate;
use modules\notification\models\Trigger;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * Class DefaultController
 *
 * @package modules\notification\backend\controllers
 */
class EmailController extends Controller{

	/**
	 * @return array
	 */
	public function behaviors(){
		$behaviors = [
			[
				'class' => StatusControllerBehavior::class,
				'model' => EmailTemplate::class
			],
			'access' => [
				'rules' => [
					[
						'allow'       => TRUE,
						'actions'     => ['index'],
						'permissions' => ['notification email_template'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['active', 'create', 'update'],
						'permissions' => ['notification email_template upsert'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['delete'],
						'permissions' => ['notification email_template delete'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['index'],
						'permissions' => ['notification email_template'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['setting'],
						'permissions' => ['setting email'],
					],
				]
			]
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}

	/**
	 * @return string|\yii\web\Response
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	public function actionSetting(){
		$model = new EmailSetting();
		$model->getValues();

		if ($model->load($this->request->post()) && $model->save()){
			$this->flash('success', 'The email setting updated successfully');

			if (!empty($model->email_tester)){
				Mailer::send($model->email_tester, 'Test email from ' . Yii::$app->name,
					'Test email from ' . Yii::$app->name);
			}

			return $this->refresh();
		}

		return $this->render('setting', [
			'model' => $model
		]);
	}

	/**
	 * @return string
	 */
	public function actionIndex(){
		$filtering = $this->request->get();
		$filters   = [
			'triggers' => Trigger::findList()
		];

		$query = EmailTemplate::find()->with('appTrigger');
		$query->andFilterWhere(['trigger_key' => $filtering['trigger'] ?? NULL]);
		$query->andFilterWhere(['OR', ['LIKE', 'name', $filtering['s'] ?? NULL], ['LIKE', 'content', $filtering['s'] ?? NULL]]);

		$email_templates = new ActiveDataProvider([
			'query' => $query,
			'sort'  => [
				'defaultOrder' => ['language' => SORT_ASC]
			]
		]);

		return $this->render('index', [
			'email_templates' => $email_templates,
			'filters'         => $filters,
			'filtering'       => $filtering
		]);
	}

	/**
	 * @param $id
	 * @param $key
	 *
	 * @return string|\yii\web\Response
	 */
	public function actionCreate($id = 0, $key = ''){
		$model = $this->findModel($id, $key);
		if (empty($model->trigger_key) && !empty($model->triggers)){
			$model->trigger_key = key($model->triggers);
		}

		if ($post = $this->request->post()){
			if ($model->load($post) && $model->save()){
				$this->flash('success', 'Email template successfully saved.');

				return $this->redirect(['email/create', 'id' => $model->id]);
			}

			if ($errors = $model->errors){
				foreach ($errors as $error){
					$this->flash('error', $error);
				}
			}
		}

		return $this->render('_form', [
			'model' => $model,
		]);
	}

	/**
	 * @param $id
	 *
	 * @return \yii\web\Response
	 */
	public function actionActive($id){
		$model = $this->findModel($id);
		$this->changeStatus($id, $model->status);

		return $this->redirect(['index']);
	}

	/**
	 * @param $id
	 *
	 * @return \yii\web\Response
	 * @throws \yii\db\StaleObjectException
	 */
	public function actionDelete($id){
		$model = $this->findModel($id);
		if (!$model->isNewRecord){
			$model->delete();
		}

		return $this->redirect(['index']);
	}

	/**
	 * @param $id
	 * @param $key
	 *
	 * @return null|EmailTemplate
	 */
	protected function findModel($id, $key = ''){
		if (($model = EmailTemplate::findOne($id)) !== NULL){
			if (!empty($key)){
				$model->trigger_key = $key;
			}

			return $model;
		}

		return new EmailTemplate([
			'trigger_key' => $key
		]);
	}
}