<?php

$filter = elgg_extract('filter', $vars, 'all');

elgg_push_breadcrumb(elgg_echo('places'));
elgg_register_title_button('places', 'add');

$title = elgg_echo("places:$filter");

$content = elgg_view('framework/places/list', [
	'filter_context' => $filter,
]);
$filter_view = elgg_view('framework/places/filter', ['filter_context' => $filter]);
$sidebar = elgg_view('framework/places/sidebar', ['filter_context' => $filter]);

echo elgg_view_page($title, [
	'content' => $content,
	'filter' => $filter_view,
	'sidebar' => $sidebar,
	'title' => $title,
]);
