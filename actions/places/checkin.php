<?php

namespace hypeJunction\Places;

$guid = (int) get_input('guid');
$entity = get_entity($guid);

if (!$entity instanceof Place) {
	return elgg_error_response(elgg_echo('places:error:not_found'));
}

if ($entity->isCheckedIn()) {
	return elgg_error_response(elgg_echo('places:checkin:error', [$entity->getDisplayName()]));
}

$id = $entity->checkIn();
if (!$id) {
	return elgg_error_response(elgg_echo('places:checkin:error', [$entity->getDisplayName()]));
}

elgg_create_river_item([
	'view' => 'river/object/hjplace/checkin',
	'action_type' => 'stream:places:checkin',
	'subject_guid' => elgg_get_logged_in_user_guid(),
	'object_guid' => $entity->guid,
	'access_id' => $entity->access_id,
	'annotation_id' => $id,
]);

return elgg_ok_response('', elgg_echo('places:checkin:success', [$entity->getDisplayName()]), REFERRER);
