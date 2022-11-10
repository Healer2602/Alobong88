<?php

namespace backend\controllers;

use backend\base\Controller;
use backend\models\UserGroup;
use backend\models\UserGroupPermission;
use backend\models\UserPermission;
use common\base\Status;
use common\base\StatusControllerBehavior;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Class RoleController
 *
 * @package backend\controllers
 */
class RoleController extends Controller{

	/**
	 * @return array
	 */
	public function behaviors(){

		$behaviors = [
			[
				'class' => StatusControllerBehavior::class,
				'model' => UserGroup::class
			],
			'access' => [
				'rules' => [
					[
						'allow'       => TRUE,
						'actions'     => ['index'],
						'permissions' => ['role'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['create', 'update', 'active'],
						'permissions' => ['role upsert'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['delete'],
						'permissions' => ['role delete'],
					],
					[
						'allow'       => TRUE,
						'actions'     => ['access-control'],
						'permissions' => ['role access_control'],
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
		$filtering = $this->request->get();
		if (!isset($filtering['state'])){
			$filtering['state'] = Status::STATUS_ACTIVE;
		}

		$query = UserGroup::find()->with('users');

		if ($filtering['state'] != - 1){
			$query->andFilterWhere(['status' => $filtering['state'] ?? NULL]);
		}

		if (!empty($filtering['s'])){
			$query->andFilterWhere(['LIKE', 'name', $filtering['s']]);
		}

		$groups = new ActiveDataProvider([
			'query'      => $query,
			'pagination' => [
				'pageSizeLimit' => [1, 100]
			]
		]);

		$filters = [
			'states' => Status::states()
		];

		return $this->render('index', [
			'groups'    => $groups,
			'filtering' => $filtering,
			'filters'   => $filters
		]);
	}

	/**
	 * @return string|\yii\web\Response
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	public function actionCreate(){
		$model = new UserGroup();

		if ($post = $this->request->post()){
			$permissions = $post['Permissions'] ?? [];

			if ($model->load($post) && $model->save()){
				$this->updatePermission($model->id, $permissions);

				$this->flash('success', 'Role successfully created.');

			}elseif ($errors = $model->errors){
				foreach ($errors as $error){
					$this->flash('error', $error);
				}
			}

			return $this->redirect(['index']);
		}

		$permissions = new ArrayDataProvider([
			'models'     => UserPermission::find()->asArray()->all(),
			'pagination' => ['pageSize' => - 1]
		]);

		return $this->render('create', [
			'model'       => $model,
			'permissions' => $permissions
		]);
	}

	/**
	 * @param $id
	 *
	 * @return string|\yii\web\Response
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionUpdate($id){
		$model = $this->findModel($id);

		if ($post = $this->request->post()){
			$permissions = [];
			if (!$model->is_primary){
				$permissions = $post['Permissions'] ?? [];
			}else{
				unset($post['UserGroup']['status']);
				unset($post['UserGroup']['modules']);
			}

			if ($model->load($post) && $model->save()){
				$this->updatePermission($model->id, $permissions);

				$this->flash('success', 'Role successfully updated.');

			}elseif ($errors = $model->errors){
				foreach ($errors as $error){
					$this->flash('error', $error);
				}
			}

			return $this->redirect(['index']);
		}

		$permissions = new ArrayDataProvider([
			'models'     => UserPermission::find()->orderBy(['name' => SORT_ASC])->asArray()->all(),
			'pagination' => ['pageSize' => - 1]
		]);

		return $this->render('update', [
			'model'       => $model,
			'permissions' => $permissions
		]);
	}

	/**
	 * @param $id
	 *
	 * @return \yii\web\Response
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionActive($id){
		$model           = $this->findModel($id);
		$model->scenario = UserGroup::SCENARIO_STATUS;
		if (!$model->is_primary){
			$model->status = ($model->status == Status::STATUS_ACTIVE) ? Status::STATUS_INACTIVE : Status::STATUS_ACTIVE;
			$model->save(FALSE);
			$this->flash('success', 'Status successfully changed');
		}

		return $this->redirect(['index']);
	}

	/**
	 * @param $id
	 *
	 * @return \yii\web\Response
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionDelete($id){
		$model = $this->findModel($id);
		if (!$model->is_primary && !$model->isRelated){
			$this->softDelete($model->id);
		}

		return $this->redirect(['index']);
	}

	/**
	 * @param $id
	 *
	 * @return null|UserGroup
	 * @throws \yii\web\NotFoundHttpException
	 */
	protected function findModel($id){
		if ($model = UserGroup::findOne($id)){
			return $model;
		}

		throw new NotFoundHttpException('The requested page does not exist.');
	}

	/**
	 * @param $id
	 * @param $permissions
	 *
	 * @return bool
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	protected function updatePermission($id, $permissions){
		$permission_data = [];

		foreach ($permissions as $permission){
			$permission_data[] = new UserGroupPermission([
				'user_group_id'      => $id,
				'user_permission_id' => intval($permission)
			]);
		}

		if (!empty($user_groups)){
			Yii::$app->authManager->clearCache([$id]);
		}

		if (!empty($permission_data)){
			return UserGroupPermission::upsert($permission_data);
		}

		return FALSE;
	}

	/**
	 * @return string|\yii\web\Response
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\db\Exception
	 */
	public function actionAccessControl(){
		if ($post = $this->request->post('Permission')){
			$permission_data = [];
			$user_groups     = [];

			foreach ($post as $user_group_id => $permissions){
				if (!is_array($permissions)){
					UserGroupPermission::deleteAll(['user_group_id' => $user_group_id]);
					$user_groups[] = $user_group_id;
				}else{
					foreach ($permissions as $permission){
						$permission_data[] = new UserGroupPermission([
							'user_group_id'      => $user_group_id,
							'user_permission_id' => $permission
						]);
					}
				}
			}

			if (!empty($user_groups)){
				Yii::$app->authManager->clearCache($user_groups);
			}

			if (!empty($permission_data)){
				$upsert = UserGroupPermission::upsert($permission_data);
			}

			if (!empty($user_groups) || !empty($upsert)){
				$this->flash('success', 'Access Control successfully updated');
			}

			return $this->refresh();
		}

		$user_groups = UserGroup::find()
		                        ->with('permissions')
		                        ->orderBy(['name' => SORT_ASC])
		                        ->asArray()
		                        ->all();

		$permissions = new ArrayDataProvider([
			'allModels'  => UserPermission::find()->orderBy(['name' => SORT_ASC])->asArray()->all(),
			'pagination' => ['pageSize' => - 1]
		]);

		return $this->render('access-control', [
			'permissions' => $permissions,
			'user_groups' => $user_groups
		]);
	}
}