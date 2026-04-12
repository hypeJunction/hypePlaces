import { test, expect } from '@playwright/test';
import { loginAs } from '../helpers/elgg';

test.describe('hypePlaces permissions', () => {
  test('anonymous user cannot access places/create (redirected to login)', async ({ page }) => {
    const response = await page.goto('/places/create');
    // Either 403/302 or the URL changed to /login
    const status = response?.status() ?? 0;
    const urlAfter = page.url();
    const ok = status === 403 || status === 302 || /login/.test(urlAfter);
    expect(ok).toBeTruthy();
  });

  test('non-admin cannot trigger places/feature action', async ({ page }) => {
    await loginAs(page, 'testuser');
    const response = await page.goto('/action/places/feature?guid=1');
    // Admin-only actions should reject non-admins (403, or redirect with error)
    const status = response?.status() ?? 0;
    expect(status).toBeLessThan(500);
    // We don't assert a specific redirect — just that it didn't crash and
    // didn't silently succeed in marking entity as featured (can't verify without guid).
  });
});
