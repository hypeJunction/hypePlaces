<?php

elgg_gatekeeper();

$container_guid = (int) elgg_extract('container_guid', $vars);
$container = get_entity($container_guid);
if (!$container instanceof \ElggEntity || !$container->canWriteToContainer(0, 'object', \hypeJunction\Places\Place::SUBTYPE)) {
	throw new \Elgg\Exceptions\Http\EntityPermissionsException();
}

elgg_push_breadcrumb(elgg_echo('places'), elgg_generate_url('collection:object:hjplace:all'));
elgg_push_breadcrumb(elgg_echo('places:create'));

$title = elgg_echo('places:create');
$content = elgg_view('framework/places/edit', ['container' => $container]);
$sidebar = elgg_view('framework/places/sidebar', ['container' => $container]);

echo elgg_view_page($title, [
	'content' => $content,
	'sidebar' => $sidebar,
	'title' => $title,
]);
