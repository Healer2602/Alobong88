<?php

/**
 * @var frontend\base\View $this
 * @var array $data
 * @var array $config
 * @var string $title_tag
 * @var string $full_width
 * @var string $css_class
 */

use yii\bootstrap5\Html;

?>

<div class="m-portlet html-widget <?= $full_width ?> <?= $config['setting']['css_class'] ?? '' ?>">
    <?php if (!empty($data['title'])){
		echo Html::tag('h4', $data['title'], ['class' => 'm-title']);
	} ?>
    <div class="m-content"><?= $data['content'] ?></div>
</div>