import { type Page, type Locator, expect } from '@wordpress/e2e-test-utils-playwright';

/**
 * Navigates to a customizer section and expands it if needed.
 * @param page - The Playwright page object.
 * @param sectionId - The ID of the section to navigate to (without the 'accordion-section-' prefix).
 */
export async function navigateToCustomizerSection(page: Page, sectionId: string): Promise<void> {
	const section = page.locator(`#accordion-section-${sectionId}`);
	const subAccordion = page.locator(`#sub-accordion-section-${sectionId}`);
	
	const isExpanded = await subAccordion.isVisible().catch(() => false);

	if (!isExpanded) {
		await section.click();
		await expect(subAccordion).toBeVisible({ timeout: 5000 });
	}

	await expect(section).toBeAttached();
}

/**
 * Opens the advanced settings panel for a typography control.
 * @param controlContainer - The container locator for the typography control.
 */
export async function openAdvancedSettings(controlContainer: Locator): Promise<void> {
	const advancedButton = controlContainer.locator('.advanced-button');
	await advancedButton.click();
	await expect(controlContainer.locator('.advanced-settings-wrapper.show')).toBeVisible({ timeout: 5000 });
}

/**
 * Selects a font from the Chosen.js dropdown in a typography control.
 * @param page - The Playwright page object.
 * @param fontName - The name of the font to select.
 * @param controlContainer - The container locator for the typography control.
 */
export async function selectFont(page: Page, fontName: string, controlContainer: Locator): Promise<void> {
	const chosenContainer = controlContainer.locator('.typography-font-family .chosen-container').first();

	await expect(chosenContainer).toBeAttached();

	await chosenContainer.scrollIntoViewIfNeeded();
	await expect(chosenContainer).toBeVisible({ timeout: 5000 });

	const chosenSingle = chosenContainer.locator('.chosen-single');

	await chosenSingle.waitFor({ state: 'visible', timeout: 5000 }).catch(async () => {
		await controlContainer.scrollIntoViewIfNeeded();
		await expect(chosenSingle).toBeVisible({ timeout: 5000 });
	});

	await chosenSingle.click({ force: true });
	await chosenContainer.locator('.chosen-drop, .chosen-results').first().waitFor({ state: 'visible', timeout: 5000 });

	const searchInput = chosenContainer.locator('.chosen-search-input');
	if ((await searchInput.count()) > 0 && (await searchInput.isVisible())) {
		await searchInput.fill(fontName);
		await expect(
			chosenContainer.locator('.chosen-results li').filter({ hasText: new RegExp(fontName, 'i') }).first()
		).toBeVisible({ timeout: 5000 });
	}

	const option = chosenContainer.locator('.chosen-results li').filter({ hasText: new RegExp(fontName, 'i') }).first();
	await option.waitFor({ state: 'visible', timeout: 5000 });
	await option.click();
	await chosenContainer.locator('.chosen-drop, .chosen-results').first().waitFor({ state: 'hidden', timeout: 5000 }).catch(() => {
		// Dropdown might close differently, just wait a bit
	});
}

/**
 * Selects a font weight from the weight dropdown.
 * @param page - The Playwright page object.
 * @param weight - The weight value to select (e.g., '700').
 * @param controlContainer - The container locator for the typography control.
 */
export async function selectFontWeight(page: Page, weight: string, controlContainer: Locator): Promise<void> {
	const weightSelect = controlContainer.locator('.typography-font-weight select');
	await weightSelect.selectOption(weight);
	await expect(weightSelect).toHaveValue(weight, { timeout: 5000 });
}

/**
 * Selects a font style from the style dropdown.
 * @param page - The Playwright page object.
 * @param style - The style value to select (e.g., 'italic').
 * @param controlContainer - The container locator for the typography control.
 */
export async function selectFontStyle(page: Page, style: string, controlContainer: Locator): Promise<void> {
	const styleSelect = controlContainer.locator('.typography-font-style select');
	await styleSelect.selectOption(style);
	await expect(styleSelect).toHaveValue(style, { timeout: 5000 });
}

/**
 * Waits for the media library modal to appear or disappear.
 * @param page - The Playwright page object.
 * @param shouldBeVisible - Whether the modal should be visible or not.
 */
export async function waitForMediaLibraryModal(page: Page, shouldBeVisible: boolean): Promise<void> {
	const mediaModal = page.getByRole('dialog').filter({ hasText: /media|library/i });
	if (shouldBeVisible) {
		await expect(mediaModal).toBeVisible({ timeout: 5000 });
	} else {
		await expect(mediaModal).not.toBeVisible({ timeout: 2000 });
	}
}
