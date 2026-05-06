<?php

namespace hypeJunction\Places;

use Elgg\IntegrationTestCase;

class RelationshipsTest extends IntegrationTestCase {

    public function up() {}
    public function down() {}

    public function getPluginID(): string {
        return '';
    }

    private function makePlace(\ElggUser $user): Place {
        _elgg_services()->session_manager->setLoggedInUser($user);
        $place = new Place();
        $place->owner_guid = $user->guid;
        $place->container_guid = $user->guid;
        $place->access_id = ACCESS_PUBLIC;
        $place->title = 'Rel Place';
        $place->save();
        return $place;
    }

    public function testBookmarkRelationshipCanBeAddedAndChecked(): void {
        $user = $this->createUser();
        $place = $this->makePlace($user);

        $this->assertFalse((bool) check_entity_relationship($user->guid, 'bookmarked', $place->guid));

        add_entity_relationship($user->guid, 'bookmarked', $place->guid);
        $this->assertTrue((bool) check_entity_relationship($user->guid, 'bookmarked', $place->guid));

        remove_entity_relationship($user->guid, 'bookmarked', $place->guid);
        $this->assertFalse((bool) check_entity_relationship($user->guid, 'bookmarked', $place->guid));

        $place->delete();
        _elgg_services()->session_manager->removeLoggedInUser();
    }

    public function testCheckinAnnotationIsRetrievable(): void {
        $user = $this->createUser();
        $place = $this->makePlace($user);

        $place->checkIn($user->guid);

        $count = $place->getCheckins(['count' => true]);
        $this->assertGreaterThan(0, $count);

        $place->delete();
        _elgg_services()->session_manager->removeLoggedInUser();
    }

    public function testFeaturedMetadataTogglesValue(): void {
        $user = $this->createUser();
        $place = $this->makePlace($user);

        $this->assertEmpty($place->featured);
        $place->featured = true;
        $place->save();

        _elgg_services()->entityCache->delete($place->guid);
        $loaded = get_entity($place->guid);
        $this->assertInstanceOf(Place::class, $loaded);
        $this->assertNotEmpty($loaded->featured);

        $place->delete();
        _elgg_services()->session_manager->removeLoggedInUser();
    }
}
