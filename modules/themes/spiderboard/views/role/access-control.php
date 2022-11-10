<?php

use yii\helpers\Html;

/* @var yii\web\View $this */
/* @var yii\data\ActiveDataProvider $dataProvider */
/* @var array $user_groups */

$this->title = Yii::t('common', 'Access Control');

$this->params['breadcrumbs'][] = [
	'label' => Yii::t('common', 'Staffs'),
	'url'   => ['users/index']
];
$this->params['breadcrumbs'][] = $this->title;
?>

	<div class="card">
		<?php
		echo Html::beginForm('', '');
		foreach ($user_groups as $user_group){
			if (empty($user_group['is_primary'])){
				echo Html::hiddenInput("Permission[{$user_group['id']}]", 0);
			}
		}

		if (!empty($user_groups)){
			echo $this->render('_module-access-control', [
				'permissions' => $permissions,
				'user_groups' => $user_groups
			]);

			?>
			<div class="card-footer">
				<div class="text-right">
					<?= Html::submitButton('Save Changes',
						['class' => 'btn btn-save btn-primary btn-with-icon']) ?>
				</div>
			</div>
			<?= Html::endForm() ?>

		<?php } ?>
	</div>

<?php
$js = <<< JS
    $('.check-all').on('change', function(){
       var index = $(this).parents('th').index();
       var that = $(this);
       $(this).parents('table').find('tbody tr').each(function() {
            $(this).find('td').eq(index).find('input').prop('checked', !!that.is(':checked'));
       })
    });
JS;
$this->registerJs($js);