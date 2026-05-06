<?php

$username = elgg_extract('username', $vars);
$user = $username ? get_user_by_username($username) : elgg_get_logged_in_user_entity();
if (!$user instanceof \ElggUser || !$user->canEdit()) {
	throw new \Elgg\Exceptions\Http\EntityPermissionsException();
}

elgg_set_page_owner_guid($user->guid);

$title = (elgg_get_logged_in_user_guid() == $user->guid) ? elgg_echo('places:bookmarked:mine') : elgg_echo('places:bookmarked:owner', [$user->getDisplayName()]);

elgg_push_breadcrumb(elgg_echo('places'), elgg_generate_url('collection:object:hjplace:all'));
elgg_push_breadcrumb($user->getDisplayName());
elgg_push_breadcrumb($title);

$content = elgg_view('framework/places/list', ['filter_context' => 'bookmarked']);
$filter_view = elgg_view('framework/places/filter', ['filter_context' => 'bookmarked']);
$sidebar = elgg_view('framework/places/sidebar', ['filter_context' => 'bookmarked']);

echo elgg_view_page($title, [
	'content' => $content,
	'filter' => $filter_view,
	'sidebar' => $sidebar,
	'title' => $title,
]);
