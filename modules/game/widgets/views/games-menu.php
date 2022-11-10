<?php
/**
 * @var yii\web\View $this
 * @var \modules\game\models\GameType[] $data
 */

use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;

$nav_menus = [
	[
		'label'       => Html::tag('div', '', ['class' => 'icon home']) . Html::tag('span',
				Yii::t('common', 'Home')),
		'encode'      => FALSE,
		'options'     => ['id' => "home-menu"],
		'url'         => ['/site/index'],
		'linkOptions' => ['class' => 'p-0']
	]
];

foreach ($data as $menu_id => $menu_item){
	$menu = [
		'label'       => "<div class='icon'><img src='{$menu_item['icon']}' alt=''></div> <span>" . Html::encode(Yii::t('game',
				$menu_item['name'])) . '</span>',
		'encode'      => FALSE,
		'options'     => ['id' => "{$menu_id}-menu"],
		'url'         => $menu_item->url,
		'linkOptions' => ['class' => 'p-0']
	];

	$nav_menus[] = $menu;
}

$nav_menus[] = [
	'label'       => Html::tag('div', '', ['class' => 'icon promotion']) . Html::tag('span',
			Yii::t('common', 'Promotion')),
	'encode'      => FALSE,
	'options'     => ['id' => "promotion-menu"],
	'url'         => ['/post/post/list', 'type' => 'post'],
	'linkOptions' => ['class' => 'p-0']
];

?>

<div class="custom-menu top">
	<div class="container">
		<?= Nav::widget([
			'items'        => $nav_menus,
			'options'      => [
				'class' => 'list-unstyled'
			],
			'encodeLabels' => FALSE,
		]); ?>
	</div>
</div>