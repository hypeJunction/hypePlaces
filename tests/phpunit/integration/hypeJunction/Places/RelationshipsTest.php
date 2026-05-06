<?php

namespace hypeJunction\Places;

use Elgg\IntegrationTestCase;

class RelationshipsTest extends IntegrationTestCase {

    public function up() {}
    public function down() {}

    /**
     * @return string
     */
    public function getPluginID(): string {
        return '';
    }

    /**
     * @param ElggUser $user
     * @return Place
     */
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

    /**
     * @return void
     */
    public function testBookmarkRelationshipCanBeAddedAndChecked(): void {
        $user = $this->createUser();
        $place = $this->makePlace($user);

        $this->assertFalse($user->hasRelationship($place->guid, 'bookmarked'));

        $user->addRelationship($place->guid, 'bookmarked');
        $this->assertTrue($user->hasRelationship($place->guid, 'bookmarked'));

        $user->removeRelationship($place->guid, 'bookmarked');
        $this->assertFalse($user->hasRelationship($place->guid, 'bookmarked'));

        $place->delete();
        _elgg_services()->session_manager->removeLoggedInUser();
    }

    /**
     * @return void
     */
    public function testCheckinAnnotationIsRetrievable(): void {
        $user = $this->createUser();
        $place = $this->makePlace($user);

        $place->checkIn($user->guid);

        $count = $place->getCheckins(['count' => true]);
        $this->assertGreaterThan(0, $count);

        $place->delete();
        _elgg_services()->session_manager->removeLoggedInUser();
    }

    /**
     * @return void
     */
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
