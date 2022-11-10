<?php

/**
 * @var yii\web\View $this
 * @var array $settings
 */

use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title                   = Yii::t('common', 'Settings');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="settings">
	<div class="row">
		<?php foreach ($settings as $setting): ?><?php if (!empty($setting['permission']) && Yii::$app->user->can($setting['permission'])): ?>
			<div class="col-xl-3 col-lg-4">
				<a class="card text-dark" href="<?= Url::to($setting['link'] ?? '#') ?>">
					<div class="card-body">
						<div class="row align-items-center">
							<div class="col">
								<h5 class="card-title text-uppercase text-muted mb-2"><?= Html::encode(Yii::t('common',
										$setting['name'])) ?></h5>
								<p class="mb-0"><?= $setting['description'] ?? '' ?></p>
							</div>
							<div class="col-auto"><?= Html::tag('span', '',
									['class' => ['h2 text-muted mb-0', $setting['icon'] ?? NULL]]) ?></div>
						</div>
					</div>
				</a>
			</div>
		<?php endif; ?><?php endforeach; ?>
	</div>
</div>