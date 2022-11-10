<?php
/**
 * @var yii\web\View $this
 * @var \modules\game\models\GameType[] $data
 */
?>

<?php if (!empty($data)):
	foreach ($data as $item):
		$vendors = $item->vendorContents;
		$games = $item->games;
		if (empty($vendors) && empty($games)){
			continue;
		}

		?>
		<section class="py-4">
			<?= $this->render('_header', ['type' => $item]) ?>

			<?php
			if ($vendors){
				echo $this->render("@modules/game/layouts/by_vendor", ['vendors' => $vendors]);
			}elseif ($games){
				echo $this->render("@modules/game/layouts/by_game", ['games' => $games]);
			}
			?>
		</section>
	<?php endforeach; endif; ?>
