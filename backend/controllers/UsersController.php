<?php

namespace backend\controllers;

use backend\base\Controller;
use backend\models\Staff;
use backend\models\UserForm;
use backend\models\UserGroup;
use common\base\Status;
use common\base\StatusControllerBehavior;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Class UsersController
 *
 * @package backend\controllers
 */
class UsersController extends Controller{

	/**
	 * @return array
	 */
	public function behaviors(){
		$behaviors = [
			[
				'class' => StatusControllerBehavior::class,
				'model' => Staff::class
			],
			'access' => [
				'rules' => [
					[
						'allow'       => TRUE,
						'actions'     => ['index'],
						'permissions' => ['user'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['active', 'create', 'update', 'validate'],
						'permissions' => ['user upsert'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['delete'],
						'permissions' => ['user delete'],
					]
				]
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

		$query = Staff::find()
		              ->alias('user')
		              ->joinWith('groups user_group')
		              ->distinct();

		if (isset($filtering['state']) && $filtering['state'] != - 1){
			$query->andFilterWhere(['user.status' => $filtering['state'] ?? NULL]);
		}

		$query->andFilterWhere(['user_group.id' => $filtering['role'] ?? NULL]);
		if (!empty($filtering['s'])){
			$query->andFilterWhere(['OR', ['LIKE', 'user.name', $filtering['s']], ['LIKE', 'email', $filtering['s']], ['LIKE', 'username', $filtering['s']]]);
		}

		$users = new ActiveDataProvider([
			'query' => $query,
			'sort'  => [
				'defaultOrder' => [
					'name' => SORT_ASC
				]
			]
		]);

		$filters = [
			'roles'  => UserGroup::findList(),
			'states' => Status::states()
		];

		return $this->render('index', [
			'users'     => $users,
			'filters'   => $filters,
			'filtering' => $filtering
		]);
	}

	/**
	 * @return string|\yii\web\Response
	 * @throws \yii\base\Exception
	 */
	public function actionCreate(){
		$model = new UserForm([
			'scenario' => UserForm::SCENARIO_CREATE
		]);

		if ($model->load($this->request->post()) && $model->signup()){
			$this->flash('success', 'Staff successfully created.');

			return $this->redirect(['update', 'id' => $model->id]);
		}

		return $this->render('create', [
			'model' => $model,
		]);
	}

	/**
	 * @param $id
	 *
	 * @return string|\yii\web\Response
	 * @throws \yii\base\Exception
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionUpdate($id){
		$model      = $this->findModel($id);
		$user_model = new UserForm([
			'scenario' => UserForm::SCENARIO_UPDATE
		]);

		$user_model->setAttributes($model->getAttributes(), FALSE);
		$user_model->user_group_id = ArrayHelper::getColumn($model->groups, 'id');

		if ($user_model->load($this->request->post()) && $user_model->update()){
			$this->flash('success', 'Staff successfully updated.');

			return $this->redirect(['index']);
		}

		return $this->render('update', [
			'model' => $user_model,
		]);
	}

	/**
	 * @param $id
	 *
	 * @return mixed
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionValidate($id = 0){
		if (!empty($id)){
			$model                = $this->findModel($id);
			$user_model           = new UserForm();
			$user_model->scenario = UserForm::SCENARIO_UPDATE;
			$user_model->setAttributes($model->getAttributes(), FALSE);
		}else{
			$user_model           = new UserForm();
			$user_model->scenario = UserForm::SCENARIO_CREATE;
		}

		if ($this->request->isAjax && $user_model->load($this->request->post())){
			Yii::$app->response->format = Response::FORMAT_JSON;

			return ActiveForm::validate($user_model);
		}

		return [];
	}

	/**
	 * @param $id
	 *
	 * @return \yii\web\Response
	 * @throws \yii\web\NotFoundHttpException
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
	 * @throws \Throwable
	 * @throws \yii\db\StaleObjectException
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionDelete($id){
		$model = $this->findModel($id);
		$model->delete();
		$this->flash('success', 'Staff deleted successfully');

		return $this->redirect(['index']);
	}

	/**
	 * @param $id
	 *
	 * @return null|Staff
	 * @throws \yii\web\NotFoundHttpException
	 */
	protected function findModel($id){
		if (($model = Staff::findOne($id)) !== NULL){
			return $model;
		}

		throw new NotFoundHttpException('Request not found');
	}
}