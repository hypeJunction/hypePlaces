<?php

namespace hypeJunction\Places;

use Elgg\Event;
use Elgg\IntegrationTestCase;

/**
 * Behavioural coverage for the menu handlers touched by the 7.x removed-fn sweep
 * (commit 9e5f1c9): interactions_menu_setup now resolves the actor via
 * elgg_get_logged_in_user_entity()->hasRelationship() instead of the removed
 * elgg_get_logged_in_user()/check_entity_relationship() pair, and entity_menu_setup
 * gates the admin-only feature item. Exercises the handlers on a booted Elgg 7.
 */
class InteractionsMenuTest extends IntegrationTestCase {

    public function up() {}
    public function down() {}

    /**
     * @return string
     */
    public function getPluginID(): string {
        return '';
    }

    /**
     * @param mixed $value
     * @param array $params
     * @return Event
     */
    private function makeEvent($value, array $params): Event {
        return new Event(elgg(), 'register', 'menu:entity', $value, $params);
    }

    /**
     * @param \ElggUser $user
     * @return Place
     */
    private function makePlace(\ElggUser $user): Place {
        $place = new Place();
        $place->owner_guid = $user->guid;
        $place->container_guid = $user->guid;
        $place->access_id = ACCESS_PUBLIC;
        $place->title = 'Interaction Place';
        $place->save();
        return $place;
    }

    /**
     * @param \ElggMenuItem[] $items
     * @param string $name
     * @return \ElggMenuItem|null
     */
    private function findItem(array $items, string $name): ?\ElggMenuItem {
        foreach ($items as $item) {
            if ($item->getName() === $name) {
                return $item;
            }
        }
        return null;
    }

    /**
     * @return void
     */
    public function testBookmarkItemTogglesOnRelationship(): void {
        $user = $this->createUser();
        _elgg_services()->session_manager->setLoggedInUser($user);
        $place = $this->makePlace($user);

        // No relationship yet → item points at the bookmark action.
        $result = interactions_menu_setup($this->makeEvent([], ['entity' => $place]));
        $bookmark = $this->findItem($result, 'bookmark');
        $this->assertInstanceOf(\ElggMenuItem::class, $bookmark);
        $this->assertStringContainsString('action/places/bookmark', $bookmark->getHref());

        // After bookmarking, the same handler must flip to the unbookmark action.
        $user->addRelationship($place->guid, 'bookmarked');
        $result = interactions_menu_setup($this->makeEvent([], ['entity' => $place]));
        $bookmark = $this->findItem($result, 'bookmark');
        $this->assertInstanceOf(\ElggMenuItem::class, $bookmark);
        $this->assertStringContainsString('action/places/unbookmark', $bookmark->getHref());

        $place->delete();
        _elgg_services()->session_manager->removeLoggedInUser();
    }

    /**
     * @return void
     */
    public function testCheckinItemOnlyWhenCheckinsEnabled(): void {
        $user = $this->createUser();
        _elgg_services()->session_manager->setLoggedInUser($user);
        $place = $this->makePlace($user);

        // checkins disabled → no checkin item.
        $result = interactions_menu_setup($this->makeEvent([], ['entity' => $place]));
        $this->assertNull($this->findItem($result, 'checkin'));

        // checkins enabled → checkin item appears.
        $place->checkins = true;
        $place->save();
        $result = interactions_menu_setup($this->makeEvent([], ['entity' => $place]));
        $checkin = $this->findItem($result, 'checkin');
        $this->assertInstanceOf(\ElggMenuItem::class, $checkin);
        $this->assertStringContainsString('action/places/checkin', $checkin->getHref());

        $place->delete();
        _elgg_services()->session_manager->removeLoggedInUser();
    }

    /**
     * @return void
     */
    public function testPassthroughWhenNotLoggedIn(): void {
        $user = $this->createUser();
        _elgg_services()->session_manager->setLoggedInUser($user);
        $place = $this->makePlace($user);
        _elgg_services()->session_manager->removeLoggedInUser();

        $result = interactions_menu_setup($this->makeEvent(['sentinel'], ['entity' => $place]));
        $this->assertEquals(['sentinel'], $result);

        _elgg_services()->session_manager->setLoggedInUser($user);
        $place->delete();
        _elgg_services()->session_manager->removeLoggedInUser();
    }

    /**
     * @return void
     */
    public function testEntityMenuSetupAddsFeatureItemForAdmin(): void {
        $owner = $this->createUser();
        _elgg_services()->session_manager->setLoggedInUser($owner);
        $place = $this->makePlace($owner);
        _elgg_services()->session_manager->removeLoggedInUser();

        // Non-admin: no feature item.
        $viewer = $this->createUser();
        _elgg_services()->session_manager->setLoggedInUser($viewer);
        $result = entity_menu_setup($this->makeEvent([], ['entity' => $place]));
        $this->assertNull($this->findItem($result, 'feature'));
        _elgg_services()->session_manager->removeLoggedInUser();

        // Admin: feature item present.
        $admin = $this->createUser();
        $admin->makeAdmin();
        _elgg_services()->session_manager->setLoggedInUser($admin);
        $result = entity_menu_setup($this->makeEvent([], ['entity' => $place]));
        $feature = $this->findItem($result, 'feature');
        $this->assertInstanceOf(\ElggMenuItem::class, $feature);
        $this->assertStringContainsString('action/places/feature', $feature->getHref());

        _elgg_services()->session_manager->setLoggedInUser($owner);
        $place->delete();
        _elgg_services()->session_manager->removeLoggedInUser();
    }
}
