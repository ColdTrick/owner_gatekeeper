<?php

namespace ColdTrick\OwnerGatekeeper;

use Elgg\DefaultPluginBootstrap;

class Bootstrap extends DefaultPluginBootstrap {
	
	/**
	 * {@inheritDoc}
	 * @see \Elgg\DefaultPluginBootstrap::boot()
	 */
	public function boot() {
		$hooks = $this->elgg()->hooks;
		
		$hooks->registerHandler('route:config', 'all', __NAMESPACE__ . '\OwnerGatekeeperMiddleware::register');
	}
}
