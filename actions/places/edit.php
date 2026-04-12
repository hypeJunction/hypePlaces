<?php

namespace hypeJunction\Places;

elgg_make_sticky_form('places/edit');

$guid = (int) get_input('guid');
$container_guid = (int) get_input('container_guid');
$title = get_input('title');
$description = get_input('description');
$access_id = get_input('access_id');
$tags = get_input('tags');
$specialties = get_input('specialties');
$markertype = get_input('markertype', 'default');

$address = (array) get_input('address', []);
$street_address = elgg_extract('street_address', $address);
$extended_address = elgg_extract('extended_address', $address);
$locality = elgg_extract('locality', $address);
$region = elgg_extract('region', $address);
$postal_code = elgg_extract('postal_code', $address);
$country_iso = elgg_extract('country_code', $address);
$country = elgg_extract('country', $address);

if (!$title || !$street_address || !$locality || !$postal_code || (!$country_iso && !$country)) {
	return elgg_error_response(elgg_echo('places:place:edit:error:required_field_empty'));
}

$entity = $guid ? get_entity($guid) : null;
$container = $container_guid ? get_entity($container_guid) : null;

$new = false;
if (!$entity instanceof Place) {
	$new = true;
	$entity = new Place();
	if ($container instanceof \ElggEntity) {
		$entity->container_guid = $container->guid;
	}
}

$entity->access_id = ($access_id === '' && $container instanceof \ElggEntity) ? $container->access_id : (int) $access_id;
$entity->title = $title;
$entity->description = $description;
$entity->tags = elgg_string_to_array((string) $tags);
$entity->specialties = elgg_string_to_array((string) $specialties);
$entity->markertype = $markertype;
$entity->street_address = $street_address;
$entity->extended_address = $extended_address;
$entity->locality = $locality;
$entity->region = $region;
$entity->postal_code = $postal_code;
$entity->country_code = $country_iso;
if ($entity->country_code && is_callable(['hypeJunction\\Geo\\Countries', 'getCountries'])) {
	$countries = call_user_func(['hypeJunction\\Geo\\Countries', 'getCountries'], 'iso', 'name', 'name');
	$country = $countries[$country_iso] ?? $country;
}
$entity->country = $country;
$entity->phone = get_input('phone');
$entity->website = get_input('website');
$entity->twitter = get_input('twitter');

$tools = (array) get_input('tools', []);
$entity->checkins = elgg_extract('checkins', $tools, false);

if (!$entity->save()) {
	return elgg_error_response(elgg_echo('places:edit:error'));
}

$entity->location = implode(', ', array_filter([
	$street_address, $extended_address, $locality, $region, $postal_code, $country,
]));

if (!empty($_FILES['icon']['name']) && $_FILES['icon']['error'] == UPLOAD_ERR_OK) {
	$entity->saveIconFromUploadedFile('icon');
}

if ($new) {
	elgg_create_river_item([
		'view' => 'river/object/hjplace/create',
		'action_type' => 'create',
		'subject_guid' => $entity->owner_guid,
		'object_guid' => $entity->guid,
	]);
}

elgg_clear_sticky_form('places/edit');

return elgg_ok_response('', elgg_echo('places:edit:success', [$entity->getDisplayName()]), $entity->getURL());
