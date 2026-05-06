<?php

namespace hypeJunction\Places;

$query = get_input('query', []);
$location = get_input('location', []);
$radius = get_input('radius', HYPEMAPS_SEARCH_RADIUS);

$body .= '<div>';
$body .= '<label>' . elgg_echo('maps:filter:objects:attributes') . '</label>';
$body .= elgg_view('input/text', [
	'name' => 'query[object]',
	'value' => elgg_extract('object', $query)
]);
$body .= '</div>';

$body .= '<div>';
$body .= '<label>' . elgg_echo('maps:filter:objects:tags') . '</label>';
$body .= elgg_view('input/text', [
	'name' => 'query[tags]',
	'value' => elgg_extract('tags', $query)
]);
$body .= '</div>';

$user = elgg_get_logged_in_user_entity();

$body .= '<div>';
$body .= '<label>' . elgg_echo('maps:filter:objects:location') . '</label>';

$body .= '<div class="maps-filter-location">';
$body .= elgg_view('input/location', [
	'name' => 'location[find]',
	'value' => elgg_extract('find', $location),
]);
$body .= '</div>';
$body .= '</div>';

$body .= '<div>';
$body .= '<label>' . elgg_echo('maps:filter:objects:radius') . '</label>';
$body .= '<div class="maps-filter-radius">';
$metric_system = elgg_get_plugin_setting('metric_system', PLUGIN_ID);
$key = 'maps:proximity:' . $metric_system;
$body .= elgg_view('input/dropdown', [
	'name' => 'radius',
	'value' => $radius,
	'options_values' => [
		0 => elgg_echo('maps:filter:radius:none'),
		5 => elgg_echo($key, [5]),
		10 => elgg_echo($key, [10]),
		25 => elgg_echo($key, [25]),
		100 => elgg_echo($key, [100]),
		500 => elgg_echo($key, [500])
	]
]);
$body .= '</div>';
$body .= '</div>';

$footer .= elgg_view('input/submit', [
	'value' => elgg_echo('filter'),
]);

echo elgg_view_module('aside', elgg_echo('places:search'), $body, [
	'footer' => $footer
]);
