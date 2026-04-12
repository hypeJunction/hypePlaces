<?php

$guid = (int) elgg_extract('guid', $vars);
$entity = get_entity($guid);
if (!$entity instanceof \hypeJunction\Places\Place) {
	throw new \Elgg\Exceptions\Http\EntityNotFoundException();
}

$container = $entity->getContainerEntity();
if ($container instanceof \ElggGroup) {
	elgg_set_page_owner_guid($container->guid);
	elgg_group_gatekeeper();
	elgg_push_breadcrumb($container->getDisplayName(), elgg_generate_url('collection:object:hjplace:group', [
		'guid' => $container->guid,
	]));
} else {
	$owner = $entity->getOwnerEntity();
	if ($owner instanceof \ElggUser) {
		elgg_push_breadcrumb($owner->getDisplayName(), elgg_generate_url('collection:object:hjplace:owner', [
			'username' => $owner->username,
		]));
	}
}

elgg_push_breadcrumb($entity->getDisplayName());

$title = $entity->getDisplayName();
$content = elgg_view('framework/places/profile', ['entity' => $entity]);
$sidebar = elgg_view('framework/places/sidebar', ['entity' => $entity]);

echo elgg_view_page($title, [
	'content' => $content,
	'sidebar' => $sidebar,
	'title' => $title,
]);
