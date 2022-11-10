<?php
/**
 * @var yii\web\View $this
 * @var \modules\game\models\Vendor[] $data
 * @var \modules\game\models\GameType $model
 */

use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\helpers\ArrayHelper;

$all       = [
	[
		'label'       => "<div class='icon all'></div> <span class='small text-capitalize'>" . Yii::t('common',
				'All') . '</span>',
		'encode'      => FALSE,
		'options'     => ['id' => "vendor-all"],
		'url'         => $model->url,
		'linkOptions' => ['class' => 'py-0 px-2'],
		'active'      => empty(Yii::$app->request->get('partner'))
	]
];
$nav_menus = [];
foreach ($data as $menu_id => $menu_item){
	$menu = [
		'label'       => "<div class='icon'><img src='{$menu_item['icon']}' alt=''></div> <span class='small'>" . Html::encode($menu_item['name']) . '</span>',
		'encode'      => FALSE,
		'options'     => ['id' => "vendor-{$menu_id}-menu"],
		'url'         => ArrayHelper::merge($model->url,
			['partner' => $menu_item->slug]),
		'linkOptions' => ['class' => 'py-0 px-2']
	];

	$nav_menus[] = $menu;
}
$number_slide = count($nav_menus);

?>

<div class="custom-menu vendors">
	<div class="container">
		<div class="d-flex w-100">
			<?= Nav::widget([
				'items'        => $all,
				'options'      => [
					'class' => 'list-unstyled pb-0'
				],
				'encodeLabels' => FALSE
			]); ?>

			<?= Nav::widget([
				'items'        => $nav_menus,
				'options'      => [
					'class'      => 'list-unstyled pb-0 w-100 custom-slider',
					'data-slick' => "{\"slidesToShow\": $number_slide}"
				],
				'encodeLabels' => FALSE,
			]); ?>
		</div>
	</div>
</div>