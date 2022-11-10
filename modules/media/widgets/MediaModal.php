<?php

namespace modules\media\widgets;

use yii\base\Widget;

/**
 * Generate Media Modal Dialog
 */
class MediaModal extends Widget{

	public $modalTitle = 'Media Manager';

	/**
	 * @return string
	 */
	public function run(){
		return <<<HTML
	<div id="{$this->id}" class="media-dialog fade" aria-modal="true" role="dialog">
		<div class="modal-dialog modal-xl">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">{$this->modalTitle}</h5>
					<button type="button" class="btn-close" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="spinner-grow" role="status">
					  <span class="visually-hidden">Loading...</span>
					</div>
				</div>
			</div>
		</div>
	</div>
HTML;
	}
}