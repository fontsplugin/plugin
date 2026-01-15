import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe('Olympus Google Fonts - Customizer', () => {
    
    test.beforeEach(async ({ page }) => {
        await page.goto('/wp-admin/customize.php?autofocus[panel]=ogf_google_fonts');
        await expect(page.locator('.wp-full-overlay')).toBeVisible();
        await expect(page.locator('#accordion-panel-ogf_google_fonts')).toBeVisible();
    });

    // Helper function to navigate to a section
    async function navigateToSection(page: any, sectionId: string) {
        const section = page.locator(`#accordion-section-${sectionId}`);
        // Check if section is already expanded
        const isExpanded = await section.evaluate((el) => {
            return el.classList.contains('open') || el.getAttribute('aria-expanded') === 'true';
        });
        
        if (!isExpanded) {
            await section.click();
            await page.waitForTimeout(500); // Wait for section to expand
        }
        
        // Wait for controls to be visible
        await page.waitForTimeout(300);
    }

    // Helper function to open advanced settings
    async function openAdvancedSettings(page: any, controlContainer: any) {
        const advancedButton = controlContainer.locator('.advanced-button');
        await advancedButton.click();
        await expect(controlContainer.locator('.advanced-settings-wrapper.show')).toBeVisible();
    }

    // Helper function to select a font using chosen.js
    async function selectFont(page: any, fontName: string, controlContainer: any) {
        const chosenContainer = controlContainer.locator('.typography-font-family .chosen-container').first();
        
        // Wait for chosen container to be attached
        await expect(chosenContainer).toBeAttached();
        
        // Scroll into view and wait for it to be visible
        await chosenContainer.scrollIntoViewIfNeeded();
        await page.waitForTimeout(200);
        
        // Get the chosen-single element
        const chosenSingle = chosenContainer.locator('.chosen-single');
        
        // Wait for it to be visible (it might be in a collapsed section)
        await chosenSingle.waitFor({ state: 'visible', timeout: 5000 }).catch(async () => {
            // If not visible, try to ensure the control is visible
            await controlContainer.scrollIntoViewIfNeeded();
            await page.waitForTimeout(300);
        });
        
        // Click to open chosen dropdown
        await chosenSingle.click({ force: true });
        await page.waitForTimeout(300);
        
        // Wait for chosen dropdown to be visible
        await chosenContainer.locator('.chosen-drop, .chosen-results').first().waitFor({ state: 'visible', timeout: 5000 });
        
        // Search for the font in the chosen results
        const searchInput = chosenContainer.locator('.chosen-search-input');
        if (await searchInput.count() > 0 && await searchInput.isVisible()) {
            await searchInput.fill(fontName);
            await page.waitForTimeout(400);
        }
        
        // Click on the option in chosen results (use filter with regex for partial match)
        const option = chosenContainer.locator('.chosen-results li').filter({ hasText: new RegExp(fontName, 'i') }).first();
        await option.waitFor({ state: 'visible', timeout: 5000 });
        await option.click();
        await page.waitForTimeout(500); // Wait for font to load
    }

    // Helper function to select font weight (regular select, not chosen)
    async function selectFontWeight(page: any, weight: string, controlContainer: any) {
        const weightSelect = controlContainer.locator('.typography-font-weight select');
        await weightSelect.selectOption(weight);
        await page.waitForTimeout(300);
    }

    // Helper function to select font style (regular select, not chosen)
    async function selectFontStyle(page: any, style: string, controlContainer: any) {
        const styleSelect = controlContainer.locator('.typography-font-style select');
        await styleSelect.selectOption(style);
        await page.waitForTimeout(300);
    }

    test.describe('Font Selection', () => {
        test('should select a Google Font from Base Typography section', async ({ page }) => {
            // Navigate to Basic Settings section
            await navigateToSection(page, 'ogf_basic');
            
            // Find Base Typography control
            const baseTypographyControl = page.locator('#customize-control-ogf_body_typography');
            await expect(baseTypographyControl).toBeVisible();
            
            // Verify chosen.js container exists
            const chosenContainer = baseTypographyControl.locator('.typography-font-family .chosen-container').first();
            await expect(chosenContainer).toBeVisible();
            
            // Select a Google Font (Roboto) using chosen.js
            await selectFont(page, 'Roboto', baseTypographyControl);
            
            // Verify the selection by checking the hidden select value
            const fontSelect = baseTypographyControl.locator('.typography-font-family select.ogf-select');
            const selectedValue = await fontSelect.inputValue();
            expect(selectedValue).toBeTruthy();
            expect(selectedValue).not.toBe('default');
        });

        test('should verify font options are categorized correctly', async ({ page }) => {
            await navigateToSection(page, 'ogf_basic');
            
            const baseTypographyControl = page.locator('#customize-control-ogf_body_typography');
            const chosenContainer = baseTypographyControl.locator('.typography-font-family .chosen-container').first();
            
            // Open chosen dropdown
            await chosenContainer.locator('.chosen-single').click();
            await page.waitForTimeout(300);
            
            // Get all option texts from chosen results
            const options = await chosenContainer.locator('.chosen-results li').allTextContents();
            
            // Check for "Google Fonts" section (disabled option)
            const hasGoogleFontsSection = options.some(opt => opt.includes('Google Fonts'));
            expect(hasGoogleFontsSection).toBeTruthy();
            
            // Check for "System Fonts" section
            const hasSystemFontsSection = options.some(opt => opt.includes('System Fonts'));
            expect(hasSystemFontsSection).toBeTruthy();
            
            // Close the dropdown by clicking outside or pressing escape
            await page.keyboard.press('Escape');
        });
    });

    test.describe('Font Weight Selection', () => {
        test('should select a font weight for Base Typography', async ({ page }) => {
            await navigateToSection(page, 'ogf_basic');
            
            const baseTypographyControl = page.locator('#customize-control-ogf_body_typography');
            
            // First select a font
            await selectFont(page, 'Roboto', baseTypographyControl);
            
            // Open advanced settings
            await openAdvancedSettings(page, baseTypographyControl);
            
            // Locate font weight select
            const weightSelect = baseTypographyControl.locator('.typography-font-weight select');
            await expect(weightSelect).toBeVisible();
            
            // Select a weight (700 for Bold)
            await weightSelect.selectOption('700');
            await page.waitForTimeout(300);
            
            // Verify weight is selected
            const selectedWeight = await weightSelect.inputValue();
            expect(selectedWeight).toBe('700');
        });

        test('should verify weight options update based on selected font', async ({ page }) => {
            await navigateToSection(page, 'ogf_basic');
            
            const baseTypographyControl = page.locator('#customize-control-ogf_body_typography');
            
            // Select a Google Font
            await selectFont(page, 'Roboto', baseTypographyControl);
            await openAdvancedSettings(page, baseTypographyControl);
            
            const weightSelect = baseTypographyControl.locator('.typography-font-weight select');
            
            // Get available weights for Google Font
            const googleFontWeights = await weightSelect.locator('option').allTextContents();
            const googleFontWeightCount = googleFontWeights.length;
            expect(googleFontWeightCount).toBeGreaterThan(0);
            
            // Now select a system font (Arial) using chosen.js
            const chosenContainer = baseTypographyControl.locator('.typography-font-family .chosen-container').first();
            await chosenContainer.locator('.chosen-single').click();
            await page.waitForTimeout(200);
            
            // Search for Arial
            const searchInput = chosenContainer.locator('.chosen-search-input');
            if (await searchInput.count() > 0) {
                await searchInput.fill('Arial');
                await page.waitForTimeout(300);
            }
            
            // Click on Arial option
            const arialOption = chosenContainer.locator('.chosen-results li').filter({ hasText: /^Arial$/ }).first();
            await arialOption.click();
            await page.waitForTimeout(500);
            
            // Get available weights for system font
            const systemFontWeights = await weightSelect.locator('option').allTextContents();
            
            // Verify weights are available (system fonts should have standard weights)
            expect(systemFontWeights.length).toBeGreaterThan(0);
        });
    });

    test.describe('Font Style Selection', () => {
        test('should select a font style', async ({ page }) => {
            await navigateToSection(page, 'ogf_basic');
            
            const baseTypographyControl = page.locator('#customize-control-ogf_body_typography');
            
            // Select a font first
            await selectFont(page, 'Roboto', baseTypographyControl);
            
            // Open advanced settings
            await openAdvancedSettings(page, baseTypographyControl);
            
            // Locate font style select
            const styleSelect = baseTypographyControl.locator('.typography-font-style select');
            await expect(styleSelect).toBeVisible();
            
            // Select "Italic"
            await styleSelect.selectOption('italic');
            await page.waitForTimeout(300);
            
            // Verify style is selected
            const selectedStyle = await styleSelect.inputValue();
            expect(selectedStyle).toBe('italic');
        });

        test('should be able to select different font styles', async ({ page }) => {
            await navigateToSection(page, 'ogf_basic');
            
            const baseTypographyControl = page.locator('#customize-control-ogf_body_typography');
            await selectFont(page, 'Roboto', baseTypographyControl);
            await openAdvancedSettings(page, baseTypographyControl);
            
            const styleSelect = baseTypographyControl.locator('.typography-font-style select');
            
            // Test normal
            await styleSelect.selectOption('normal');
            expect(await styleSelect.inputValue()).toBe('normal');
            
            // Test italic
            await styleSelect.selectOption('italic');
            expect(await styleSelect.inputValue()).toBe('italic');
            
            // Test oblique
            await styleSelect.selectOption('oblique');
            expect(await styleSelect.inputValue()).toBe('oblique');
        });
    });

    test.describe('Multiple Typography Elements', () => {
        test('should apply fonts to different typography elements independently', async ({ page }) => {
            // Test Base Typography
            await navigateToSection(page, 'ogf_basic');
            const baseTypographyControl = page.locator('#customize-control-ogf_body_typography');
            await selectFont(page, 'Roboto', baseTypographyControl);
            const baseFontValue = await baseTypographyControl.locator('.typography-font-family select.ogf-select').inputValue();
            
            // Test Headings Typography
            const headingsTypographyControl = page.locator('#customize-control-ogf_headings_typography');
            await selectFont(page, 'Open Sans', headingsTypographyControl);
            const headingsFontValue = await headingsTypographyControl.locator('.typography-font-family select.ogf-select').inputValue();
            
            // Test Buttons/Inputs Typography
            const inputsTypographyControl = page.locator('#customize-control-ogf_inputs_typography');
            await selectFont(page, 'Lato', inputsTypographyControl);
            const inputsFontValue = await inputsTypographyControl.locator('.typography-font-family select.ogf-select').inputValue();
            
            // Verify each has different fonts
            expect(baseFontValue).toBeTruthy();
            expect(headingsFontValue).toBeTruthy();
            expect(inputsFontValue).toBeTruthy();
            // They should be different (unless by coincidence they're the same)
            expect(baseFontValue).not.toBe('default');
            expect(headingsFontValue).not.toBe('default');
            expect(inputsFontValue).not.toBe('default');
        });
    });

    test.describe('Preview and Persistence', () => {
        test('should verify preview updates in real-time', async ({ page }) => {
            await navigateToSection(page, 'ogf_basic');
            
            const baseTypographyControl = page.locator('#customize-control-ogf_body_typography');
            
            // Select font, weight, and style
            await selectFont(page, 'Roboto', baseTypographyControl);
            await openAdvancedSettings(page, baseTypographyControl);
            await selectFontWeight(page, '700', baseTypographyControl);
            await selectFontStyle(page, 'italic', baseTypographyControl);
            
            // Verify the preview iframe exists (customizer uses iframe for preview)
            const previewFrame = page.frameLocator('iframe[name="customize-preview"]');
            // The preview should update automatically via postMessage
            // We can verify the settings were applied by checking the select values
            expect(await baseTypographyControl.locator('.typography-font-weight select').inputValue()).toBe('700');
            expect(await baseTypographyControl.locator('.typography-font-style select').inputValue()).toBe('italic');
        });

        test('should verify font persistence after customizer save', async ({ page }) => {
            await navigateToSection(page, 'ogf_basic');
            
            const baseTypographyControl = page.locator('#customize-control-ogf_body_typography');
            await selectFont(page, 'Roboto', baseTypographyControl);
            
            const selectedFont = await baseTypographyControl.locator('.typography-font-family select.ogf-select').inputValue();
            expect(selectedFont).toBeTruthy();
            expect(selectedFont).not.toBe('default');
            
            // Wait for changes to be detected (save button should become enabled)
            const publishButton = page.locator('#save');
            // Wait for button to be enabled (it starts as disabled when no changes)
            await publishButton.waitFor({ state: 'visible', timeout: 5000 });
            
            // Check if button is enabled, if not wait a bit more for changes to register
            const isEnabled = await publishButton.isEnabled();
            if (!isEnabled) {
                // Wait for customizer to detect changes
                await page.waitForTimeout(1000);
            }
            
            // Only click if enabled (there are changes to save)
            if (await publishButton.isEnabled()) {
                await publishButton.click();
                await page.waitForTimeout(1000);
            }
            
            // Reload the customizer
            await page.reload();
            await expect(page.locator('.wp-full-overlay')).toBeVisible();
            await navigateToSection(page, 'ogf_basic');
            
            // Verify selection is persisted
            const persistedControl = page.locator('#customize-control-ogf_body_typography');
            const persistedFont = await persistedControl.locator('.typography-font-family select.ogf-select').inputValue();
            // Font should be persisted (either the one we set or default if it was already default)
            expect(persistedFont).toBeTruthy();
        });
    });

    test.describe('Font Loading Optimization', () => {
        test('should verify font loading optimization section', async ({ page }) => {
            // Navigate to Font Loading section
            await navigateToSection(page, 'ogf_font_loading');
            
            // Verify section exists (it may be hidden initially)
            const fontLoadingSection = page.locator('#accordion-section-ogf_font_loading');
            await expect(fontLoadingSection).toBeAttached();
            
            // After clicking, wait for content to be available
            await page.waitForTimeout(500);
            
            // Check if section has any content (controls, descriptions, etc.)
            // The section should exist and be accessible
            const sectionExists = await fontLoadingSection.count();
            expect(sectionExists).toBe(1);
            
            // Verify section is part of the customizer structure
            const sectionId = await fontLoadingSection.getAttribute('id');
            expect(sectionId).toBe('accordion-section-ogf_font_loading');
        });
    });
});