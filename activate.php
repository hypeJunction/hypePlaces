<?php

namespace hypeJunction\Places;

// Load plugin-local autoloader if it exists
if (file_exists(__DIR__ . '/vendors/autoload.php')) {
    require_once __DIR__ . '/vendors/autoload.php';
}

elgg_set_entity_class('object', Place::SUBTYPE, Place::class);
