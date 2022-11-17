<?php

/**
 * @var $this frontend\base\View
 * @var $widgets \modules\block\BlockWidget[]
 */
?>

<div class="row">
	<?php foreach ($widgets as $widget){
		echo $widget->display();
	} ?>
</div>