<?php

namespace hypeJunction\Places;

require_once __DIR__ . '/vendors/autoload.php';
$subtypes = array(Place::SUBTYPE => get_class(new Place()));
foreach ($subtypes as $subtype => $class) {
    if (get_subtype_id('object', $subtype)) {
        elgg_set_entity_class('object', $subtype, $class);
    } else {
        elgg_set_entity_class('object', $subtype, $class);
    }
}