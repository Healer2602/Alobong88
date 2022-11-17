<?php

namespace modules\website\backend\controllers;

use backend\base\Controller;
use backend\base\MenuHelper;
use common\base\Status;
use common\base\StatusControllerBehavior;
use modules\website\models\Menu;
use Yii;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * PublicHolidayController implements the CRUD actions for PublicHoliday model.
 */
class MenuController extends Controller{

	/**
	 * @inheritdoc
	 */
	public function behaviors(){
		$behaviors = [
			[
				'class' => StatusControllerBehavior::class,
				'model' => Menu::class
			],
			'access' => [
				'rules' => [
					[
						'allow'       => TRUE,
						'permissions' => ['menu'],
					],
				]
			]
		];

		return ArrayHelper::merge(parent::behaviors(), $behaviors);
	}

	/**
	 * Lists all Menu models.
	 *
	 * @return mixed
	 */
	public function actionIndex(){
		$query = Menu::find()->roots();
		$menus = new ActiveDataProvider([
			'query'      => $query,
			'pagination' => [
				'pageSizeLimit' => [1, 100]
			]
		]);

		return $this->render('index', [
			'menus' => $menus
		]);
	}

	/**
	 * Creates a new Menu model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 *
	 * @param int $id
	 *
	 * @return mixed
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionCreate($id = 0){
		$model = $this->findModel($id);

		if ($model->load(Yii::$app->request->post())){
			if ($model->isNewRecord){
				$model->makeRoot();
			}

			if ($model->save()){
				$this->flash('success', 'The menu saved successfully.');
			}elseif ($errors = $model->errors){
				foreach ($errors as $error){
					$this->flash('error', $error);
				}
			}
		}

		if (!$this->request->isAjax){
			return $this->redirect(['index']);
		}

		return $this->renderAjax('_form', [
			'model' => $model,
		]);
	}

	/**
	 * @param $id
	 *
	 * @return \yii\web\Response
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionActive($id){
		$menu = $this->findModel($id);
		if (!$menu->isNewRecord){
			$this->changeStatus($id, $menu->status);
			if (!empty($menu->position)){
				TagDependency::invalidate(Yii::$app->cache,
					Menu::cacheKey($menu->position));
			}
		}

		return $this->redirect(['index']);
	}

	/**
	 * Lists all Menu Item models.
	 *
	 * @param $id
	 *
	 * @return mixed
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionList($id){
		if ($post = $this->request->post()){
			$cids = $this->request->post('cid');
			$this->_sortNode($cids);
		}

		$menu      = $this->findModel($id);
		$filtering = $this->request->get();

		$query = $menu->children()
		              ->andFilterCompare('name', $filtering['s'] ?? NULL, 'LIKE');

		$menu_items = new ActiveDataProvider([
			'query'      => $query,
			'pagination' => [
				'pageSizeLimit' => [1, 100]
			]
		]);

		return $this->render('list', [
			'menus'     => $menu_items,
			'menu'      => $menu,
			'filtering' => $filtering
		]);
	}

	/**
	 * @param int $id
	 * @param $menu
	 *
	 * @return string|\yii\web\Response
	 * @throws NotFoundHttpException
	 */
	public function actionItem($menu, $id = 0){
		$root        = $this->findModel($menu);
		$is_new_item = FALSE;

		if (!$item = Menu::findOne(['id' => $id])){
			$item        = new Menu();
			$is_new_item = TRUE;
		}

		if ($post = $this->request->post()){
			if ($item->load(Yii::$app->request->post())){
				if (empty($item->parent_id)){
					$item->parent_id = $menu;
					$this->_insertRootNode($is_new_item, $item, $root);
				}else{
					$this->_insertChildNode($is_new_item, $item, $item->parent);
				}

				if ($item->save()){
					TagDependency::invalidate(Yii::$app->cache,
						Menu::cacheKey($root->position));

					$this->flash('success', 'The menu item saved successfully.');
				}

				if ($errors = $item->errors){
					foreach ($errors as $error){
						$this->flash('error', $error);
					}
				}
			}
		}

		if (!$this->request->isAjax){
			return $this->redirect(['list', 'id' => $menu]);
		}

		return $this->renderAjax('menu_item', [
			'model'  => $item,
			'parent' => $root
		]);
	}

	/**
	 * @param integer $id
	 *
	 * @return \yii\web\Response
	 */
	public function actionActiveItem($id){
		$menu_item = Menu::findOne($id);
		if ($menu_item){
			$menu_item->status = ($menu_item->status == Status::STATUS_ACTIVE) ? Status::STATUS_INACTIVE : Status::STATUS_ACTIVE;
			if ($menu_item->save(FALSE)){
				if ($parents = $menu_item->getParents()){
					$positions = array_filter(ArrayHelper::getColumn($parents, 'position'));
					$tags      = [];

					foreach ($positions as $position){
						$tags[] = Menu::cacheKey($position);
					}

					TagDependency::invalidate(Yii::$app->cache, $tags);
				}

				$this->flash('success', Yii::t('common', 'Status successfully changed'));
			}
		}

		return $this->redirect(Yii::$app->request->referrer);
	}

	/**
	 * Deletes an existing Menu model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 *
	 * @param integer $id
	 *
	 * @return mixed
	 * @throws \Exception
	 * @throws \Throwable
	 * @throws \yii\db\StaleObjectException
	 * @throws \yii\web\NotFoundHttpException if the model cannot be found
	 */
	public function actionDelete($id){
		$model = $this->findModel($id);
		if (!$model->isRelated()){
			if ($model->isRoot()){
				$model->deleteWithChildren();
			}else{
				$model->delete();
			}

			$this->flash('success', 'Menu deleted successfully');
		}

		return $this->redirect(Yii::$app->request->referrer);
	}

	/**
	 * Display a list of menu type
	 *
	 * @return string
	 */
	public function actionMenuTypes(){
		$types = MenuHelper::renderMenuTypeList();

		return $this->renderAjax('_menu_types', [
			'types' => $types
		]);
	}

	/**
	 * @param array $ids
	 *
	 * @throws NotFoundHttpException
	 */
	private function _sortNode($ids){
		foreach ($ids as $id){
			$node = $this->findModel($id);
			$node->appendTo($node->parent);
		}
	}

	/**
	 * Finds the Menu model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 *
	 * @param integer $id
	 *
	 * @return Menu the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id){
		if (empty($id)){
			return new Menu(['status' => Status::STATUS_INACTIVE]);
		}

		if (($model = Menu::findOne($id)) !== NULL){
			return $model;
		}

		throw new NotFoundHttpException(Yii::t('common', 'The requested page does not exist.'));
	}

	/**
	 * @param bool $is_new
	 * @param $node
	 * @param $parent
	 */
	protected function _insertRootNode($is_new = TRUE, $node, $parent){
		if (!$is_new){
			$next = $node->next()->one();
			if (!empty($next)){
				$node->insertBefore($next);
			}else{
				$node->appendTo($parent);
			}
		}else{
			$node->appendTo($parent);
		}
	}

	/**
	 * @param bool $is_new
	 * @param $node
	 * @param $parent
	 */
	protected function _insertChildNode($is_new = TRUE, $node, $parent){
		if (!$is_new){
			$next = $node->next()->one();
			if (!empty($next) && $next->parent_id == $node->parent->id){
				$node->insertBefore($next);
			}else{
				$node->appendTo($parent);
			}
		}else{
			$node->appendTo($parent);
		}
	}
}
