<?php

$username = elgg_extract('username', $vars);
$user = get_user_by_username($username);
if (!$user) {
	throw new \Elgg\Exceptions\Http\EntityNotFoundException();
}

elgg_set_page_owner_guid($user->guid);

$is_mine = elgg_get_logged_in_user_guid() == $user->guid;
if ($is_mine) {
	elgg_register_title_button('places', 'add');
	$title = elgg_echo('places:mine');
} else {
	$title = elgg_echo('places:owner', [$user->getDisplayName()]);
}

elgg_push_breadcrumb(elgg_echo('places'), elgg_generate_url('collection:object:hjplace:all'));
elgg_push_breadcrumb($user->getDisplayName());

$content = elgg_view('framework/places/list', ['filter_context' => 'owner']);
$filter_view = elgg_view('framework/places/filter', ['filter_context' => 'owner']);
$sidebar = elgg_view('framework/places/sidebar', ['filter_context' => 'owner']);

echo elgg_view_page($title, [
	'content' => $content,
	'filter' => $filter_view,
	'sidebar' => $sidebar,
	'title' => $title,
]);
