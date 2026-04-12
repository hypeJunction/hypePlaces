<?php

namespace hypeJunction\Places;

$guid = (int) get_input('guid');
$entity = get_entity($guid);

if (!$entity instanceof Place) {
	return elgg_error_response(elgg_echo('places:error:not_found'));
}

if (!$entity->isCheckedIn()) {
	return elgg_error_response(elgg_echo('places:checkout:error', [$entity->getDisplayName()]));
}

$checkins = elgg_get_annotations([
	'guids' => $entity->guid,
	'annotation_owner_guids' => elgg_get_logged_in_user_guid(),
	'annotation_names' => 'checkin',
	'annotation_created_time_lower' => time() - $entity->getCheckinDuration(),
	'limit' => false,
]);

foreach ($checkins as $ci) {
	$ci->delete();
}

return elgg_ok_response('', elgg_echo('places:checkout:success', [$entity->getDisplayName()]), REFERRER);
