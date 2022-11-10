<?php

/**
 * @var frontend\base\View $this
 * @var string $type
 * @var \yii\data\Pagination $pagination
 * @var \modules\post\frontend\models\Post[] $posts
 * @var \modules\post\frontend\models\Category $model
 */


use modules\post\widgets\Category;
use modules\post\widgets\Sidebar;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\LinkPager;

$this->params['bodyClasses'] = "post-category {$type}";
?>
<div class="container my-lg-5 my-3">
	<?= Breadcrumbs::widget([
		'links'              => $this->params['breadcrumbs'] ?? [],
		'itemTemplate'       => "<li class='breadcrumb-item'>{link}</li>\n",
		'activeItemTemplate' => "<li class='breadcrumb-item active'>{link}</li>\n",
		'options'            => ['class' => 'breadcrumb pt-0 pb-lg-3 pb-0 small']
	]) ?>

	<?= Category::widget() ?>

	<?php if (!empty($model->description)): ?>
		<article class="description row justify-content-center mb-4">
			<div class="col-md-10">
				<?= $model->description ?>
			</div>
		</article>
	<?php endif; ?>
	<div class="row">
		<div class="col-12 col-lg-8 col-xl-9">
			<section class="post-listing">
				<?php if (!empty($posts)){
					foreach ($posts as $post){
						echo $this->render('/post/_content', [
							'model' => $post
						]);
					}

					echo LinkPager::widget([
						'pagination' => $pagination
					]);
				}else{
					echo Html::tag('div',
						Yii::t('post', 'No promotion found.'),
						['class' => 'alert alert-danger', 'role' => 'alert']);
				} ?>
			</section>
		</div>
		<div class="col-12 col-lg-4 col-xl-3">
			<?= Sidebar::widget() ?>
		</div>
	</div>
</div>