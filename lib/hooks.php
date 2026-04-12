<?php

namespace hypeJunction\Places;

use ElggMenuItem;

/**
 * Site menu handler — adds the "Places" menu item to the site navigation.
 */
function site_menu_setup($hook, $type, $return, $params) {
	$return[] = ElggMenuItem::factory([
		'name' => 'places',
		'text' => elgg_echo('places'),
		'href' => elgg_generate_url('collection:object:hjplace:all'),
	]);
	return $return;
}

/**
 * Give entities their own URLs.
 */
function url_handler($hook, $type, $return, $params) {
	$entity = elgg_extract('entity', $params);
	if ($entity instanceof Place) {
		return elgg_generate_url('view:object:hjplace', [
			'guid' => $entity->guid,
			'title' => elgg_get_friendly_title($entity->getDisplayName()),
		]);
	}
	return $return;
}

/**
 * Setup entity menus.
 */
function entity_menu_setup($hook, $type, $return, $params) {
	$entity = elgg_extract('entity', $params);
	if (!$entity instanceof Place) {
		return $return;
	}
	if (elgg_is_admin_logged_in()) {
		$featured = $entity->featured;
		$on_action = 'action/places/feature?guid=' . $entity->guid;
		$off_action = 'action/places/unfeature?guid=' . $entity->guid;
		$on_text = elgg_echo('places:feature');
		$off_text = elgg_echo('places:unfeature');
		$return[] = ElggMenuItem::factory([
			'name' => 'feature',
			'href' => $featured ? $off_action : $on_action,
			'text' => $featured ? $off_text : $on_text,
			'confirm' => true,
		]);
	}
	return $return;
}

/**
 * Setup user interaction menus.
 */
function interactions_menu_setup($hook, $type, $return, $params) {
	$entity = elgg_extract('entity', $params);
	if (!$entity instanceof Place) {
		return $return;
	}
	if (!elgg_is_logged_in()) {
		return $return;
	}

	$bookmarked = (bool) check_entity_relationship(elgg_get_logged_in_user_guid(), 'bookmarked', $entity->guid);
	$on_action = 'action/places/bookmark?guid=' . $entity->guid;
	$off_action = 'action/places/unbookmark?guid=' . $entity->guid;
	$return[] = ElggMenuItem::factory([
		'name' => 'bookmark',
		'href' => $bookmarked ? $off_action : $on_action,
		'text' => elgg_echo($bookmarked ? 'places:unbookmark' : 'places:bookmark'),
	]);

	if ($entity->checkins) {
		$checked_in = $entity->isCheckedIn();
		$on_action = 'action/places/checkin?guid=' . $entity->guid;
		$off_action = 'action/places/checkout?guid=' . $entity->guid;
		$return[] = ElggMenuItem::factory([
			'name' => 'checkin',
			'href' => $checked_in ? $off_action : $on_action,
			'text' => elgg_echo($checked_in ? 'places:checkout' : 'places:checkin'),
		]);
	}

	return $return;
}

/**
 * Setup owner block menu.
 */
function owner_block_menu_setup($hook, $type, $return, $params) {
	$entity = elgg_extract('entity', $params);
	if ($entity instanceof \ElggGroup && $entity->places_enable !== 'no') {
		$return[] = ElggMenuItem::factory([
			'name' => 'group:places',
			'text' => elgg_echo('places:group'),
			'href' => elgg_generate_url('collection:object:hjplace:group', [
				'guid' => $entity->guid,
			]),
		]);
	} else if ($entity instanceof \ElggUser) {
		$return[] = ElggMenuItem::factory([
			'name' => 'user:places',
			'text' => elgg_echo('places'),
			'href' => elgg_generate_url('collection:object:hjplace:owner', [
				'username' => $entity->username,
			]),
		]);
	}
	return $return;
}

/**
 * Allow place owners to add widgets.
 */
function widget_layout_permissions_check($hook, $type, $return, $params) {
	$context = elgg_extract('context', $params);
	$user = elgg_extract('user', $params);
	$page_owner = elgg_extract('page_owner', $params);
	if (!$user instanceof \ElggEntity || !$page_owner instanceof \ElggEntity) {
		return $return;
	}
	if ($context == PAGEHANDLER && $user->guid == $page_owner->owner_guid) {
		return true;
	}
	return $return;
}
