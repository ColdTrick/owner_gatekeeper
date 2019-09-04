<?php

namespace ColdTrick\OwnerGatekeeper;

use Elgg\Request;
use Elgg\GatekeeperException;

class OwnerGatekeeperMiddleware {
	
	/**
	 * Protect pages
	 *
	 * @param Request $request the current request
	 *
	 * @return void
	 * @throws GatekeeperException
	 */
	public function __invoke(Request $request) {
		
		$page_owner = elgg_get_page_owner_entity();
		if (!$page_owner instanceof \ElggUser) {
			return;
		}
		
		$user = elgg_get_logged_in_user_entity();
		if ($user instanceof \ElggUser && $user->isAdmin()) {
			// admins can see all
			return;
		}
		
		if ($user instanceof \ElggUser && $user->guid === $page_owner->guid) {
			// can see own content
			return;
		}
		
		$site_url = elgg_get_site_url();
		$path = str_ireplace($site_url, '', $request->getURL());
		if (strpos($path, 'action') === 0) {
			// don't protect actions
			return;
		} elseif ($request->getURL() === $page_owner->getURL()) {
			// profile page
			if (!$this->protectProfile()) {
				return;
			}
		} elseif (!$this->protectPageOwner()) {
			return;
		}
		
		if ($this->matchGroupMembership($page_owner, $user)) {
			return;
		}
		
		throw new GatekeeperException();
	}
	
	/**
	 * Should profile be proteced
	 *
	 * @return bool
	 */
	protected function protectProfile() {
		return elgg_get_plugin_setting('protect_profile', 'owner_gatekeeper') === 'yes';
	}
	
	/**
	 * Should pages owned by the user be protected
	 *
	 * @return bool
	 */
	protected function protectPageOwner() {
		return elgg_get_plugin_setting('protect_page_owner', 'owner_gatekeeper') === 'yes';
	}
	
	/**
	 * Match group memberships between two users
	 *
	 * @param \ElggUser $page_owner     current page owner
	 * @param \ElggUser $logged_in_user current logged in user
	 *
	 * @return bool
	 */
	protected function matchGroupMembership(\ElggUser $page_owner, \ElggUser $logged_in_user = null) {
		
		if (!$logged_in_user instanceof \ElggUser) {
			return false;
		}
		
		$options = [
			'type' => 'group',
			'limit' => false,
			'callback' => function($row) {
				return (int) $row->guid;
			},
			'relationship' => 'member',
			'relationship_guid' => $page_owner->guid,
		];
		
		// page owners groups
		$page_owner_group_guids = elgg_get_entities($options);
		
		// users groups
		$options['relationship_guid'] = $logged_in_user->guid;
		$user_group_guids = elgg_get_entities($options);
		
		// same groups
		$matching_guids = array_intersect($page_owner_group_guids, $user_group_guids);
		
		return !empty($matching_guids);
	}
	
	/**
	 * Add middleware to routes
	 *
	 * @param \Elgg\Hook $hook 'route:config', 'all'
	 *
	 * @return array
	 */
	public static function register(\Elgg\Hook $hook) {
		
		$route_params = $hook->getValue();
		$route_params['middleware'] = elgg_extract('middleware', $route_params, []);
		$route_params['middleware'][] = static::class;
		
		return $route_params;
	}
}
