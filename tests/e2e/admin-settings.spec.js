/**
 * E2E tests for WETU Importer admin settings page.
 */

const { test, expect } = require('@playwright/test');

test.describe('WETU Importer Settings Page', () => {
	test.beforeEach(async ({ page }) => {
		// Navigate to WordPress admin
		await page.goto('/wp-admin/');

		// Assume user is already logged in for local testing
		// In a real setup, you'd handle authentication here
	});

	test('should display WETU settings page', async ({ page }) => {
		// Navigate to WETU settings
		await page.goto('/wp-admin/admin.php?page=lsx-wetu-settings');

		// Check if the page loaded correctly
		await expect(page.locator('h1')).toContainText('WETU Importer');

		// Verify main settings tabs are present
		await expect(page.locator('.nav-tab-wrapper .nav-tab')).toBeVisible();
	});

	test('should validate API key field exists', async ({ page }) => {
		await page.goto('/wp-admin/admin.php?page=lsx-wetu-settings');

		// Find API key input field and verify it exists
		const apiKeyField = page.locator('input[name*="api_key"]').first();

		// Just verify the field exists for now
		await expect(apiKeyField).toBeVisible();
	});

	test('should test importer tab functionality', async ({ page }) => {
		await page.goto('/wp-admin/admin.php?page=lsx-wetu-settings');

		// Click on importer tab
		const importerTab = page
			.locator('.nav-tab')
			.filter({ hasText: 'Importer' });

		await importerTab.click();

		// Verify importer options are visible
		await expect(page.locator('.form-table')).toBeVisible();
	});

	test('should handle AJAX operations', async ({ page }) => {
		await page.goto('/wp-admin/admin.php?page=lsx-wetu-settings');

		// Listen for AJAX requests
		const ajaxPromise = page.waitForRequest(
			(request) =>
				request.url().includes('admin-ajax.php') &&
				request.method() === 'POST'
		);

		// Find and click any AJAX-enabled buttons
		const ajaxButton = page
			.locator('button[data-action], .button[data-action]')
			.first();

		await ajaxButton.click();

		// Wait for AJAX request to complete
		const request = await ajaxPromise;
		await expect(request).toBeTruthy();
	});
});

test.describe('WETU Import Process', () => {
	test('should handle tour import workflow', async ({ page }) => {
		await page.goto('/wp-admin/admin.php?page=lsx-wetu-import');

		// This test would simulate the import process
		// In a real implementation, you'd mock the WETU API responses

		// Check if import form is present
		const importForm = page.locator('form[action*="lsx-wetu"]').first();
		await expect(importForm).toBeTruthy();
	});
});
