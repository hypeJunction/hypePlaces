<?php

namespace hypeJunction\Places\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Per-fix regression guards for the Elgg 7.x migration of hypePlaces.
 *
 * Each method asserts that a specific removed-symbol fix actually LANDED in the
 * source file it was applied to: the removed API is gone AND its 7.x-safe
 * replacement is present. Static (no Elgg boot) because the un-migrated source
 * fatals at load/render on 7.x before an assertion could run — the signature has
 * to be caught in the file. Keyed to the migration commits in the gap spec.
 */
class MigrationFixesTest extends TestCase {

    private function pluginRoot(): string {
        return dirname(dirname(__DIR__));
    }

    private function read(string $relative): string {
        $path = $this->pluginRoot() . '/' . ltrim($relative, '/');
        $this->assertFileExists($path);
        return (string) file_get_contents($path);
    }

    /**
     * ccc5db0 — current_page_url() (removed Elgg 5.0/6.x) -> elgg_get_current_url()
     * in the maps search sidebar form action. The bare call fatalled the page.
     *
     * @return void
     */
    public function testSidebarUsesCurrentUrlReplacement(): void {
        $src = $this->read('views/default/framework/maps/search/places/sidebar.php');
        $this->assertStringContainsString('elgg_get_current_url(', $src);
        $this->assertSame(0, preg_match('/(?<!\w)current_page_url\s*\(/', $src),
            'removed current_page_url() must not survive on 7.x');
    }

    /**
     * 9ea1473 — get_default_access() (removed 5.x) -> elgg_get_default_access()
     * as the access_id default in the edit form.
     *
     * @return void
     */
    public function testEditFormUsesDefaultAccessReplacement(): void {
        $src = $this->read('views/default/forms/places/edit.php');
        $this->assertStringContainsString('elgg_get_default_access(', $src);
        $this->assertSame(0, preg_match('/(?<!\w)get_default_access\s*\(/', $src),
            'removed get_default_access() must not survive on 7.x');
    }

    /**
     * c3dd1bc — get_user_by_username() (removed 5.x) -> elgg_get_user_by_username()
     * in the owner and bookmarked collection resources.
     *
     * @return void
     */
    public function testOwnerResourcesUseUsernameLookupReplacement(): void {
        foreach (['views/default/resources/places/owner.php', 'views/default/resources/places/bookmarked.php'] as $file) {
            $src = $this->read($file);
            $this->assertStringContainsString('elgg_get_user_by_username(', $src, "$file must use the replacement");
            $this->assertSame(0, preg_match('/(?<!\w)get_user_by_username\s*\(/', $src),
                "removed get_user_by_username() must not survive in $file");
        }
    }

    /**
     * a257d1b — check/add/remove_entity_relationship() (removed 5.x) replaced with
     * the ElggEntity OO relationship API in the bookmark/unbookmark actions.
     *
     * @return void
     */
    public function testActionsUseOoRelationshipApi(): void {
        $bookmark = $this->read('actions/places/bookmark.php');
        $this->assertStringContainsString('->addRelationship(', $bookmark);
        $this->assertStringContainsString('->hasRelationship(', $bookmark);

        $unbookmark = $this->read('actions/places/unbookmark.php');
        $this->assertStringContainsString('->removeRelationship(', $unbookmark);
        $this->assertStringContainsString('->hasRelationship(', $unbookmark);

        foreach ([$bookmark, $unbookmark] as $src) {
            $this->assertSame(0, preg_match('/(?<!\w)(?:check|add|remove)_entity_relationship\s*\(/', $src),
                'removed procedural relationship functions must not survive on 7.x');
        }
    }

    /**
     * a257d1b — languages/en.php converted from add_translation() (removed 5.0)
     * to a plain return-array module.
     *
     * @return void
     */
    public function testLanguagesFileReturnsArray(): void {
        $src = $this->read('languages/en.php');
        $this->assertSame(0, preg_match('/(?<!\w)add_translation\s*\(/', $src),
            'removed add_translation() must not survive on 7.x');
        $this->assertMatchesRegularExpression('/return\s*\[/', $src,
            'language file must return an array directly on 5.x+');
    }

    /**
     * 56b240e — Seed::getType() is abstract public static in 7.x; the override
     * must be declared static (a non-static override fatalled) and use the
     * lowercase plugin id.
     *
     * @return void
     */
    public function testSeederGetTypeIsStaticAndLowercase(): void {
        $src = $this->read('classes/hypeJunction/Places/Seeder.php');
        $this->assertMatchesRegularExpression('/public\s+static\s+function\s+getType\s*\(/', $src,
            'getType() must be declared static to satisfy Seed::getType on 7.x');
        $this->assertMatchesRegularExpression("/return\s+'hypeplaces'/", $src,
            'seed type must be the lowercase plugin id');
    }
}
