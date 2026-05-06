<?php

namespace hypeJunction\Places;

use Elgg\Event;
use ElggMenuItem;

function site_menu_setup(Event $event) {
	$return = $event->getValue();
	$return[] = ElggMenuItem::factory([
		'name' => 'places',
		'text' => elgg_echo('places'),
		'href' => elgg_generate_url('collection:object:hjplace:all'),
	]);
	return $return;
}

function url_handler(Event $event) {
	$entity = $event->getParam('entity');
	if ($entity instanceof Place) {
		return elgg_generate_url('view:object:hjplace', [
			'guid' => $entity->guid,
			'title' => elgg_get_friendly_title($entity->getDisplayName()),
		]);
	}
	return $event->getValue();
}

function entity_menu_setup(Event $event) {
	$entity = $event->getParam('entity');
	if (!$entity instanceof Place) {
		return $event->getValue();
	}
	$return = $event->getValue();
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

function interactions_menu_setup(Event $event) {
	$entity = $event->getParam('entity');
	if (!$entity instanceof Place) {
		return $event->getValue();
	}
	if (!elgg_is_logged_in()) {
		return $event->getValue();
	}

	$return = $event->getValue();
	$bookmarked = elgg_get_logged_in_user()->hasRelationship($entity->guid, 'bookmarked');
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

function owner_block_menu_setup(Event $event) {
	$entity = $event->getParam('entity');
	$return = $event->getValue();
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

function entity_icon_url(Event $event) {
	$entity = $event->getParam('entity');
	if (!$entity instanceof Place) {
		return $event->getValue();
	}
	if ($entity->icontime) {
		return $event->getValue();
	}
	$size = $event->getParam('size') ?? 'medium';
	return elgg_get_simplecache_url("hypePlaces/graphics/icon/{$size}.png");
}

function entity_icon_sizes(Event $event) {
	$entity = $event->getParam('entity');
	if (!$entity instanceof Place) {
		return $event->getValue();
	}
	return [
		'tiny'    => ['w' => 25,  'h' => 25,  'square' => true,  'upscale' => true],
		'small'   => ['w' => 40,  'h' => 40,  'square' => true,  'upscale' => true],
		'medium'  => ['w' => 100, 'h' => 100, 'square' => true,  'upscale' => true],
		'large'   => ['w' => 200, 'h' => 200, 'square' => true,  'upscale' => true],
		'125'     => ['w' => 125, 'h' => 125, 'square' => true,  'upscale' => true],
		'325x200' => ['w' => 325, 'h' => 200, 'square' => false, 'upscale' => true],
	];
}

function setup_site_search_maps(Event $event) {
	$return = $event->getValue();
	$return['places'] = [
		'options' => [
			'types'    => 'object',
			'subtypes' => Place::SUBTYPE,
		],
	];
	return $return;
}

function widget_layout_permissions_check(Event $event) {
	$context = $event->getParam('context');
	$user = $event->getParam('user');
	$page_owner = $event->getParam('page_owner');
	if (!$user instanceof \ElggEntity || !$page_owner instanceof \ElggEntity) {
		return $event->getValue();
	}
	if ($context == PAGEHANDLER && $user->guid == $page_owner->owner_guid) {
		return true;
	}
	return $event->getValue();
}
