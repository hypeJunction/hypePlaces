<?php

namespace hypeJunction\Places;

$guid = get_input('guid');
$entity = get_entity($guid);

if (!$entity instanceof Place) {
	register_error(elgg_echo('places:error:not_found'));
	forward(REFERER);
}

if (!$entity->isCheckedIn()) {
	if ($id = $entity->checkIn()) {
		elgg_create_river_item(array(
			'view' => 'framework/river/places/checkin',
			'action_type' => 'stream:places:checkin',
			'subject_guid' => elgg_get_logged_in_user_guid(),
			'object_guid' => $entity->guid,
			'acess_id' => $entity->access_id,
			'annotation_id' => $id
		));
		system_message(elgg_echo('places:checkin:success', array($entity->title)));
		forward(REFERER);
	}
}

register_error(elgg_echo('places:checkin:error', array($entity->title)));
forward(REFERER);
