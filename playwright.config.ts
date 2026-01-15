import { defineConfig, devices } from '@playwright/test';
import baseConfig from '@wordpress/scripts/config/playwright.config.js';

/**
 * See https://playwright.dev/docs/test-configuration.
 * Based on https://github.com/WordPress/gutenberg/blob/trunk/test/e2e/playwright.config.ts
 */
const config = defineConfig( {
	...baseConfig,
	testDir: './tests/e2e',
	/* Run tests in files in parallel */
	fullyParallel: true,
	/* Fail the build on CI if you accidentally left test.only in the source code. */
	forbidOnly: !!process.env.CI,
	/* Retry on CI only */
	retries: process.env.CI ? 2 : 0,
	/* Opt out of parallel tests on CI. */
	workers: process.env.CI ? 1 : undefined,
	/* Reporter to use. See https://playwright.dev/docs/test-reporters */
	reporter: process.env.CI
		? [
				['list'],
				['html', { outputFolder: 'playwright-report' }],
			]
		: 'html',
	// 1. Run the setup file
	globalSetup: require.resolve('./tests/e2e/global-setup.ts'),
		
	use: {
		baseURL: 'http://localhost:8888',
		// 2. Tell Playwright to load cookies from this file
		storageState: 'artifacts/storage-states/admin.json',
		screenshot: 'on',
	},

	/* Configure projects for major browsers */
	projects: [
		{
			name: 'chromium',
			use: { ...devices['Desktop Chrome'] },
		},
	],

	/* Run your local dev server before starting the tests */
	webServer: {
		command: 'npx wp-env start',
		url: process.env.WP_BASE_URL || 'http://localhost:8888',
		reuseExistingServer: !process.env.CI,
		timeout: 120 * 1000,
	},
} );

export default config;
