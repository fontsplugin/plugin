# E2E Tests

End-to-end tests for the Olympus Google Fonts plugin using Playwright.

## Prerequisites

1. **Docker** - Required for `wp-env`
2. **Node.js** - For running Playwright tests
3. **@wordpress/env** - WordPress local development environment

## Setup

1. Install dependencies:
```bash
npm install
```

2. Start the WordPress environment using `wp-env`:
```bash
wp-env start
```

This will:
- Start a local WordPress instance at `http://localhost:8888`
- Automatically mount and activate the Olympus Google Fonts plugin
- Use default credentials:
  - Username: `admin`
  - Password: `password`

The `.wp-env.json` configuration file ensures the plugin is properly mounted and activated for testing.

3. Install Playwright browsers (first time only):
```bash
npx playwright install
```

## Running Tests

Run all e2e tests:
```bash
npm run test:e2e
```

Run tests with UI mode (interactive):
```bash
npm run test:e2e:ui
```

Run tests in debug mode:
```bash
npm run test:e2e:debug
```

## Test Structure

- `customizer.spec.ts` - Tests the plugin's customizer functionality, including:
  - Accessing the Fonts Plugin panel
  - Navigating to Basic Settings
  - Selecting a Google Font for Base Typography
  - Verifying font selection works correctly

## Configuration

The test configuration is in `playwright.config.ts` at the root of the plugin directory. By default, tests run against `http://localhost:8888` (the default `wp-env` URL).

To use a different WordPress instance, set the `WP_BASE_URL` environment variable:
```bash
WP_BASE_URL=http://localhost:3333 npm run test:e2e
```

## References

- [WordPress wp-env Documentation](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/)
- [Gutenberg E2E Test Configuration](https://github.com/WordPress/gutenberg/blob/trunk/test/e2e/playwright.config.ts)
- [Playwright Documentation](https://playwright.dev/)
