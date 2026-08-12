# Plinth / OGF Refinement TODO

## Completed

- [x] Trim `ogf_save_settings` to host/preload only; remove dead font_display/use_px branches
- [x] Delete unused `.ogf-toggle` CSS (Settings tab uses `pln-toggle`)
- [x] Remove unused `isPro` and `tokenSaved` from `ogfAdmin` localize
- [x] Remove `icon()` wrapper; use `$shell->icon()` in tab callbacks and helpers
- [x] Adobe tab cards → `Plinth\Components::card()`
- [x] Apply `Components::card()` across tabs (upload, fonts, dashboard, diagnostics, guide, recommendations)
- [x] `Components::card()` auto-close + `wrap_body` option for flush lists

## Medium priority

- [ ] **Single tab/nav config** — define tabs once in `get_shell()`, derive `nav_groups` (~50 lines)
- [ ] **Toggle CSS → Plinth** — migrate `ogf-toggle-sm` / `ogf-toggle-xs` to `pln-toggle--sm` / `pln-toggle--xs`; delete duplicate CSS (~60 lines)
- [ ] **`.ogf-setting-select` → `pln-setting__select`** in custom font forms (~22 lines CSS)
- [ ] **Host/preload → Plinth Settings** — register in `register_settings()`, drop `ogf_save_settings` + `initFontToggles()` (~80 lines PHP+JS)
- [ ] **Welcome screen → `Shell::register_menu()`** — move OGF enqueue into `OGF_Admin_Page::enqueue_assets()` (~35 lines)

## Larger projects

- [ ] **Retire legacy Upload Fonts screen** — `class-ogf-upload-fonts-screen.php`, `admin/style.css`, `uploadFonts.js`; update E2E (~500 lines)
- [ ] **Components adoption** — use `recommendation_row()`, `stat_card()`, diagnostics helpers in dashboard/diagnostics tabs, or trim unused lib methods
- [ ] **Fix `Components::recommendation_row()` BEM** — `pln-rec-low` → `pln-rec--low`
- [ ] **Duplicate `ajaxPost`** — extend Plinth to accept custom nonce; remove copy in `admin-page.js`

## OSS packaging

- [ ] Extract `lib/` to standalone GitHub repo
- [ ] `lib/README.md` + `examples/hello-plinth.php`
- [ ] `Settings::from_json()` optional loader
- [ ] PHPUnit tests for `Settings::sanitize()` and registration
- [ ] Runtime Composer autoload (`vendor/autoload.php`)
- [ ] GPL-2.0 license file in `lib/`
