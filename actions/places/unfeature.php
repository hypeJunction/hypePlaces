<?php

namespace hypeJunction\Places;

$guid = (int) get_input('guid');
$entity = get_entity($guid);

if (!$entity instanceof Place) {
	return elgg_error_response(elgg_echo('places:error:not_found'));
}

$entity->featured = false;

return elgg_ok_response('', elgg_echo('places:unfeature:success', [$entity->getDisplayName()]), REFERRER);
