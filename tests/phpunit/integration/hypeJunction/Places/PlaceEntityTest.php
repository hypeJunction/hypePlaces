<?php

namespace hypeJunction\Places;

use Elgg\IntegrationTestCase;
use ElggObject;

class PlaceEntityTest extends IntegrationTestCase {

    public function up() {
    }

    public function down() {
    }

    public function getPluginID(): string {
        return '';
    }

    public function testPlaceClassExists(): void {
        $this->assertTrue(class_exists(Place::class));
        $this->assertEquals('hjplace', Place::SUBTYPE);
    }

    public function testPlaceInitializesSubtype(): void {
        $place = new Place();
        $this->assertEquals('hjplace', $place->getSubtype());
    }

    public function testPlaceIsElggObject(): void {
        $place = new Place();
        $this->assertInstanceOf(ElggObject::class, $place);
    }

    public function testPlaceCanBeSaved(): void {
        $user = $this->createUser();
        _elgg_services()->session_manager->setLoggedInUser($user);
        $place = new Place();
        $place->owner_guid = $user->guid;
        $place->container_guid = $user->guid;
        $place->access_id = ACCESS_PUBLIC;
        $place->title = 'Test Place';
        $place->description = 'A test place';
        $this->assertTrue($place->save() !== false);
        $this->assertGreaterThan(0, $place->guid);

        $loaded = get_entity($place->guid);
        $this->assertInstanceOf(Place::class, $loaded);
        $this->assertEquals('hjplace', $loaded->getSubtype());
        $this->assertEquals('Test Place', $loaded->title);

        $place->delete();
        _elgg_services()->session_manager->removeLoggedInUser();
    }

    public function testPlacePersistsAddressMetadata(): void {
        $user = $this->createUser();
        _elgg_services()->session_manager->setLoggedInUser($user);
        $place = new Place();
        $place->owner_guid = $user->guid;
        $place->container_guid = $user->guid;
        $place->access_id = ACCESS_PUBLIC;
        $place->title = 'Addressed Place';
        $place->street_address = '123 Main St';
        $place->extended_address = 'Suite 1';
        $place->locality = 'Townsville';
        $place->region = 'Region';
        $place->postal_code = '12345';
        $place->country_code = 'US';
        $place->country = 'United States';
        $place->phone = '555-0100';
        $place->website = 'https://example.com';
        $place->twitter = 'example';
        $this->assertTrue($place->save() !== false);

        _elgg_services()->entityCache->delete($place->guid);
        $loaded = get_entity($place->guid);
        $this->assertInstanceOf(Place::class, $loaded);

        $address = $loaded->getAddress();
        $this->assertEquals('123 Main St', $address['street_address']);
        $this->assertEquals('Suite 1', $address['extended_address']);
        $this->assertEquals('Townsville', $address['locality']);
        $this->assertEquals('Region', $address['region']);
        $this->assertEquals('12345', $address['postal_code']);
        $this->assertEquals('US', $address['country_code']);
        $this->assertEquals('United States', $address['country']);

        $this->assertEquals('555-0100', $loaded->phone);
        $this->assertEquals('https://example.com', $loaded->website);
        $this->assertEquals('example', $loaded->twitter);

        $place->delete();
        _elgg_services()->session_manager->removeLoggedInUser();
    }

    public function testGetAddressReturnsAllKeys(): void {
        $place = new Place();
        $address = $place->getAddress();
        $expected_keys = [
            'street_address', 'extended_address', 'locality', 'region',
            'postal_code', 'country_code', 'country',
        ];
        foreach ($expected_keys as $key) {
            $this->assertArrayHasKey($key, $address);
        }
    }

    public function testGetCheckinDurationDefaultIsOneHour(): void {
        $place = new Place();
        $duration = $place->getCheckinDuration();
        $this->assertIsNumeric($duration);
        $this->assertGreaterThan(0, $duration);
    }

    public function testCheckInCreatesAnnotation(): void {
        $user = $this->createUser();
        _elgg_services()->session_manager->setLoggedInUser($user);
        $place = new Place();
        $place->owner_guid = $user->guid;
        $place->container_guid = $user->guid;
        $place->access_id = ACCESS_PUBLIC;
        $place->title = 'Checkin Place';
        $this->assertTrue($place->save() !== false);

        $id = $place->checkIn($user->guid);
        $this->assertIsNumeric($id);
        $this->assertGreaterThan(0, $id);

        $count = $place->isCheckedIn($user->guid);
        $this->assertGreaterThan(0, $count);

        $place->delete();
        _elgg_services()->session_manager->removeLoggedInUser();
    }

    public function testCheckInWithoutUserReturnsFalseWhenNotLoggedIn(): void {
        $place = new Place();
        $place->owner_guid = 0;
        $place->container_guid = elgg_get_site_entity()->guid;
        $place->access_id = ACCESS_PUBLIC;
        $place->title = 'Anonymous Checkin';
        $this->assertFalse($place->checkIn(0));
    }

    public function testOwnerCanEditPlace(): void {
        $owner = $this->createUser();
        $other = $this->createUser();
        _elgg_services()->session_manager->setLoggedInUser($owner);
        $place = new Place();
        $place->owner_guid = $owner->guid;
        $place->container_guid = $owner->guid;
        $place->access_id = ACCESS_PUBLIC;
        $place->title = 'Perm Test';
        $this->assertTrue($place->save() !== false);

        $this->assertTrue($place->canEdit($owner->guid));
        $this->assertFalse($place->canEdit($other->guid));

        $place->delete();
        _elgg_services()->session_manager->removeLoggedInUser();
    }
}
