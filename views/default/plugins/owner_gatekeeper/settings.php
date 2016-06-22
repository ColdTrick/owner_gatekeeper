<?php

$plugin = elgg_extract('entity', $vars);

$yesno_options = [
	'yes' => elgg_echo('option:yes'),
	'no' => elgg_echo('option:no'),
];

echo elgg_view_input('select', [
	'name' => 'params[protect_profile]',
	'value' => $plugin->protect_profile,
	'options_values' => $yesno_options,
	'label' => elgg_echo('owner_gatekeeper:settings:protect_profile'),
]);

echo elgg_view_input('select', [
	'name' => 'params[protect_page_owner]',
	'value' => $plugin->protect_page_owner,
	'options_values' => $yesno_options,
	'label' => elgg_echo('owner_gatekeeper:settings:protect_page_owner'),
	'help' => elgg_echo('owner_gatekeeper:settings:protect_page_owner:description'),
]);
