<?php

namespace hypeJunction\Places;

use Elgg\IntegrationTestCase;

/**
 * Test bookmarking relationships and checkin annotations behavior that the
 * actions rely on.
 */
class RelationshipsTest extends IntegrationTestCase {

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
        $place->title = 'Rel Place';
        $place->save();
        return $place;
    }

    public function testBookmarkRelationshipCanBeAddedAndChecked(): void {
        $user = $this->createUser();
        $place = $this->makePlace($user->guid);

        $this->assertFalse((bool) check_entity_relationship($user->guid, 'bookmarked', $place->guid));

        add_entity_relationship($user->guid, 'bookmarked', $place->guid);
        $this->assertTrue((bool) check_entity_relationship($user->guid, 'bookmarked', $place->guid));

        remove_entity_relationship($user->guid, 'bookmarked', $place->guid);
        $this->assertFalse((bool) check_entity_relationship($user->guid, 'bookmarked', $place->guid));

        $place->delete();
    }

    public function testCheckinAnnotationIsRetrievable(): void {
        $user = $this->createUser();
        $place = $this->makePlace($user->guid);

        $place->checkIn($user->guid);

        $count = $place->getCheckins(['count' => true]);
        $this->assertGreaterThan(0, $count);

        $place->delete();
    }

    public function testFeaturedMetadataTogglesValue(): void {
        $user = $this->createUser();
        $place = $this->makePlace($user->guid);

        $this->assertEmpty($place->featured);
        $place->featured = true;
        $place->save();

        _elgg_services()->entityCache->delete($place->guid);
        $loaded = get_entity($place->guid);
        $this->assertNotEmpty($loaded->featured);

        $place->delete();
    }
}
