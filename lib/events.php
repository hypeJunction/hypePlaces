<?php

namespace hypeJunction\Places;

/**
 * Setup menus
 * @return void
 */
function pagesetup() {

	elgg_register_menu_item('site', array(
		'name' => 'places',
		'text' => elgg_echo('places'),
		'href' => 'places',
	));
}