<?php

return [
	'bootstrap' => [
		'website', 'notification', 'media', 'block',
		'matrix', 'customer', 'wallet',
		'media_center', 'game', 'promotion', 'post', 'ezp', 'internet_banking', 'gmail',
		'agent', 'copay'
	],
	'modules'   => [
		'notification'     => \modules\notification\Module::class,
		'media'            => \modules\media\Module::class,
		'block'            => \modules\block\Module::class,
		'matrix'           => \modules\matrix\Module::class,
		'customer'         => \modules\customer\Module::class,
		'website'          => \modules\website\Module::class,
		'media_center'     => \modules\media_center\Module::class,
		'game'             => \modules\game\Module::class,
		'wallet'           => \modules\wallet\Module::class,
		'post'             => \modules\post\Module::class,
		'ezp'              => \modules\ezp\Module::class,
		'internet_banking' => \modules\internet_banking\Module::class,
		'gmail'            => \modules\gmail\Module::class,
		'promotion'        => \modules\promotion\Module::class,
		'agent'            => \modules\agent\Module::class,
		'copay'            => \modules\copay\Module::class,
	]
];