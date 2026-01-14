import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe('Olympus Google Fonts - Customizer', () => {
    
    test('should access the admin area', async ({ page }) => {
        // You are already logged in thanks to globalSetup!
        await page.goto('/wp-admin/customize.php?autofocus[panel]=ogf_google_fonts');
        
        await expect(page.locator('.wp-full-overlay')).toBeVisible();
        await expect(page.locator('#accordion-panel-ogf_google_fonts')).toBeVisible();
    });

    test('should load Fonts Plugin panel and allow selection', async ({ page }) => {
        await page.goto('/wp-admin/customize.php?autofocus[panel]=ogf_google_fonts');
        
        // Add your font selection logic here
        const fontsPanel = page.locator('#accordion-panel-ogf_google_fonts');
        await fontsPanel.click();
    });
});