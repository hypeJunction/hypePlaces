<?php

elgg_gatekeeper();

$guid = (int) elgg_extract('guid', $vars);
$entity = get_entity($guid);
if (!$entity instanceof \hypeJunction\Places\Place || !$entity->canEdit()) {
	throw new \Elgg\Exceptions\Http\EntityPermissionsException();
}

elgg_push_breadcrumb(elgg_echo('places'), elgg_generate_url('collection:object:hjplace:all'));
elgg_push_breadcrumb($entity->getDisplayName(), $entity->getURL());
elgg_push_breadcrumb(elgg_echo('edit'));

$title = elgg_echo('places:edit', [$entity->getDisplayName()]);
$content = elgg_view('framework/places/edit', ['entity' => $entity]);
$sidebar = elgg_view('framework/places/sidebar', ['entity' => $entity]);

echo elgg_view_page($title, [
	'content' => $content,
	'sidebar' => $sidebar,
	'title' => $title,
]);
