<?php

/**
 * Protect user content pages from being access by other users
 *
 * @param ElggEntity $page_owner (optional) the page owner to protect
 * @param ElggUser   $user       (optional) the user to protect against
 *
 * @return void
 */
function owner_gatekeeper_protect(ElggEntity $page_owner = null, ElggUser $user = null) {
	static $run_once;
	
	if (isset($run_once)) {
		// prevent deadloops
		return;
	}
	$run_once = true;
	
	if (!($page_owner instanceof ElggEntity)) {
		$page_owner = elgg_get_page_owner_entity();
	}
	
	if (!($page_owner instanceof ElggUser)) {
		// not a user, so no need to protect
		return;
	}
	
	if (!($user instanceof ElggUser)) {
		$user = elgg_get_logged_in_user_entity();
	}
	
	if (!($user instanceof ElggUser)) {
		// not logged in
		_owner_gatekeeper_apply_rules($page_owner);
		return;
	}
	
	if ($user->isAdmin()) {
		// admins can see all
		return;
	}
	
	if ($page_owner->getGUID() === $user->getGUID()) {
		// you can see your own content
		return;
	}
	
	// determine access
	$has_access = owner_gatekeeper_apply_rules($page_owner, $user);
	// trigger plugin hook
	$params = [
		'user' => $user,
		'page_owner' => $page_owner,
		'original_access' => $has_access,
	];
	$has_access = (bool) elgg_trigger_plugin_hook('apply_rules', 'owner_gatekeeper', $params, $has_access);
	
	if (!$has_access) {
		forward('', '404');
	}
}

/**
 * Apply the protection rules
 *
 * @param ElggUser $page_owner
 * @param ElggUser $user
 *
 * @access private
 * @return bool true you have access, false you don't
 */
function owner_gatekeeper_apply_rules(ElggUser $page_owner, ElggUser $user = null) {
	
	if (!($page_owner instanceof ElggUser)) {
		return false;
	}
	
	if (current_page_url() === elgg_normalize_url($page_owner->getURL())) {
		// profile page
		if (!owner_gatekeeper_protect_profile()) {
			return true;
		}
	} else {
		// content pages
		if (!owner_gatekeeper_protect_page_owner()) {
			return true;
		}
	}
	
	if (!($user instanceof ElggUser)) {
		return false;
	}
	
	if (!owner_gatekeeper_match_group($page_owner, $user)) {
		return false;
	}
	
	return true;
}

/**
 * Is the users profile page protected
 *
 * @return bool
 */
function owner_gatekeeper_protect_profile() {
	static $result;
	
	if (isset($result)) {
		return $result;
	}
	
	$result = true;
	$setting = elgg_get_plugin_setting('protect_profile', 'owner_gatekeeper');
	if ($setting === 'no') {
		$result = false;
	}
	
	return $result;
}

/**
 * Are the content pages protected
 *
 * @return bool
 */
function owner_gatekeeper_protect_page_owner() {
	static $result;
	
	if (isset($result)) {
		return $result;
	}
	
	$result = true;
	$setting = elgg_get_plugin_setting('protect_page_owner', 'owner_gatekeeper');
	if ($setting === 'no') {
		$result = false;
	}
	
	return $result;
}

/**
 * Check if page owner and user are a member of the same group
 *
 * @param ElggUser $page_owner the current page owner
 * @param ElggUser $user       the user to check with
 *
 * @return bool
 */
function owner_gatekeeper_match_group(ElggUser $page_owner, ElggUser $user) {
	
	if (!($page_owner instanceof ElggUser) || !($user instanceof ElggUser)) {
		return false;
	}
	
	$options = [
		'type' => 'group',
		'limit' => false,
		'callback' => function($row) {
			return (int) $row->guid;
		},
		'relationship' => 'member',
		'relationship_guid' => $page_owner->getGUID(),
	];
	
	// page owners groups
	$page_owner_group_guids = elgg_get_entities_from_relationship($options);
	
	// users groups
	$options['relationship_guid'] = $user->getGUID();
	$user_group_guids = elgg_get_entities_from_relationship($options);
	
	// same groups
	$matching_guids = array_intersect($page_owner_group_guids, $user_group_guids);
	
	return !empty($matching_guids);
}
