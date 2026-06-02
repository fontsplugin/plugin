# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

### PHP (Composer)
```bash
composer install              # Install PHP dependencies
composer test:unit            # Run unit tests (fast, no WordPress needed)
composer test:integration     # Run integration tests (requires WordPress)
composer test                 # Run unit tests (alias for test:unit)
composer test:all             # Run all PHP test suites
composer test:coverage        # Run unit tests with HTML coverage report
composer lint                 # Run PHPCS code style checks
composer lint:fix             # Auto-fix PHPCS violations with phpcbf
```

### Gutenberg Block (JS)
```bash
cd blocks
npm install
npm run build     # Production build
npm run start     # Watch mode for development
npm run lint:js   # JS linting
npm run lint:css  # CSS/SCSS linting
npm run format    # Auto-format JS/CSS
```

### E2E Tests (Playwright + wp-env)
```bash
npm install
npm run env:start       # Start local WordPress environment (port 8888)
npm run env:stop        # Stop environment
npm run env:clean       # Reset environment
npm run test:e2e        # Run Playwright E2E tests
npm run test:e2e:ui     # Run with Playwright UI
npm run test:e2e:debug  # Run in debug mode
```

## Architecture

This is a WordPress plugin (slug: `olympus-google-fonts`, text domain: `olympus-google-fonts`) with a free/Pro split. The constant `OGF_PRO` is defined only when the Pro version is active; free-only code is gated with `! defined( 'OGF_PRO' )`.

### Font ID Conventions

Font IDs encode the font type via prefix:
- No prefix → Google Font (key into `OGF_Fonts::$google_fonts`, e.g. `open-sans`)
- `sf-` prefix → System font (e.g. `sf-arial`)
- `cf-` prefix → Custom uploaded font (e.g. `cf-my-font`)
- `tk-` prefix → Adobe/Typekit font (e.g. `tk-some-font`)

All CSS generation in `ogf_build_font_stack()` branches on these prefixes. The Google Fonts data lives in `blocks/src/google-fonts/fonts.json` and is loaded by `ogf_fonts_array()`.

### Core CSS Pipeline

1. `wp_head` (priority 1000) → `ogf_output_css()` in `includes/customizer/output-css.php`
2. That fires the `ogf_inline_styles` action, which triggers:
   - `ogf_generate_css_variables()` — writes `:root` CSS custom properties for body/headings/inputs
   - `ogf_echo_custom_font_css()` — writes `@font-face` blocks for uploaded fonts
   - `Olympus_Google_Fonts::enqueue()` — emits the Google Fonts `@import` or cached CSS
3. Then `ogf_generate_css()` iterates over every element from `ogf_get_elements()` and `ogf_get_custom_elements()`, writing selector-scoped CSS from theme mods.

The Google Fonts `@import` URL is built by `OGF_Fonts::build_url()` and cached as a transient (`ogf_external_font_css_<md5>`) for 24 hours. `OGF_Fonts` is a singleton — use `OGF_Fonts::get_instance()`.

### Customizer Structure

Settings live under the `ogf_google_fonts` panel, defined in `includes/customizer/panels.php` and `includes/customizer/settings.php`. Each element in `ogf_get_elements()` gets a typography control group (font, size, weight, style, color, etc.) stored as theme mods keyed `{element_id}_font`, `{element_id}_font_size`, etc.

Custom element selectors added by users are stored as JSON in the `ogf_custom_selectors` theme mod and surfaced through `ogf_get_custom_elements()`.

Custom controls are in `includes/customizer/controls/`:
- `OGF_Customize_Typography_Control` — the main per-element control
- `OGF_Customize_Multiple_Fonts_Control` — the "load additional fonts" control
- `OGF_Customize_Repeater_Control` — custom selector builder

### Custom Font Uploads

Uploaded fonts are stored as terms in the `ogf_custom_fonts` WordPress taxonomy (registered by `OGF_Fonts_Taxonomy`). Font file URLs and metadata (weight, style, family name) are stored in `wp_options` as `taxonomy_ogf_custom_fonts_{term_id}`. The admin UI for uploads is `admin/class-ogf-upload-fonts-screen.php`.

### Gutenberg Block

The block (`olympus-google-fonts/google-fonts`) is registered in `blocks/init.php`. Its JS source is in `blocks/src/google-fonts/`. The build output (`blocks/build/`) must be committed — it is not gitignored. The block renders server-side via `olympus_google_fonts_block_render()`.

Gutenberg-specific CSS output is handled separately in `includes/gutenberg/output-css.php`, firing on `ogf_gutenberg_inline_styles`.

### Compatibility

`Olympus_Google_Fonts::compatibility()` auto-loads a compatibility file from `compatibility/` by matching the active theme's author slug (e.g. `compatibility/elegantthemes.php`). Plugin-specific compat files (Elementor, WooCommerce, Divi, MemberPress) are conditionally loaded.

### Testing Structure

- `tests/unit/` — uses Brain\Monkey for WP function mocking; no WordPress bootstrap needed
- `tests/integration/` — requires a live WordPress installation
- `tests/e2e/` — Playwright tests against `http://localhost:8888` (wp-env)
- Test class base: `OGF_Unit_TestCase` (from `tests/bootstrap.php`)

PHPCS is configured in `phpcs.xml`: WordPress-Extra + PHPCompatibilityWP, PHP 7.0+, WP 6.0+. Yoda conditions are disabled.
