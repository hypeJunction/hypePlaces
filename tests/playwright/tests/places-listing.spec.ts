import { test, expect } from '@playwright/test';
import { loginAs } from '../helpers/elgg';

test.describe('hypePlaces listing pages', () => {
  test('places/all page renders for guest or logged-in user', async ({ page }) => {
    const response = await page.goto('/places/all');
    // Either renders, or redirects to login (walled-garden) — not a 500
    expect(response?.status() ?? 0).toBeLessThan(500);
  });

  test('places/featured page renders', async ({ page }) => {
    const response = await page.goto('/places/featured');
    expect(response?.status() ?? 0).toBeLessThan(500);
  });

  test('logged-in user sees places list with title', async ({ page }) => {
    await loginAs(page, 'testuser');
    await page.goto('/places/all');
    await expect(page).toHaveURL(/\/places\/all/);
    // Places heading or site menu item present
    const hasHeading = await page
      .locator('h1, .elgg-heading-main, .elgg-menu-site a[href*="places"]')
      .first()
      .isVisible()
      .catch(() => false);
    expect(hasHeading).toBeTruthy();
  });

  test('places/owner/:username renders for logged-in user', async ({ page }) => {
    await loginAs(page, 'testuser');
    const response = await page.goto('/places/owner/testuser');
    expect(response?.status() ?? 0).toBeLessThan(500);
  });
});
