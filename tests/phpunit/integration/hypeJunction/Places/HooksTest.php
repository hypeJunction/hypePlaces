<?php

namespace hypeJunction\Places;

use Elgg\Event;
use Elgg\IntegrationTestCase;
use ElggMenuItem;

class HooksTest extends IntegrationTestCase {

    public function up() {}
    public function down() {}

    public function getPluginID(): string {
        return '';
    }

    private function makeEvent(string $name, string $type, $value, array $params = []): Event {
        return new Event(elgg(), $name, $type, $value, $params);
    }

    private function makePlace(\ElggUser $user): Place {
        _elgg_services()->session_manager->setLoggedInUser($user);
        $place = new Place();
        $place->owner_guid = $user->guid;
        $place->container_guid = $user->guid;
        $place->access_id = ACCESS_PUBLIC;
        $place->title = 'Hook Place';
        $place->save();
        return $place;
    }

    public function testUrlHandlerReturnsPlaceUrl(): void {
        $user = $this->createUser();
        $place = $this->makePlace($user);

        $event = $this->makeEvent('entity:url', 'object', '', ['entity' => $place]);
        $url = url_handler($event);
        $this->assertIsString($url);
        $this->assertStringContainsString('places/view/' . $place->guid, $url);

        $place->delete();
        _elgg_services()->session_manager->removeLoggedInUser();
    }

    public function testUrlHandlerPassesThroughForNonPlace(): void {
        $user = $this->createUser();
        _elgg_services()->session_manager->setLoggedInUser($user);
        $obj = new \ElggObject();
        $obj->setSubtype('blog');
        $obj->owner_guid = $user->guid;
        $obj->container_guid = $user->guid;
        $obj->access_id = ACCESS_PUBLIC;
        $obj->title = 'Not a place';
        $obj->save();

        $event = $this->makeEvent('entity:url', 'object', 'original-url', ['entity' => $obj]);
        $result = url_handler($event);
        $this->assertEquals('original-url', $result);

        $obj->delete();
        _elgg_services()->session_manager->removeLoggedInUser();
    }

    public function testEntityIconUrlReturnsDefaultWhenNoIcontime(): void {
        $user = $this->createUser();
        $place = $this->makePlace($user);

        $event = $this->makeEvent('entity:icon:url', 'object', '', [
            'entity' => $place,
            'size' => 'medium',
        ]);
        $result = entity_icon_url($event);
        $this->assertStringContainsString('hypePlaces/graphics/icon/medium.png', $result);

        $place->delete();
        _elgg_services()->session_manager->removeLoggedInUser();
    }

    public function testEntityIconSizesReturnsConfigForPlace(): void {
        $user = $this->createUser();
        $place = $this->makePlace($user);

        $event = $this->makeEvent('entity:icon:sizes', 'object', [], ['entity' => $place]);
        $result = entity_icon_sizes($event);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('125', $result);
        $this->assertArrayHasKey('325x200', $result);
        $this->assertEquals(125, $result['125']['w']);
        $this->assertEquals(325, $result['325x200']['w']);

        $place->delete();
        _elgg_services()->session_manager->removeLoggedInUser();
    }

    public function testEntityIconSizesPassesThroughForNonPlace(): void {
        $existing = ['topbar' => ['w' => 16, 'h' => 16]];
        $event = $this->makeEvent('entity:icon:sizes', 'object', $existing, [
            'entity' => elgg_get_site_entity(),
        ]);
        $result = entity_icon_sizes($event);
        $this->assertEquals($existing, $result);
    }

    public function testSetupSiteSearchMapsRegistersPlacesMap(): void {
        $event = $this->makeEvent('search:site', 'maps', [], []);
        $result = setup_site_search_maps($event);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('places', $result);
        $this->assertEquals('object', $result['places']['options']['types']);
        $this->assertEquals(Place::SUBTYPE, $result['places']['options']['subtypes']);
    }

    public function testEntityMenuSetupIgnoresNonPlace(): void {
        $event = $this->makeEvent('register', 'menu:entity', [], [
            'entity' => elgg_get_site_entity(),
        ]);
        $result = entity_menu_setup($event);
        $this->assertEquals([], $result);
    }

    public function testWidgetLayoutPermissionsCheckPassesThrough(): void {
        $event = $this->makeEvent('permissions_check', 'widget_layout', false, []);
        $result = widget_layout_permissions_check($event);
        $this->assertFalse($result);
    }
}
