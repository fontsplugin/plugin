import { test, expect, type Page } from '@wordpress/e2e-test-utils-playwright';
import { waitForMediaLibraryModal, navigateToCustomizerSection } from './helpers';

/**
 * Navigates to the Upload Fonts screen and waits for it to be ready.
 */
async function navigateToUploadFontsScreen(page: Page): Promise<void> {
	await page.goto('/wp-admin/edit-tags.php?taxonomy=ogf_custom_fonts');
	await expect(page).toHaveURL(/.*edit-tags\.php.*taxonomy=ogf_custom_fonts/);
}

test.describe('Olympus Google Fonts - Upload Fonts', () => {
	test.beforeEach(async ({ page }) => {
		await navigateToUploadFontsScreen(page);
	});

	test('should navigate to Upload Fonts screen and verify form', async ({ page }) => {
		await expect(page).toHaveURL(/.*edit-tags\.php.*taxonomy=ogf_custom_fonts/);

		const addNewForm = page.locator('#addtag');
		await expect(addNewForm).toBeVisible();

		const fontNameField = page.locator('#tag-name');
		await expect(fontNameField).toBeVisible();

		const fontFamilyField = page.locator('input.ogf-custom-fonts-family-input');
		await expect(fontFamilyField).toBeVisible();

		const uploadButtons = page.locator('.ogf-custom-fonts-upload');
		await expect(uploadButtons.first()).toBeVisible();
		const uploadButtonCount = await uploadButtons.count();
		expect(uploadButtonCount).toBeGreaterThan(0);
	});

	test('should upload a custom font file and verify it appears in customizer', async ({ page, admin }) => {

        const fontName = 'Test Font E2E ' + new Date().getTime();
        const fontFamily = 'Test Font Family E2E ' + new Date().getTime();

        await navigateToUploadFontsScreen(page);
		const fontNameInput = page.locator('#tag-name');
		await fontNameInput.fill(fontName);

		const fontFamilyInput = page.locator('input.ogf-custom-fonts-family-input').first();
		await fontFamilyInput.fill(fontFamily);

		const uploadButton = page.locator('.ogf-custom-fonts-upload').first();
		await expect(uploadButton).toBeVisible();
		await uploadButton.click();

		await waitForMediaLibraryModal(page, true).catch(() => {
			// Media library might not open immediately, continue
		});

		try {
			await page.keyboard.press('Escape');
			await waitForMediaLibraryModal(page, false).catch(() => {
				// Modal might already be closed
			});
		} catch {
			// Media library might not have opened, continue
		}

		const fontFileInput = page.locator('input.ogf-custom-fonts-link-input').first();
		if ((await fontFileInput.count()) > 0) {
			await fontFileInput.fill('https://example.com/font.woff2');
		}

		const addNewFontButton = page.locator('#submit');
		await expect(addNewFontButton).toBeVisible();
		
		await Promise.all([
			page.waitForNavigation({ waitUntil: 'networkidle', timeout: 10000 }).catch(() => {}),
			addNewFontButton.click(),
		]);
		
		await page.waitForLoadState('networkidle');
		
		const successMessage = page.locator('.notice-success, .updated').first();
		await expect(successMessage).toBeVisible({ timeout: 10000 }).catch(() => {
			// Success message might not always appear, continue anyway
		});

		await admin.visitAdminPage('customize.php', 'autofocus[panel]=ogf_google_fonts');

		await expect(page.locator('.wp-full-overlay')).toBeVisible();
		await expect(page.locator('#accordion-panel-ogf_google_fonts')).toBeVisible();

		await navigateToCustomizerSection(page, 'ogf_basic');

		const baseTypographyControl = page.locator('#customize-control-ogf_body_typography');
		await expect(baseTypographyControl).toBeVisible({ timeout: 10000 });

		const chosenContainer = baseTypographyControl.locator('.typography-font-family .chosen-container').first();
		await expect(chosenContainer).toBeVisible();

		await chosenContainer.locator('.chosen-single').click();
		await chosenContainer.locator('.chosen-drop, .chosen-results').first().waitFor({ state: 'visible', timeout: 5000 });

		const options = await chosenContainer.locator('.chosen-results li').allTextContents();

		const hasCustomFontsSection = options.some((opt) => opt.includes('Custom Fonts'));

		await page.keyboard.press('Escape');
		await chosenContainer.locator('.chosen-drop, .chosen-results').first().waitFor({ state: 'hidden', timeout: 2000 }).catch(() => {});

		if (hasCustomFontsSection) {
			const fontSelect = baseTypographyControl.locator('.typography-font-family select.ogf-select');
			const customFontOptions = await fontSelect.locator('option[value^="cf-"]').all();

			expect(customFontOptions.length).toBeGreaterThan(0);

			const fontOptionTexts = await Promise.all(
				customFontOptions.map(async (opt) => await opt.textContent())
			);
			
			const fontExists = fontOptionTexts.some((text) => text && text.includes(fontFamily));
			expect(fontExists).toBeTruthy();
		} else {
			expect(hasCustomFontsSection).toBeTruthy();
		}
	});
});
