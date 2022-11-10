<?php
/**
 * @var yii\web\View $this
 * @var \modules\customer\frontend\models\CustomerIdentity $model
 */

use modules\customer\models\Kyc;
use yii\bootstrap5\Modal;
use yii\helpers\Html;
use yii\helpers\Url;

$code = $model->referral->code ?? '';
$link = Url::to(["/customer/default/referral", 'code' => $code], TRUE);

$description = Yii::t('customer',
	'Welcome to {0}! You have received an invitation from your friend. Click on the link below to sign up and get an exclusive welcome bonus!',
	[Yii::$app->name]);

?>

	<div class="code-place box-round">
		<div class="row align-items-center justify-content-between">
			<div class="col-xxl-auto col-lg-4 mb-xxl-0 mb-2">
				<div class="code box-round">
					<div class="input-group">
						<input type="text" class="form-control icon-copy" value="<?= $code ?>">
						<input type="text" class="form-control ref-code" value="<?= $description . ' ' . $link ?>">
						<div class="dropdown">
							<button class="btn btn-primary btn-round btn-sm" type="button" id="dropdown-share" data-bs-toggle="dropdown" aria-expanded="false">
								<?= Yii::t('common', 'Share') ?>
							</button>

							<ul class="dropdown-menu" aria-labelledby="dropdown-share">
								<li>
									<?= Html::a('<i class="fas fa-copy"></i> ' . Yii::t('common',
											'Copy to clipboard'), 'javascript:',
										[
											'data-bs-toggle' => 'modal',
											'data-bs-target' => '#modal-share',
											'class'          => 'copy-code'
										]) ?>
								</li>
								<li>
									<?= Html::a('<i class="fab fa-facebook-square" aria-hidden="true"></i> Facebook',
										"https://www.facebook.com/share.php?u={$link}",
										['target' => '_blank', 'class' => 'facebook']) ?>
								</li>
								<li>
									<?= Html::a('<i class="fab fa-google-plus-square" aria-hidden="true"></i> Email',
										"mailto:?body={$description}%20%0A%20%0A{$link}",
										['target' => '_blank', 'class' => 'email']) ?>
								</li>
								<li>
									<?= Html::a('<i class="fab fa-whatsapp-square" aria-hidden="true"></i> Whatsapp',
										"https://wa.me/?text={$description} {$link}",
										['target' => '_blank', 'class' => 'whatsapp']) ?>
								</li>
								<li>
									<?= Html::a('<i class="fab fa-telegram" aria-hidden="true"></i> Telegram',
										"https://t.me/share/url?url={$link}&text={$description}",
										['target' => '_blank', 'class' => 'telegram']) ?>
								</li>
								<li>
									<?= Html::a('<i class="fab fa-line" aria-hidden="true"></i> Line',
										"https://lineit.line.me/share/ui?url={$link}&text={$description}",
										['target' => '_blank', 'class' => 'line']) ?>
								</li>
								<li>
									<?= Html::a('<i class="fab fa-skype" aria-hidden="true"></i> Skype',
										"https://web.skype.com/share?url={$link}!%0A%0A{$description}",
										['target' => '_blank', 'class' => 'skype']) ?>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xxl col-lg-8">
				<div class="content">
					<div class="icon">
						<i class="fas fa-users" aria-hidden="true"></i>
					</div>
					<div class="description">
						<div><?= Yii::t('customer',
								'Share the referral code with your friend.') ?></div>
						<div class="text-medium fw-bold">
							<?= Yii::t('customer',
								'Get exclusive referral bonuses when your friend signs up using your referral code.') ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="wallet-place box-round">
		<div class="row align-items-center">
			<div class="col-lg-auto mb-lg-0 mb-2">
				<div class="d-flex justify-content-between">
					<div class="rank <?= $model->rank->type ?? '' ?>">
						<div class="image"></div>
						<div class="content">
							<div>
								<?= Yii::t('customer', 'Welcome') ?>
							</div>
							<div class="icon-check <?= (!empty($model->kyc->status) && $model->kyc->status == Kyc::STATUS_APPROVED) ? 'checked' : '' ?>">
								<strong class="text-primary text-medium"><?= $model->name ?></strong>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-lg mb-lg-0 mb-2">
				<div class="wallet">
					<div class="content">
						<div class="title">
							<?= Yii::t('customer', 'Main wallet') ?>
						</div>
						<div class="balance">
							<?= Yii::$app->formatter->asCurrency($model->wallet->balance ?? 0) ?>
						</div>
					</div>
					<a href="javascript:" onclick="location.reload()" class="btn btn-round btn-secondary btn-sm"><i class="fas fa-redo" aria-hidden="true"></i></a>
					<a href="<?= Url::to(['/wallet/transfer/restore']) ?>" class="btn btn-info btn-restore">
						<?= Yii::t('customer', 'Restore'); ?>
					</a>
				</div>
			</div>
			<div class="col-lg-auto">
				<div class="auto-transfer">
					<div class="form-check form-switch form-switch-md">
						<label class="form-check-label" for="auto-transfer">
							<?= Yii::t('customer', 'Main wallet auto transfer'); ?>
						</label>
						<?= Html::checkbox('auto-transfer', !empty($model->wallet->auto_transfer),
							['class' => 'form-check-input', 'role' => "switch", 'id' => 'auto-transfer']) ?>
					</div>
				</div>
			</div>
		</div>
	</div>

<?php Modal::begin([
	'id'          => 'modal-share',
	'title'       => NULL,
	'options'     => ['class' => 'modal-ajax fade', 'tabindex' => NULL],
	'size'        => 'modal-md',
	'closeButton' => FALSE
]); ?>
	<div class="description-share text-center">
		<?= Html::encode($description) ?>
		<?= Yii::$app->formatter->asUrl($link, ['class' => 'd-block mt-4']) ?>
	</div>
<?php Modal::end(); ?>

<?php
$this->registerJsVar('textCopy', Yii::t('wallet', 'Copied to Clipboard'));
$url = Url::to(['/wallet/transfer/auto']);
$js  = <<<JS
	$('#auto-transfer').on('change', function(event){
		$.post('{$url}');
	});
JS;

$this->registerJs($js);