<?php

namespace hypeJunction\Places;

$guid = (int) get_input('guid');
$entity = get_entity($guid);

if (!$entity instanceof Place || !$entity->canEdit()) {
	return elgg_error_response(elgg_echo('places:delete:error', [$entity ? $entity->getDisplayName() : '']));
}

$title = $entity->getDisplayName();
$owner = $entity->getOwnerEntity();

if (!$entity->delete()) {
	return elgg_error_response(elgg_echo('places:delete:error', [$title]));
}

$forward = $owner instanceof \ElggUser ? elgg_generate_url('collection:object:hjplace:owner', ['username' => $owner->username]) : elgg_generate_url('collection:object:hjplace:all');

return elgg_ok_response('', elgg_echo('places:delete:success', [$title]), $forward);
