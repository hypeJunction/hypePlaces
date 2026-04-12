import { test, expect } from '@playwright/test';
import { loginAs, getEntitiesBySubtype, getRelationship, queryDb } from '../helpers/elgg';

test.describe('hypePlaces bookmark action', () => {
  test('bookmark action creates a bookmarked relationship', async ({ page }) => {
    await loginAs(page, 'testuser');

    // Pick any existing hjplace or skip if none
    const places = await getEntitiesBySubtype('hjplace');
    test.skip(places.length === 0, 'No hjplace entities exist — skipping bookmark test');

    const place = places[0];
    const userRows = await queryDb(
      'SELECT guid FROM elgg_users_entity WHERE username = ?',
      ['testuser']
    );
    const userGuid = userRows[0]?.guid;
    test.skip(!userGuid, 'testuser not found in DB');

    // Trigger bookmark action via URL
    const response = await page.goto(`/action/places/bookmark?guid=${place.guid}`);
    expect(response?.status() ?? 0).toBeLessThan(500);

    const rel = await getRelationship(userGuid, 'bookmarked', place.guid);
    expect(rel.length).toBeGreaterThan(0);

    // Cleanup: unbookmark
    await page.goto(`/action/places/unbookmark?guid=${place.guid}`);
    const relAfter = await getRelationship(userGuid, 'bookmarked', place.guid);
    expect(relAfter.length).toBe(0);
  });
});
