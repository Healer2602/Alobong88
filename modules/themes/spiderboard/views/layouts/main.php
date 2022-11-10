<?php

/**
 * @var \yii\web\View $this
 * @var string $content
 */

use backend\base\MenuHelper;
use common\widgets\Alert;
use modules\themes\spiderboard\AppAsset;
use modules\themes\spiderboard\widgets\Nav;
use yii\bootstrap5\Html;
use yii\bootstrap5\Modal;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

$asset = AppAsset::register($this);
?>

<?php $this->beginPage() ?>
	<!DOCTYPE html>
	<html lang="<?= Yii::$app->language ?>">
	<head>
		<meta charset="<?= Yii::$app->charset ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="msapplication-TileColor" content="#B5121B">
		<meta name="theme-color" content="#B5121B">
		<?= Html::csrfMetaTags() ?>
		<title><?= Html::encode($this->title . ' - ' . Yii::$app->name) ?></title>
		<?php $this->head() ?>
	</head>
	<body>
	<?php $this->beginBody() ?>

	<nav class="navbar navbar-vertical fixed-start navbar-expand-lg navbar-light" id="sidebar">
		<div class="container-fluid">
			<!-- Toggler -->
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarCollapse" aria-controls="sidebarCollapse" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>

			<!-- User (xs) -->
			<div class="navbar-user d-lg-none">
				<!-- Dropdown -->
				<div class="dropdown">
					<!-- Toggle -->
					<a href="#" id="sidebarIcon" class="dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<div class="avatar avatar-sm avatar-online">
							<img src="<?= $asset->baseUrl . '/img/avatar.png' ?>" class="avatar-img rounded-circle" alt="...">
						</div>
					</a>

					<!-- Menu -->
					<div class="dropdown-menu dropdown-menu-end" aria-labelledby="sidebarIcon">
						<?= Html::a(Yii::t('common', 'Profile'), ['/site/my-profile'],
							['class' => 'dropdown-item']) ?>
						<hr class="dropdown-divider">
						<?= Html::a(Yii::t('common', 'Logout'), ['/site/logout'],
							['class' => 'dropdown-item', 'data-method' => 'POST']) ?>
					</div>
				</div>
			</div>

			<!-- Collapse -->
			<div class="collapse navbar-collapse" id="sidebarCollapse">
				<?php
				$menu_items = MenuHelper::list();
				$nav_menus  = [];

				foreach ($menu_items as $menu_id => $menu_item){
					$menu = [
						'label'   => "<i class='{$menu_item['icon']}'></i> <span>" . $menu_item['label'] . '</span>',
						'active'  => $menu_item['active'] ?? FALSE,
						'visible' => Yii::$app->user->can($menu_item['permission']),
						'encode'  => FALSE,
						'options' => ['id' => "{$menu_id}-menu"]
					];

					if (!empty($menu_item['children'])){
						foreach ($menu_item['children'] as $chilren_id => $children){
							$menu_items = [
								'label'       => $children['label'],
								'url'         => $children['link'],
								'active'      => $children['active'] ?? FALSE,
								'visible'     => Yii::$app->user->can($children['permission']),
								'linkOptions' => ['target' => $children['target'], 'class' => $children['active'] ? 'active' : ''],
								'encode'      => FALSE,
								'options'     => ['id' => "{$chilren_id}-menu"]
							];

							if (!empty($children['children'])){
								$menu_items['options'] = ['class' => 'parent'];
								foreach ($children['children'] as $child_id => $child){
									$child_menu_items = [
										'label'       => $child['label'],
										'url'         => $child['link'],
										'active'      => $child['active'] ?? FALSE,
										'visible'     => Yii::$app->user->can($child['permission']),
										'linkOptions' => ['target' => $child['target'], 'class' => $children['active'] ? 'active' : ''],
										'encode'      => FALSE,
									];

									$menu_items['items'][] = $child_menu_items;
								}
							}


							$menu['items'][] = $menu_items;
						}
					}else{
						if ($menu_item['link'] && is_array($menu_item['link']) && !empty($menu_item['link'][1])){
							unset($menu_item['link'][0]);
							$menu_item['link'] = array_values($menu_item['link']);
						}

						$menu['url']         = $menu_item['link'] ?? '#';
						$menu['linkOptions'] = ['target' => $menu_item['target']];
						$menu['options']     = ['class' => 'menu-item'];

						if (!empty($menu_item['icon'])){
							$menu['label'] = "<i class='{$menu_item['icon']}'></i> <span>" . $menu_item['label'] . '</span>';
						}
					}

					$nav_menus[] = $menu;
				}

				echo Nav::widget([
					'id'      => 'sidebarMenu',
					'options' => ['class' => 'navbar-nav'],
					'items'   => $nav_menus,
				]);
				?>

				<?php if (Yii::$app->user->can('user') || Yii::$app->user->can('setting')): ?>
					<hr class="navbar-divider my-3"><h6 class="navbar-heading"><?= Yii::t('common',
							'System') ?></h6>

					<?= Nav::widget([
						'id'      => 'sidebarMenu',
						'options' => ['class' => 'navbar-nav'],
						'items'   => [
							[
								'url'     => ['/users/index'],
								'label'   => Html::tag('i', '',
										['class' => 'fe fe-users']) . Yii::t('common', 'Staffs'),
								'encode'  => FALSE,
								'visible' => Yii::$app->user->can('user')
							],
							[
								'url'     => ['/setting/index'],
								'label'   => Html::tag('i', '',
										['class' => 'fe fe-settings']) . Yii::t('common',
										'Settings'),
								'encode'  => FALSE,
								'visible' => Yii::$app->user->can('setting')
							],
							[
								'url'     => ['/log/index'],
								'label'   => Html::tag('i', '',
										['class' => 'las la-microchip']) . Yii::t('common',
										'Audit Trails'),
								'encode'  => FALSE,
								'visible' => Yii::$app->user->can('system logs')
							],
							[
								'url'     => ['/setting/clear-cache'],
								'label'   => Html::tag('i', '',
										['class' => 'fe fe-zap-off']) . Yii::t('common',
										'Clear Cache'),
								'encode'  => FALSE,
								'visible' => Yii::$app->user->can('setting')
							]
						],
					]); ?>

				<?php endif; ?>
			</div> <!-- / .navbar-collapse -->

			<div class="copyright text-white-50 text-center d-none d-lg-block">&copy <?= date('Y') ?> <?= Yii::$app->name ?></div>
		</div>
	</nav>

	<div class="main-content pb-4">
		<nav class="navbar navbar-expand-md navbar-light d-none d-md-flex" id="topbar">
			<div class="container-fluid">
				<!-- User -->
				<div class="navbar-user ms-auto">
					<!-- Dropdown -->
					<div class="dropdown">
						<!-- Toggle -->
						<span class="text-body me-2"><?= Yii::$app->user->identity->name ?></span>
						<a href="#" class="avatar avatar-sm avatar-online dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<img src="<?= $asset->baseUrl . '/img/avatar.png' ?>" class="avatar-img rounded-circle" alt="...">
						</a>
						<!-- Menu -->
						<div class="dropdown-menu dropdown-menu-end">
							<?= Html::a(Yii::t('common', 'Profile'), ['/site/my-profile'],
								['class' => 'dropdown-item']) ?>
							<hr class="dropdown-divider">
							<?= Html::a(Yii::t('common', 'Logout'), ['/site/logout'],
								['class' => 'dropdown-item', 'data-method' => 'POST']) ?>
						</div>
					</div>
				</div>
			</div>
		</nav>

		<div class="header mb-0">
			<div class="container-fluid">
				<!-- Body -->
				<div class="header-body border-bottom-0">
					<?= Breadcrumbs::widget([
						'links'              => $this->params['breadcrumbs'] ?? [],
						'itemTemplate'       => "<li class='breadcrumb-item'>{link}</li>\n",
						'activeItemTemplate' => "<li class='breadcrumb-item active'>{link}</li>\n",
						'options'            => ['class' => 'breadcrumb pt-0 pb-3 small']
					]) ?>
					<div class="row">
						<div class="col">
							<h1 class="header-title"><?= Html::encode($this->title) ?></h1>
						</div>
						<?php if (!empty($this->params['primary_link'])): ?>
							<div class="col-auto primary-links">
								<?= $this->params['primary_link'] ?>
							</div>
						<?php endif; ?>
					</div> <!-- / .row -->
				</div> <!-- / .header-body -->
			</div>
		</div>

		<div class="container-fluid">
			<?= Alert::widget() ?>
			<?= $content ?>
		</div>

	</div> <!-- / .main-content -->

	<?php

	Modal::begin([
		'id'            => 'global-modal',
		'title'         => '',
		'options'       => ['class' => 'modal-ajax fade fixed-right', 'tabindex' => NULL],
		'size'          => 'modal-dialog-vertical',
		'clientOptions' => [
			'backdrop' => 'static',
			'keyboard' => FALSE
		],
	]);

	Modal::end();

	Modal::begin([
		'id'            => 'confirm-modal',
		'title'         => '',
		'options'       => ['tabindex' => NULL, 'style' => 'z-index: 1060'],
		'clientOptions' => [
			'backdrop' => 'static',
			'keyboard' => FALSE
		],
	]);

	Modal::end();

	$this->registerJsVar('baseUrl', Url::to(['/']));
	$this->registerJsVar('flatpickrDefaults', [
		'disableMobile'  => TRUE,
		'datetimeFormat' => 'd/m/Y G:i K',
		'dateFormat'     => 'd/m/Y'
	]);
	?>
	<?php $this->endBody() ?>
	</body>
	</html>

<?php $this->endPage() ?>