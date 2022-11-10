<?php

use backend\base\GridView;
use yii\helpers\Html;

/* @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $data
 * @var array $filtering
 */


$this->title = Yii::t('game', 'Game Play History');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Games'),
	'url' => ['/game/default/index']
];

$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bank-index">
	<div class="filter">
		<form class="form-inline" action="" method="get">
			<div class="input-group">
				<label class="control-label"><?= Yii::t('common', 'Keywords') ?></label>
				<?= Html::textInput('s', $filtering['s'] ?? NULL,
					['class' => 'form-control', 'placeholder' => Yii::t('game',
						'Player or Game')]) ?>
			</div>
			<div class="input-group">
				<label class="control-label"><?= Yii::t('game',
						'Play Date') ?></label>
				<?= Html::textInput('date',
					$filtering['date'] ?? NULL,
					[
						'class'          => 'form-control',
						'data-flatpickr' => '{"mode": "range"}'
					]) ?>
			</div>
			<div class="input-group">
				<div class="input-group-btn">
					<button class="btn btn-outline-primary" type="submit">
						<i class="fe fe-search" aria-hidden="true"></i>
					</button>
					<button class="btn btn-outline-secondary clear" type="button">
						<i class="fe fe-x" aria-hidden="true"></i></button>
				</div>
			</div>
		</form>
	</div>

	<?= GridView::widget([
		'dataProvider' => $data,
		'columns'      => [
			['class' => 'yii\grid\SerialColumn'],
			[
				'attribute' => 'player_id',
				'value'     => 'player.name'
			],
			[
				'attribute' => 'game_id',
				'value'     => 'game.name'
			],
			'first_play:datetime',
			'last_play:datetime',
		],
	]); ?>
</div>