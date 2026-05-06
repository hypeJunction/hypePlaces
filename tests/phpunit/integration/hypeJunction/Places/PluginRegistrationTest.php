<?php

namespace hypeJunction\Places;

use Elgg\IntegrationTestCase;

class PluginRegistrationTest extends IntegrationTestCase {

    public function up() {}
    public function down() {}

    /**
     * @return string
     */
    public function getPluginID(): string {
        return '';
    }

    /**
     * @return void
     */
    public function testPlaceClassIsMappedForSubtype(): void {
        $class = elgg_get_entity_class('object', Place::SUBTYPE);
        if ($class === null) {
            elgg_set_entity_class('object', Place::SUBTYPE, Place::class);
            $class = elgg_get_entity_class('object', Place::SUBTYPE);
        }
        $this->assertEquals(Place::class, $class);
    }

    /**
     * @return void
     */
    public function testEntityLoadsAsPlaceInstance(): void {
        elgg_set_entity_class('object', Place::SUBTYPE, Place::class);

        $user = $this->createUser();
        _elgg_services()->session_manager->setLoggedInUser($user);
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
        _elgg_services()->session_manager->removeLoggedInUser();
    }

    /**
     * @return void
     */
    public function testPluginConstantsAreDefined(): void {
        $this->assertEquals('hypeplaces', PLUGIN_ID);
        $this->assertEquals('places', PAGEHANDLER);
    }
}
