<?php

/* @var $plugin \ElggPlugin */
$plugin = elgg_extract('entity', $vars);

echo elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('owner_gatekeeper:settings:protect_profile'),
	'name' => 'params[protect_profile]',
	'default' => 'no',
	'value' => 'yes',
	'checked' => $plugin->protect_profile === 'yes',
	'switch' => true,
]);

echo elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('owner_gatekeeper:settings:protect_page_owner'),
	'#help' => elgg_echo('owner_gatekeeper:settings:protect_page_owner:description'),
	'name' => 'params[protect_page_owner]',
	'default' => 'no',
	'value' => 'yes',
	'checked' => $plugin->protect_page_owner === 'yes',
	'switch' => true,
]);
