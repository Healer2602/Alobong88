<?php

/**
 * @var $this frontend\base\View
 * @var $name string
 * @var $message string
 * @var $exception Exception
 */

use yii\helpers\Html;

$this->title = $name;

$url = Yii::$app->request->getUrl();
if (strpos($url, 'information') !== FALSE){
	Yii::$app->response->redirect(['information'])->send();
}elseif (strpos($url, 'promotion') !== FALSE){
	Yii::$app->response->redirect(['promotion'])->send();
}
?>
<div class="container site-error text-center">

	<div class="row justify-content-center my-5">
		<div class="col-lg-6 col-md-8 py-5">
			<h1 class="text-uppercase text-white-50 mb-4">#<?= Html::encode($exception->statusCode ?? 404) ?></h1>

			<h5 class="text-secondary"><?= Yii::t('common', 'Page Not Found') ?></h5>

			<p class="text-secondary"><?= Yii::t('common',
					'Please check the URL or contact our Customer Care department for assistance.') ?> </p>

			<?= Html::a(Yii::t('common', 'Go home'), ['/site/index'],
				['class' => 'btn btn-primary text-uppercase mt-4 btn-md']) ?>
		</div>
	</div>
</div>