<?php

/**
 * @var frontend\base\View $this
 * @var Post $model
 */

use modules\post\frontend\models\Post;
use modules\post\widgets\Sidebar;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->params['bodyClasses'] = "post-category {$model->slug}";
?>
<div class="container my-lg-5 my-3">
	<?= Breadcrumbs::widget([
		'links'              => $this->params['breadcrumbs'] ?? [],
		'itemTemplate'       => "<li class='breadcrumb-item'>{link}</li>\n",
		'activeItemTemplate' => "<li class='breadcrumb-item active'>{link}</li>\n",
		'options'            => ['class' => 'breadcrumb pt-0 pb-lg-3 pb-0 small']
	]) ?>

	<div class="row">
		<div class="col-12 col-lg-8 col-xl-9">
			<article class="post-detail" itemscope itemtype="http://schema.org/NewsArticle">
				<h1 class="page-title mb-3" itemprop="headline"><?= Html::encode($model->name) ?></h1>
				<small class="text-muted d-block mb-2"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></small>
				<div class="content" itemprop="articleBody">
					<?= $model->content ?>
				</div>

				<meta itemprop="url" content="<?= Url::to($model->url, TRUE) ?>">
				<meta itemprop="mainEntityOfPage" content="<?= Url::to($model->url, TRUE) ?>">
				<meta itemprop="datePublished" content="<?= Yii::$app->formatter->asDatetime($model->created_at,
					'php:Y-m-d\TH:i:sP') ?>">
				<meta itemprop="dateModified" content="<?= Yii::$app->formatter->asDatetime($model->updated_at,
					'php:Y-m-d\TH:i:sP') ?>">
				<?php if (!empty($model->thumbnail)): ?>
					<meta itemprop="image" content="<?= Url::to($model->thumbnail, TRUE) ?>">
				<?php endif; ?>
				<div class="d-none" itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
					<meta itemprop="name" content="<?= $this->setting->site_name ?? Yii::$app->name ?>">
					<div itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
						<?= Html::img($this->setting->site_logo ?? '') ?>
						<meta itemprop="url" content="<?= $this->setting->site_logo ?? '/' ?>">
					</div>
				</div>
				<div class="d-none" itemprop="author" itemscope itemtype="https://schema.org/Person">
					<meta itemprop="name" content="<?= $model->author->name ?>">
				</div>
			</article>

			<?php if ($relates = $model->relates): ?>
				<section class="post-listing mt-lg-5 pt-lg-5 mt-4 pt-4 border-top related-posts">
					<h4 class="title mb-4 fw-bold"><?= Yii::t('post', 'Related Articles') ?></h4>
					<?php foreach ($relates as $relate){
						echo $this->render('_content', [
							'model' => $relate
						]);
					} ?>
				</section>
			<?php endif; ?>
		</div>
		<div class="col-12 col-lg-4 col-xl-3">
			<?= Sidebar::widget() ?>
		</div>
	</div>
</div>