<?php

use yii\bootstrap5\Html;
use yii\helpers\Url;

/**
 * @var frontend\base\View $this
 * @var \modules\post\frontend\models\Post $model
 */
?>
<div class="post">
	<div class="row">
		<?php if (!empty($model->thumbnail)): ?>
			<div class="col-lg-3 col-md-4">
				<a href="<?= Url::to($model->url) ?>" class="ratio ratio-4x3 thumbnail">
					<img src="<?= $model->thumbnail ?>" alt="<?= Html::encode($model->name) ?>" class="rounded img-fluid">
				</a>
			</div>
		<?php endif; ?>
		<div class="col-md">
			<div class="mb-2 d-flex align-items-center small">
				<span class="text-muted"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></span>
				<?= empty($model->category) ? '' : Html::a($model->category->name,
					$model->category->url,
					['class' => 'category-name']); ?>
			</div>
			<h3 class="title mb-2">
				<a href="<?= Url::to($model->url) ?>"><?= Html::encode($model->name) ?></a>
			</h3>
			<div class="intro"><?= $model->intro ?></div>
		</div>
	</div>
</div>
