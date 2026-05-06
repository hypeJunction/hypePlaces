<?php

namespace hypeJunction\Places;

echo elgg_view_comments(elgg_get_page_owner_entity(), true, [
	'limit' => $vars['entity']->num_display
]);
