<?php

/**
 * @var frontend\base\View $this
 * @var \modules\post\models\Banner[] $data
 */

use yii\helpers\Url;

?>
<div class="banner">
	<?php if ($data && (count($data) > 1)): ?>
		<div id="<?= $this->context->id ?? 'banner' ?>" class="carousel slide" data-bs-ride="carousel">
			<div class="carousel-indicators">
				<?php foreach ($data as $key => $item): ?>
					<button type="button" data-bs-target="#<?= $this->context->id ?? 'banner' ?>" data-bs-slide-to="<?= $key ?>" class="<?= $key == 0 ? 'active' : '' ?>" aria-current="<?= $key == 0 ? 'true' : 'false' ?>"></button>
				<?php endforeach; ?>
			</div>
			<div class="carousel-inner">
				<?php foreach ($data as $key => $item): ?>
					<div class="carousel-item <?= $key == 0 ? 'active' : '' ?>">
						<a class="d-block" href="<?= Url::to($item['content']['url']) ?>">
							<img src="<?= $item['thumbnail'] ?>" class="w-100 desktop" alt="...">
							<img src="<?= $item['intro'] ?>" class="w-100 mobile" alt="...">
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php elseif (!empty($data[0])): ?>

		<?php if ($image = $data[0]['thumbnail']): ?>
			<a class="d-block" href="<?= Url::to($data[0]['content']['url']) ?>">
				<img src="<?= $image ?>" class="w-100 desktop" alt="...">
			</a>
		<?php endif; ?>

		<?php if ($image = $data[0]['intro']): ?>
			<a class="d-block" href="<?= Url::to($data[0]['content']['url']) ?>">
				<img src="<?= $image ?>" class="w-100 mobile" alt="...">
			</a>
		<?php endif; ?>

	<?php endif; ?>
</div>
