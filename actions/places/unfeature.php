<?php

$guid = get_input('guid');
$entity = get_entity($guid);

if (!$entity) {
	elgg_register_error_message(elgg_echo('places:error:not_found'));
	forward(REFERER);
}

if (!elgg_trigger_plugin_hook('permissions_check', 'places', array(
			'action' => 'feature',
			'entity' => $entity
				), true)) {
	elgg_register_error_message(elgg_echo('places:error:permissions'));
	forward(REFERER);
}

$entity->featured = false;

elgg_register_success_message(elgg_echo('places:unfeature:success', array($entity->title)));
forward(REFERER);
