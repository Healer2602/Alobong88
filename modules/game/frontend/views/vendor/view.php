<?php
/**
 * @var frontend\base\View $this
 * @var \modules\game\models\Vendor $model
 * @var \modules\game\models\Game[] $data
 * @var \yii\data\Pagination $pagination
 */

use modules\post\widgets\BannerSlider;
use yii\bootstrap5\LinkPager;
use yii\helpers\Html;
use yii\helpers\Url;

?>

<?= BannerSlider::widget(['position' => "vendors"]) ?>

<div class="default-bg">
	<div class="container">
		<?php if ($data): ?>
			<div class="listing full-width">
				<div class="search-place">
					<div class="row">
						<div class="col-lg-4 ms-auto">
							<form method="get" action="<?= Url::current() ?>">
								<div class="input-group">
									<span class="input-group-text"><i class="fas fa-search" aria-hidden="true"></i></span>
									<input type="search" name="s" class="form-control" placeholder="<?= Yii::t('game',
										'Search game') ?>" value="<?= Html::encode(Yii::$app->request->get('s')) ?>">
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>

			<?= $this->render("@modules/game/layouts/by_game", ['games' => $data]) ?>

			<?= LinkPager::widget([
				'pagination'  => $pagination,
				'listOptions' => ['class' => 'pagination justify-content-end']
			]); ?>

		<?php else: ?>
			<p class="lead text-muted"><?= Yii::t('common', 'No record found.') ?></p>
		<?php endif ?>
	</div>
</div>