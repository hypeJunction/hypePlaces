<?php

namespace hypeJunction\Places;

$guid = get_input('guid');
$entity = get_entity($guid);

$title = $entity->title;

if (($entity) && ($entity->canEdit())) {
	if ($entity->delete()) {
		elgg_register_success_message(elgg_echo('places:delete:success', array($title)));
	} else {
		elgg_register_error_message(elgg_echo('places:delete:error', array($title)));
	}
} else {
	elgg_register_error_message(elgg_echo('places:delete:error', array($title)));
}

forward(REFERER);
