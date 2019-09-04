<?php

use ColdTrick\OwnerGatekeeper\Bootstrap;

return [
	'bootstrap' => Bootstrap::class,
	'settings' => [
		'protect_profile' => 'yes',
		'protect_page_owner' => 'yes',
	],
];
