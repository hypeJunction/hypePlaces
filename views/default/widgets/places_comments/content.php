<?php

namespace hypeJunction\Places;

echo elgg_view_comments(elgg_get_page_owner_entity(), true, array(
	'limit' => $vars['entity']->num_display
));
