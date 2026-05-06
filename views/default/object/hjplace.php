<?php

namespace hypeJunction\Places;

$entity = elgg_extract('entity', $vars);
$full = elgg_extract('full_view', $vars);

$title = elgg_view('output/url', [
	'text' => $entity->title,
	'href' => $entity->getURL(),
]);

if (!elgg_in_context('widgets')) {
	$metadata = elgg_view_menu('entity', [
		'entity' => $entity,
		'sort_by' => 'priority',
		'class' => 'elgg-menu-hz',
		'handler' => PAGEHANDLER,
	]);
}

if ($entity->description && $full) {
	$description_value = $full ? $entity->description : elgg_get_excerpt($entity->description);
	$description = elgg_view('output/longtext', ['value' => $description_value]);
}

$subtitle[] = elgg_view('output/places/location', [
	'entity' => $entity,
]);

if ($entity->phone) {
	$subtitle[] = $entity->phone;
}

if ($entity->website) {
	$subtitle[] = elgg_view('output/url', [
		'text' => elgg_echo('places:place:website'),
		'href' => $entity->website,
	]);
}

if ($entity->twitter) {
	$twitter = str_replace('@@', '@', "@$entity->twitter");
	$subtitle[] = elgg_view('output/url', [
		'text' => $twitter,
		'href' => "https://twitter.com/$twitter",
		'target' => '_blank',
	]);
}

if ($entity->specialties) {
	$subtitle[] = elgg_view('output/tags', [
		'value' => $entity->specialties,
		'icon_class' => 'places-icon-specialties',
	]);
}

$subtitle = implode('', array_map(function($elem) {
			return "<div class=\"places-meta\">$elem</div>";
}, $subtitle));

if (elgg_in_context('gallery')) {
	$icon = elgg_view_entity_icon($entity, '325x200');
	if ($entity->featured) {
		$ribbon = '<div class="places-featured-ribbon"><div class="banner"><div class="text">' . elgg_echo('places:featured') . '</div></div></div>';
	}

	echo elgg_view_module('aside', $title, $ribbon . $icon . $subtitle, [
		'footer' => $metadata
	]);
} else {
	$icon = elgg_view_entity_icon($entity, 'medium');
	$summary = elgg_view('object/elements/summary', [
		'entity' => $entity,
		'title' => $title,
		'subtitle' => $subtitle,
		'metadata' => $metadata,
		'content' => $description,
	]);
	$icon .= $interactions;
	
	if ($full) {
		echo elgg_view('object/elements/full', [
			'icon' => $icon,
			'entity' => $entity,
			'summary' => $summary,
				//'body' => elgg_view('breeds/animal/breeds', $vars)
		]);
	} else {
		echo elgg_view_image_block($icon, $summary);
	}
}
