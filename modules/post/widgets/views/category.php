<?php
/**
 * @var frontend\base\View $this
 * @var \modules\post\frontend\models\Category[] $data
 */

use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;

$items = [];
if (!empty($data)){
	$items = [
		[
			'label'       => Yii::t('post', 'All'),
			'url'         => ['/post/post/list', 'type' => 'post'],
			'linkOptions' => ['class' => 'btn btn-outline-primary btn-sm']
		]
	];

	foreach ($data as $datum){
		$items[] = [
			'label'       => $datum->name . Html::tag('small', $datum->total,
					['class' => 'badge bg-primary text-white ms-2']),
			'url'         => ['/post/category/index', 'slug' => $datum->slug],
			'linkOptions' => ['class' => 'btn btn-outline-primary btn-sm text-nowrap'],
			'encode'      => FALSE
		];
	}
}

if (!empty($items)){
	echo Nav::widget([
		'items'   => $items,
		'options' => [
			'class' => ['press-category mb-2']
		]
	]);
}