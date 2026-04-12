<?php

namespace hypeJunction\Places;

$guid = (int) get_input('guid');
$entity = get_entity($guid);

if (!$entity instanceof Place) {
	return elgg_error_response(elgg_echo('places:error:not_found'));
}

$user_guid = elgg_get_logged_in_user_guid();

if (!check_entity_relationship($user_guid, 'bookmarked', $guid)) {
	return elgg_error_response(elgg_echo('places:bookmark:remove:error'));
}

remove_entity_relationship($user_guid, 'bookmarked', $guid);
elgg_delete_river([
	'action_type' => 'stream:places:bookmark',
	'subject_guid' => $user_guid,
	'object_guid' => $entity->guid,
]);

return elgg_ok_response('', elgg_echo('places:bookmark:remove:success'), REFERRER);
