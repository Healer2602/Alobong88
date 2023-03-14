<?php

/**
 * @var \frontend\base\View $this
 * @var string $content
 */

use common\models\Language;
use frontend\widgets\Footer;
use modules\customer\widgets\Customer;
use modules\themes\captain\AppAsset;
use modules\website\widgets\NavMenu;
use yii\bootstrap5\Html;
use yii\bootstrap5\Modal;
use yii\helpers\Url;

$asset = AppAsset::register($this);
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="msapplication-TileColor" content="#D00000">
    <meta name="theme-color" content="#D00000">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title . ' - ' . Yii::$app->name) ?></title>
    <?php $this->head() ?>

    <?php if (!empty($this->setting->gtm)): ?>
    <!-- Google Tag Manager -->
    <script>
    (function(w, d, s, l, i) {
        w[l] = w[l] || [];
        w[l].push({
            'gtm.start': new Date().getTime(),
            event: 'gtm.js'
        });
        var f = d.getElementsByTagName(s)[0],
            j = d.createElement(s),
            dl = l != 'dataLayer' ? '&l=' + l : '';
        j.async = true;
        j.src =
            'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
        f.parentNode.insertBefore(j, f);
    })(window, document, 'script', 'dataLayer', '<?= $this->setting->gtm?>');
    </script> <!-- End Google Tag Manager -->
    <?php endif; ?>
</head>

<body class="<?= $this->params['bodyClasses'] ?? 'page' ?>">
    <?php $this->beginBody() ?>
    <?php if (!empty($this->setting->gtm)): ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=<?= $this->setting->gtm ?>" height="0" width="0"
            style="display:none;visibility:hidden"></iframe>
    </noscript> <!-- End Google Tag Manager (noscript) -->
    <?php endif; ?>
    <header class="main">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light">
                <?php if (!empty($this->setting->site_logo)): ?>
                <a class="navbar-brand" href="<?= Url::to(['/']) ?>">
                    <img src="<?= $this->setting->site_logo ?>" alt="">
                </a>
                <?php endif; ?>

                <?= Customer::widget() ?>

                <?php
				$languages = Language::listLanguage(FALSE);
				if ($languages && count($languages) > 1):
					?>
                <div class="dropdown language-switcher">
                    <a class="dropdown-toggle language <?= Language::current() ?>" href="#" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <span><?= Html::encode(Language::currentName()) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdown-menu">
                        <?php foreach ($languages as $code => $language): ?>
                        <li>
                            <a class="dropdown-item language <?= $code ?>"
                                href="<?= Url::current(['lang' => $code]) ?>">
                                <span><?= Html::encode($language) ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                <div class="media-menu">
                    <a href="#menu-top-menu"><i class="la la-bars" aria-hidden="true"></i></a>
                </div>
            </nav>
        </div>
        <div class="top-menu " id="top-menu" data-title="<?= Yii::t('common','Menu') ?>">
            <div class="container">
                <?= NavMenu::widget(['position' => 'top-menu']) ?>
            </div>
        </div>
    </header>

    <main class="main">
        <?= $content ?>
    </main>

    <footer class="main">
        <div class="container">
            <?= Footer::widget() ?>
        </div>
    </footer>

    <div id="page"></div>

    <?php Modal::begin([
		'id'            => 'global-modal',
		'title'         => '',
		'options'       => ['class' => 'modal-ajax fade fixed-right', 'tabindex' => NULL],
		'size'          => 'modal-dialog-vertical',
		'clientOptions' => [
			'backdrop' => 'assets',
			'keyboard' => FALSE
		],
	]); ?>

    <?php Modal::end(); ?>

    <?php
	$this->registerJsVar('baseUrl', Url::to(['/']));
	$this->registerJsVar('flatpickrDefaults', [
		'disableMobile'  => TRUE,
		'datetimeFormat' => 'd/m/Y G:i K',
		'dateFormat'     => 'd/m/Y'
	]);
	?>
    <?php $this->endBody() ?>
</body>

</html>

<?php $this->endPage() ?>