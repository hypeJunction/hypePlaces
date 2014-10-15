<?php

namespace hypeJunction\Places;

$guid = get_input('guid');
$entity = get_entity($guid);

if (!$entity) {
	register_error(elgg_echo('places:error:not_found'));
	forward(REFERER);
}

if (!check_entity_relationship(elgg_get_logged_in_user_guid(), 'bookmarked', $guid)) {
	add_entity_relationship(elgg_get_logged_in_user_guid(), 'bookmarked', $guid);
	elgg_create_river_item(array(
		'view' => 'framework/river/places/bookmark',
		'action_type' => 'stream:places:bookmark',
		'subject_guid' => elgg_get_logged_in_user_guid(),
		'object_guid' => $entity->guid,
		'access_id' => $entity->access_id
	));
	system_message(elgg_echo('places:bookmark:create:success'));
	forward(REFERER);
}

register_error(elgg_echo('places:bookmark:create:error'));
forward(REFERER);
