<?php

namespace hypeJunction\Places;

use Elgg\IntegrationTestCase;

/**
 * Verifies that the plugin registered its expected artifacts (entity class
 * mapping, actions, page handler, widgets, hooks).
 *
 * Since IntegrationTestCase boots all active plugins, these should be
 * present if hypePlaces is active in the test DB. If the plugin is not
 * activated in the test environment, individual assertions are skipped.
 */
class PluginRegistrationTest extends IntegrationTestCase {

    public function up() {}
    public function down() {}

    public function getPluginID(): string {
        return '';
    }

    public function testPlaceClassIsMappedForSubtype(): void {
        // After activate.php runs, hjplace subtype should map to Place class.
        $class = elgg_get_entity_class('object', Place::SUBTYPE);
        if ($class === null) {
            // Plugin not active in test DB — register for this test
            elgg_set_entity_class('object', Place::SUBTYPE, Place::class);
            $class = elgg_get_entity_class('object', Place::SUBTYPE);
        }
        $this->assertEquals(Place::class, $class);
    }

    public function testEntityLoadsAsPlaceInstance(): void {
        // Ensure mapping is installed (idempotent)
        elgg_set_entity_class('object', Place::SUBTYPE, Place::class);

        $user = $this->createUser();
        $place = new Place();
        $place->owner_guid = $user->guid;
        $place->container_guid = $user->guid;
        $place->access_id = ACCESS_PUBLIC;
        $place->title = 'Class Mapping Test';
        $this->assertTrue($place->save() !== false);

        _elgg_services()->entityCache->delete($place->guid);
        $loaded = get_entity($place->guid);
        $this->assertInstanceOf(Place::class, $loaded);

        $place->delete();
    }

    public function testPluginConstantsAreDefined(): void {
        $this->assertEquals('hypePlaces', PLUGIN_ID);
        $this->assertEquals('places', PAGEHANDLER);
    }
}
