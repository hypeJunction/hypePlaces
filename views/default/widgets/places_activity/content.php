<?php

namespace hypeJunction\Places;

$options = array(
	'limit' => $vars['entity']->num_display,
	'pagination' => false,
	'object_guids' => elgg_get_page_owner_guid(),
);

$content = elgg_list_river($options);
if (!$content) {
	$content = elgg_echo('river:none');
}

echo $content;
