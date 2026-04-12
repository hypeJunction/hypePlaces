<?php

namespace hypeJunction\Places;

use Elgg\IntegrationTestCase;
use ElggMenuItem;

/**
 * Tests for the plugin hook handlers defined in lib/hooks.php.
 *
 * These tests call handler functions directly with synthetic params to
 * verify return values. They do not rely on any registered handler state
 * so they work in isolation.
 */
class HooksTest extends IntegrationTestCase {

    public function up() {}
    public function down() {}

    public function getPluginID(): string {
        return '';
    }

    private function makePlace(int $owner_guid): Place {
        $place = new Place();
        $place->owner_guid = $owner_guid;
        $place->container_guid = $owner_guid;
        $place->access_id = ACCESS_PUBLIC;
        $place->title = 'Hook Place';
        $place->save();
        return $place;
    }

    public function testUrlHandlerReturnsPlaceUrl(): void {
        $user = $this->createUser();
        $place = $this->makePlace($user->guid);

        $url = url_handler('entity:url', 'object', '', ['entity' => $place]);
        $this->assertIsString($url);
        $this->assertStringContainsString('places/profile/' . $place->guid, $url);

        $place->delete();
    }

    public function testUrlHandlerPassesThroughForNonPlace(): void {
        $user = $this->createUser();
        $obj = new \ElggObject();
        $obj->setSubtype('blog');
        $obj->owner_guid = $user->guid;
        $obj->container_guid = $user->guid;
        $obj->access_id = ACCESS_PUBLIC;
        $obj->title = 'Not a place';
        $obj->save();

        $result = url_handler('entity:url', 'object', 'original-url', ['entity' => $obj]);
        $this->assertEquals('original-url', $result);

        $obj->delete();
    }

    public function testEntityIconUrlReturnsDefaultWhenNoIcontime(): void {
        $user = $this->createUser();
        $place = $this->makePlace($user->guid);

        $result = entity_icon_url('entity:icon:url', 'object', '', [
            'entity' => $place,
            'size' => 'medium',
        ]);
        $this->assertStringContainsString('hypePlaces/graphics/icon/medium.png', $result);

        $place->delete();
    }

    public function testEntityIconSizesReturnsConfigForPlace(): void {
        $user = $this->createUser();
        $place = $this->makePlace($user->guid);

        $result = entity_icon_sizes('entity:icon:sizes', 'object', [], ['entity' => $place]);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('125', $result);
        $this->assertArrayHasKey('325x200', $result);
        $this->assertEquals(125, $result['125']['w']);
        $this->assertEquals(325, $result['325x200']['w']);

        $place->delete();
    }

    public function testEntityIconSizesPassesThroughForNonPlace(): void {
        $existing = ['topbar' => ['w' => 16, 'h' => 16]];
        $result = entity_icon_sizes('entity:icon:sizes', 'object', $existing, [
            'entity' => elgg_get_site_entity(),
        ]);
        $this->assertEquals($existing, $result);
    }

    public function testSetupSiteSearchMapsRegistersPlacesMap(): void {
        $result = setup_site_search_maps('search:site', 'maps', [], []);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('places', $result);
        $this->assertEquals('object', $result['places']['options']['types']);
        $this->assertEquals(Place::SUBTYPE, $result['places']['options']['subtypes']);
    }

    public function testEntityMenuSetupIgnoresNonPlace(): void {
        $menu = [];
        $result = entity_menu_setup('register', 'menu:entity', $menu, [
            'entity' => elgg_get_site_entity(),
        ]);
        $this->assertEquals($menu, $result);
    }

    public function testWidgetLayoutPermissionsCheckPassesThrough(): void {
        $result = widget_layout_permissions_check('permissions_check', 'widget_layout', false, []);
        $this->assertFalse($result);
    }
}
