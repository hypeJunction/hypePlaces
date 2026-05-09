<?php

namespace hypeJunction\Places;

use Elgg\DefaultPluginBootstrap;

/**
 * Plugin bootstrap.
 */
class Bootstrap extends DefaultPluginBootstrap {

	/**
	 * {@inheritdoc}
	 */
	public function load() {
		// Load legacy hook/callback function definitions.
		require_once $this->plugin->getPath() . 'lib/functions.php';
		require_once $this->plugin->getPath() . 'lib/hooks.php';
		require_once $this->plugin->getPath() . 'lib/events.php';
	}

	/**
	 * {@inheritdoc}
	 */
	public function boot() {
	}

	/**
	 * {@inheritdoc}
	 */
	public function init() {
		// 'specialties' is exposed as a tag metadata name via the
		// 'tag_names' config in 4.x — see elgg_set_config or skip if unused.
		elgg_register_event_handler('seeds', 'database', [Seeder::class, 'addSeed']);
	}
}
