/**
 * Customizer E2E Tests for Olympus Google Fonts
 *
 * Tests the typography controls in the WordPress Customizer.
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import type { Page, Locator } from '@playwright/test';

/**
 * Navigate to customizer and open the Google Fonts panel and a specific section
 */
async function navigateToCustomizerSection(page: Page, sectionId: string) {
	await page.goto('/wp-admin/customize.php');
	await expect(page).toHaveURL(/.*customize\.php/);

	await page.waitForSelector('#accordion-panel-ogf_google_fonts');
	await page.click('#accordion-panel-ogf_google_fonts');

	await page.waitForSelector(`#accordion-section-${sectionId}`);
	await page.click(`#accordion-section-${sectionId}`);
}

/**
 * Helper function to select a random font from Chosen.js dropdown
 * This combines getting a random font and selecting it in one operation
 * @param page - Playwright page object
 * @param chosenSelector - Selector for the chosen-single element
 */
async function selectRandomChosenOption(
	page: Page,
	chosenSelector: string
): Promise<string> {
	// Get the chosen-single element
	const chosenSingle = page.locator(
		chosenSelector.includes('.chosen-single')
			? chosenSelector
			: `${chosenSelector} .chosen-single`
	);

	// Wait for element to be visible and stable
	await chosenSingle.waitFor({ state: 'visible' });
	await page.waitForTimeout(200);

	// Get current font to exclude it from random selection
	const currentFont = (await chosenSingle.textContent())?.trim() || '';

	// Click to open the dropdown
	await chosenSingle.click();

	// Wait for the dropdown to appear
	await page.waitForSelector('.chosen-container.chosen-with-drop .chosen-drop', { state: 'visible' });

	// Wait for results to be available
	await page.waitForSelector('.chosen-container.chosen-with-drop .chosen-results li', { state: 'visible' });

	// Get all available font options (excluding the current one and any group headers)
	const allOptions = await page
		.locator('.chosen-container.chosen-with-drop .chosen-results li:not(.group-result)')
		.allTextContents();

	// Filter out empty strings and the current font
	const availableFonts = allOptions
		.map(font => font.trim())
		.filter(font => font && font.toLowerCase() !== currentFont.toLowerCase());

	// If no other fonts available, close and return (shouldn't happen but safety check)
	if (availableFonts.length === 0) {
		await page.keyboard.press('Escape');
		await page.waitForSelector('.chosen-container.chosen-with-drop', { state: 'hidden' });
		return currentFont || 'Roboto';
	}

	// Select a random font
	const randomIndex = Math.floor(Math.random() * availableFonts.length);
	const selectedFont = availableFonts[randomIndex];

	// Find and click the option
	const option = page
		.locator('.chosen-container.chosen-with-drop .chosen-results li')
		.filter({ hasText: new RegExp(selectedFont, 'i') })
		.first();

	// Scroll the option into view if needed
	await option.scrollIntoViewIfNeeded();
	
	// Wait for option to be visible and clickable
	await option.waitFor({ state: 'visible' });
	await option.click();

	// Wait for dropdown to close
	await page.waitForSelector('.chosen-container.chosen-with-drop', { state: 'hidden' });

	return selectedFont;
}

test.describe('WordPress Admin', () => {
	// Login once before each test
	test.beforeEach(async ({ page }) => {
		// Navigate to the login page - 'load' is usually sufficient (faster than 'networkidle')
		await page.goto('/wp-login.php', { waitUntil: 'networkidle' });

		// Get field locators - Playwright actions auto-wait, so we don't need explicit waits
		const usernameField = page.locator('input[name="log"]');
		const passwordField = page.locator('input[name="pwd"]');

		// Fill fields - .fill() auto-waits for visibility, enabled, and stability
		await usernameField.fill('admin');
		await passwordField.fill('password');

		// Submit - .click() also auto-waits
		await page.locator('input[type="submit"]').click();

		// Wait for navigation - this is necessary to ensure login completed
		await page.waitForURL(/.*wp-admin/);
	});

	test('should login and navigate to wp-admin', async ({ admin, page }) => {
		// Now we can use admin.visitAdminPage() since we're logged in
		await admin.visitAdminPage('/');

		// Verify we're on the admin dashboard
		await expect(page).toHaveURL(/.*wp-admin/);
		
		// Verify the admin dashboard is visible
		await expect(page.locator('#wpbody-content')).toBeVisible();
	});

	test('should login and navigate to customizer', async ({ page }) => {
		// Navigate directly to the customizer (bypassing visitAdminPage to ignore PHP warnings)
		await page.goto('/wp-admin/customize.php');

		// Verify we're on the customizer page
		await expect(page).toHaveURL(/.*customize\.php/);

		await page.waitForSelector('#accordion-panel-ogf_google_fonts');
		await page.click('#accordion-panel-ogf_google_fonts');

		await page.waitForSelector('#accordion-section-ogf_basic');
		await page.click('#accordion-section-ogf_basic');

		await expect(page.locator('#customize-control-ogf_body_typography')).toBeVisible();

		// Test interacting with Chosen.js dropdown
		const fontFamilySelector = '#customize-control-ogf_body_typography .typography-font-family .chosen-single';
		
		// Wait for the element to be visible and stable
		await page.waitForSelector(fontFamilySelector, { state: 'visible' });
		await page.waitForTimeout(300); // Allow any animations to complete
		
		// Select a random font (this opens dropdown once, gets random font, and selects it)
		const selectedFont = await selectRandomChosenOption(page, fontFamilySelector);

		// Verify the selection was made (check that the chosen-single text changed)
		await expect(page.locator(fontFamilySelector)).toContainText(selectedFont);

		// Save the changes (button should be visible now since we made a change)
		const saveButton = page.locator('input[name="save"]');
		await expect(saveButton).toBeVisible();
		await saveButton.click();
		
		// Wait for save to complete - button value changes to "Published" and becomes disabled
		await expect(saveButton).toHaveValue('Published');
		await expect(saveButton).toBeDisabled();

	});
});



