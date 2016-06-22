<?php
/**
 * Main plugin file
 */

require_once(dirname(__FILE__) . '/lib/functions.php');

// register default Elgg events
elgg_register_event_handler('init', 'system', 'owner_gatekeeper_init');

/**
 * Called during system init
 *
 * @return void
 */
function owner_gatekeeper_init() {
	
	// extends views
	elgg_extend_view('page/default', 'owner_gatekeeper/extends/page', 400);
}
