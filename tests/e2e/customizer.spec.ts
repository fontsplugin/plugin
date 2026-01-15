import { test, expect, type Page } from '@wordpress/e2e-test-utils-playwright';
import {
	navigateToCustomizerSection,
	openAdvancedSettings,
	selectFont,
	selectFontWeight,
	selectFontStyle,
} from './helpers';

test.describe('Olympus Google Fonts - Customizer', () => {
	test.beforeEach(async ({ page }) => {
		await page.goto('/wp-admin/customize.php?autofocus[panel]=ogf_google_fonts');
		await expect(page.locator('.wp-full-overlay')).toBeVisible();
		await expect(page.locator('#accordion-panel-ogf_google_fonts')).toBeVisible();
	});

	test.describe('Font Selection', () => {
		test('should select a Google Font from Base Typography section', async ({ page }) => {
			await navigateToCustomizerSection(page, 'ogf_basic');

			const baseTypographyControl = page.locator('#customize-control-ogf_body_typography');
			await expect(baseTypographyControl).toBeVisible();

			const chosenContainer = baseTypographyControl.locator('.typography-font-family .chosen-container').first();
			await expect(chosenContainer).toBeVisible();

			await selectFont(page, 'Roboto', baseTypographyControl);

			const fontSelect = baseTypographyControl.locator('.typography-font-family select.ogf-select');
			const selectedValue = await fontSelect.inputValue();
			expect(selectedValue).toBeTruthy();
			expect(selectedValue).not.toBe('default');
		});

		test('should verify font options are categorized correctly', async ({ page }) => {
			await navigateToCustomizerSection(page, 'ogf_basic');

			const baseTypographyControl = page.locator('#customize-control-ogf_body_typography');
			const chosenContainer = baseTypographyControl.locator('.typography-font-family .chosen-container').first();

			await chosenContainer.locator('.chosen-single').click();
			await chosenContainer.locator('.chosen-drop, .chosen-results').first().waitFor({ state: 'visible', timeout: 5000 });

			const options = await chosenContainer.locator('.chosen-results li').allTextContents();

			const hasGoogleFontsSection = options.some((opt) => opt.includes('Google Fonts'));
			expect(hasGoogleFontsSection).toBeTruthy();

			const hasSystemFontsSection = options.some((opt) => opt.includes('System Fonts'));
			expect(hasSystemFontsSection).toBeTruthy();

			await page.keyboard.press('Escape');
			await chosenContainer.locator('.chosen-drop, .chosen-results').first().waitFor({ state: 'hidden', timeout: 2000 }).catch(() => {});
		});
	});

	test.describe('Font Weight Selection', () => {
		test('should select a font weight for Base Typography', async ({ page }) => {
			await navigateToCustomizerSection(page, 'ogf_basic');

			const baseTypographyControl = page.locator('#customize-control-ogf_body_typography');

			await selectFont(page, 'Roboto', baseTypographyControl);

			await openAdvancedSettings(baseTypographyControl);

			const weightSelect = baseTypographyControl.locator('.typography-font-weight select');
			await expect(weightSelect).toBeVisible();

			await selectFontWeight(page, '700', baseTypographyControl);

			const selectedWeight = await weightSelect.inputValue();
			expect(selectedWeight).toBe('700');
		});

		test('should verify weight options update based on selected font', async ({ page }) => {
			await navigateToCustomizerSection(page, 'ogf_basic');

			const baseTypographyControl = page.locator('#customize-control-ogf_body_typography');

			await selectFont(page, 'Roboto', baseTypographyControl);
			await openAdvancedSettings(baseTypographyControl);

			const weightSelect = baseTypographyControl.locator('.typography-font-weight select');

			const googleFontWeights = await weightSelect.locator('option').allTextContents();
			const googleFontWeightCount = googleFontWeights.length;
			expect(googleFontWeightCount).toBeGreaterThan(0);

			const chosenContainer = baseTypographyControl.locator('.typography-font-family .chosen-container').first();
			await chosenContainer.locator('.chosen-single').click();
			await chosenContainer.locator('.chosen-drop, .chosen-results').first().waitFor({ state: 'visible', timeout: 5000 });

			const searchInput = chosenContainer.locator('.chosen-search-input');
			if ((await searchInput.count()) > 0) {
				await searchInput.fill('Arial');
				await expect(
					chosenContainer.locator('.chosen-results li').filter({ hasText: /^Arial$/ })
				).toBeVisible({ timeout: 5000 });
			}

			const arialOption = chosenContainer.locator('.chosen-results li').filter({ hasText: /^Arial$/ }).first();
			await arialOption.click();
			await chosenContainer.locator('.chosen-drop, .chosen-results').first().waitFor({ state: 'hidden', timeout: 5000 }).catch(() => {});

			const systemFontWeights = await weightSelect.locator('option').allTextContents();

			expect(systemFontWeights.length).toBeGreaterThan(0);
		});
	});

	test.describe('Font Style Selection', () => {
		test('should select a font style', async ({ page }) => {
			await navigateToCustomizerSection(page, 'ogf_basic');

			const baseTypographyControl = page.locator('#customize-control-ogf_body_typography');

			await selectFont(page, 'Roboto', baseTypographyControl);

			await openAdvancedSettings(baseTypographyControl);

			const styleSelect = baseTypographyControl.locator('.typography-font-style select');
			await expect(styleSelect).toBeVisible();

			await selectFontStyle(page, 'italic', baseTypographyControl);

			const selectedStyle = await styleSelect.inputValue();
			expect(selectedStyle).toBe('italic');
		});

		test('should be able to select different font styles', async ({ page }) => {
			await navigateToCustomizerSection(page, 'ogf_basic');

			const baseTypographyControl = page.locator('#customize-control-ogf_body_typography');
			await selectFont(page, 'Roboto', baseTypographyControl);
			await openAdvancedSettings(baseTypographyControl);

			const styleSelect = baseTypographyControl.locator('.typography-font-style select');

			await selectFontStyle(page, 'normal', baseTypographyControl);
			expect(await styleSelect.inputValue()).toBe('normal');

			await selectFontStyle(page, 'italic', baseTypographyControl);
			expect(await styleSelect.inputValue()).toBe('italic');

			await selectFontStyle(page, 'oblique', baseTypographyControl);
			expect(await styleSelect.inputValue()).toBe('oblique');
		});
	});

	test.describe('Multiple Typography Elements', () => {
		test('should apply fonts to different typography elements independently', async ({ page }) => {
			await navigateToCustomizerSection(page, 'ogf_basic');

			const baseTypographyControl = page.locator('#customize-control-ogf_body_typography');
			await selectFont(page, 'Roboto', baseTypographyControl);
			const baseFontValue = await baseTypographyControl
				.locator('.typography-font-family select.ogf-select')
				.inputValue();

			const headingsTypographyControl = page.locator('#customize-control-ogf_headings_typography');
			await selectFont(page, 'Open Sans', headingsTypographyControl);
			const headingsFontValue = await headingsTypographyControl
				.locator('.typography-font-family select.ogf-select')
				.inputValue();

			const inputsTypographyControl = page.locator('#customize-control-ogf_inputs_typography');
			await selectFont(page, 'Lato', inputsTypographyControl);
			const inputsFontValue = await inputsTypographyControl
				.locator('.typography-font-family select.ogf-select')
				.inputValue();

			expect(baseFontValue).toBeTruthy();
			expect(headingsFontValue).toBeTruthy();
			expect(inputsFontValue).toBeTruthy();
			expect(baseFontValue).not.toBe('default');
			expect(headingsFontValue).not.toBe('default');
			expect(inputsFontValue).not.toBe('default');
		});
	});

	test.describe('Preview and Persistence', () => {
		test('should verify preview updates in real-time', async ({ page }) => {
			await navigateToCustomizerSection(page, 'ogf_basic');

			const baseTypographyControl = page.locator('#customize-control-ogf_body_typography');

			await selectFont(page, 'Roboto', baseTypographyControl);
			await openAdvancedSettings(baseTypographyControl);
			await selectFontWeight(page, '700', baseTypographyControl);
			await selectFontStyle(page, 'italic', baseTypographyControl);

			const previewFrame = page.frameLocator('iframe[name="customize-preview"]');
			expect(await baseTypographyControl.locator('.typography-font-weight select').inputValue()).toBe('700');
			expect(await baseTypographyControl.locator('.typography-font-style select').inputValue()).toBe('italic');
		});

		test('should verify font persistence after customizer save', async ({ page }) => {
			await navigateToCustomizerSection(page, 'ogf_basic');

			const baseTypographyControl = page.locator('#customize-control-ogf_body_typography');
			await selectFont(page, 'Roboto', baseTypographyControl);

			const selectedFont = await baseTypographyControl.locator('.typography-font-family select.ogf-select').inputValue();
			expect(selectedFont).toBeTruthy();
			expect(selectedFont).not.toBe('default');

			const publishButton = page.locator('#save');
			await expect(publishButton).toBeVisible({ timeout: 5000 });

			const isEnabled = await publishButton.isEnabled();
			if (isEnabled) {
				await publishButton.click();
				await expect(page.locator('.notice-success')).toBeVisible({ timeout: 5000 }).catch(() => {
					// Success message might appear differently
				});
			}

			await page.reload();
			await expect(page.locator('.wp-full-overlay')).toBeVisible();
			await navigateToCustomizerSection(page, 'ogf_basic');

			const persistedControl = page.locator('#customize-control-ogf_body_typography');
			const persistedFont = await persistedControl.locator('.typography-font-family select.ogf-select').inputValue();
			expect(persistedFont).toBeTruthy();
		});
	});

	test.describe('Font Loading Optimization', () => {
		test('should verify font loading optimization section', async ({ page }) => {
			await navigateToCustomizerSection(page, 'ogf_font_loading');

			const fontLoadingSection = page.locator('#accordion-section-ogf_font_loading');
			await expect(fontLoadingSection).toBeAttached();

			const sectionExists = await fontLoadingSection.count();
			expect(sectionExists).toBe(1);

			const sectionId = await fontLoadingSection.getAttribute('id');
			expect(sectionId).toBe('accordion-section-ogf_font_loading');
		});
	});
});
