<?php
/**
 * @var frontend\base\View $this
 * @var \modules\post\frontend\models\Post[] $data
 */

use yii\bootstrap5\Html;
use yii\helpers\Url;

?>

<?php if (!empty($data)): ?>
	<h4 class="blog-box-heading mb-4">
		<?= Yii::t('common', 'Featured Articles') ?>
	</h4>
	<ul class="list-featured list-unstyled">
		<?php foreach ($data as $item): ?>
			<li class="post mb-4">
				<div class="row g-3">
					<?php if (!empty($item->thumbnail)): ?>
						<div class="col-lg-4 col-4">
							<a href="<?= Url::to($item->url) ?>" class="thumbnail ratio ratio-1x1">
								<img src="<?= $item->thumbnail ?>" alt="<?= Html::encode($item->name) ?>" class="img-fluid rounded">
							</a>
						</div>
					<?php endif; ?>
					<div class="col-lg col-8">
						<h5 class="title mb-2">
							<a href="<?= Url::to($item->url) ?>"><?= Html::encode($item->name) ?></a>
						</h5>
					</div>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>