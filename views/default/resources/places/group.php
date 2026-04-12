<?php

$guid = (int) elgg_extract('guid', $vars);
$group = get_entity($guid);
if (!$group instanceof \ElggGroup) {
	throw new \Elgg\Exceptions\Http\EntityNotFoundException();
}

elgg_set_page_owner_guid($group->guid);
elgg_group_gatekeeper();

if ($group->canWriteToContainer(0, 'object', \hypeJunction\Places\Place::SUBTYPE)) {
	elgg_register_title_button('places', 'add');
}

elgg_push_breadcrumb(elgg_echo('places'), elgg_generate_url('collection:object:hjplace:all'));
elgg_push_breadcrumb($group->getDisplayName());

$title = elgg_echo('places:group');
$content = elgg_view('framework/places/group', ['entity' => $group]);

echo elgg_view_page($title, [
	'content' => $content,
	'title' => $title,
]);
