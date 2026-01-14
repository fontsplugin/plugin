---
name: E2E Test Coverage Plan - Refined
overview: Create comprehensive e2e tests for Olympus Google Fonts plugin covering typography controls, font selection (including custom fonts), live preview, saving, and frontend rendering.
todos:
  - id: basic-typography-tests
    content: Create test suite for basic typography controls (body, headings, inputs) with font selection and save verification
    status: pending
  - id: typography-properties-helpers
    content: "Create helper functions for typography properties: selectFontWeight, setFontSize, selectColor, setLineHeight, verifyFontInDropdown, verifyCustomFontsSection"
    status: pending
  - id: typography-properties-tests
    content: Add tests for typography properties (weight, size, color, line height) on body typography control
    status: pending
    dependencies:
      - typography-properties-helpers
  - id: custom-fonts-tests
    content: "Add tests for custom fonts: verify they appear in dropdown, can be selected, and verify font categories"
    status: pending
    dependencies:
      - typography-properties-helpers
  - id: save-publish-tests
    content: Enhance save/publish flow tests to verify button state changes and persistence after reload
    status: pending
  - id: advanced-typography-tests
    content: Add tests for advanced typography controls (site title, navigation) in their respective sections
    status: pending
  - id: frontend-rendering-tests
    content: Create tests to verify saved fonts appear correctly on frontend with correct CSS selectors using getComputedStyle
    status: pending
    dependencies:
      - basic-typography-tests
  - id: font-list-validation
    content: Add tests to verify expected fonts are in dropdown (common Google Fonts, system fonts, custom fonts section)
    status: pending
---

# E2E Test Coverage Plan for Olympus Google Fonts (Refined)

## Current State

- Basic login and navigation tests exist
- One test for body typography font selection exists
- Helper function `selectRandomChosenOption` for Chosen.js dropdowns
- Navigation helper `navigateToCustomizerSection` added

## Test Structure

### 1. Basic Typography Controls (`ogf_basic` section)

Test the three main typography controls in the Basic Settings section:

- **Base Typography** (`ogf_body_typography`)
  - Select random font from dropdown
  - Verify font selection persists
  - Save and verify button state changes
  - Verify font appears on frontend

- **Headings Typography** (`ogf_headings_typography`)
  - Select random font
  - Verify selection
  - Save and verify

- **Buttons and Inputs Typography** (`ogf_inputs_typography`)
  - Select random font
  - Verify selection
  - Save and verify

### 2. Typography Properties

For each typography control, test additional properties:

- **Font Weight**
  - Select different weights (Normal/400, Bold/700, etc.)
  - Verify selection in dropdown
  - Test with font selection
  - Save and verify

- **Font Size**
  - Set font size using slider/input
  - Verify value updates in input field
  - Save and verify

- **Font Color**
  - Select color using WordPress color picker
  - Verify color is applied (check color input value)
  - Save and verify

- **Line Height**
  - Adjust line height using slider/input
  - Verify value updates
  - Save and verify

### 3. Custom Fonts Testing (NEW - Core Feature)

Test that custom fonts appear in dropdowns and can be selected:

- **Verify Custom Fonts in Dropdown**
  - Open font family dropdown
  - Verify "- Custom Fonts -" section header exists
  - Verify custom fonts appear in the list (if any exist)
  - Verify custom fonts have "cf-" prefix in their values

- **Select Custom Font**
  - If custom fonts exist, select one
  - Verify selection persists
  - Save and verify

- **Custom Font Categories**
  - Verify dropdown shows all categories:
    - Default Font
    - Custom Fonts (if any)
    - Typekit Fonts (if any)
    - System Fonts
    - Google Fonts

### 4. Advanced Typography Controls

Test key advanced controls:

- **Site Title Typography** (`ogf_site_title_typography`)
  - Navigate to Advanced > Branding section
  - Select font for site title
  - Verify and save

- **Navigation Typography** (`ogf_site_navigation_typography`)
  - Navigate to Advanced > Navigation section
  - Select font for navigation
  - Verify and save

### 5. Live Preview

Test that changes are visible in customizer preview:

- Change font and verify preview updates immediately
- Change color and verify preview updates
- Change size and verify preview updates
- Verify preview matches saved state

### 6. Frontend Rendering

Test that saved fonts appear correctly on frontend:

- Navigate to frontend after saving
- Verify font-family CSS is applied to correct selectors (e.g., `body` for base typography)
- Verify font weights/styles are applied
- Verify colors are applied
- Use `getComputedStyle()` to verify CSS values

### 7. Save/Publish Flow

Test the save functionality:

- Make changes and verify save button appears
- Click save and verify button changes to "Published"
- Verify button becomes disabled after save
- Reload customizer and verify changes persist
- Verify changes persist on frontend after reload

### 8. Font List Validation

Test that expected fonts are available:

- Verify dropdown is not empty
- Verify common Google Fonts are present (e.g., "Roboto", "Open Sans", "Lato")
- Verify system fonts section exists
- Verify custom fonts section exists (if custom fonts are added)

## Implementation Files

- `tests/e2e/customizer.spec.ts` - Main test file (already exists)
  - Add test suites for each typography control
  - Add tests for typography properties
  - Add custom font tests
  - Add frontend rendering tests

- Helper functions to add:
  - `navigateToCustomizerSection` - Already exists
  - `selectRandomChosenOption` - Already exists
  - `selectFontWeight` - Select font weight from dropdown (`.typography-font-weight select`)
  - `setFontSize` - Set font size using slider/input (`.typography-font-size input` or slider)
  - `selectColor` - Use WordPress color picker (`.typography-font-color .color-picker-hex`)
  - `setLineHeight` - Adjust line height (`.typography-line-height input` or slider)
  - `verifyFontInDropdown` - Check if a specific font exists in dropdown
  - `verifyCustomFontsSection` - Verify custom fonts section exists in dropdown
  - `verifyFrontendFont` - Check font-family CSS on frontend using `getComputedStyle`

## Test Organization

```typescript
test.describe('WordPress Admin', () => {
  // Login tests (existing)
});

test.describe('Customizer - Basic Typography', () => {
  test('should select font for body typography', ...);
  test('should select font for headings typography', ...);
  test('should select font for inputs typography', ...);
});

test.describe('Customizer - Typography Properties', () => {
  test('should set font weight', ...);
  test('should set font size', ...);
  test('should set font color', ...);
  test('should set line height', ...);
});

test.describe('Customizer - Custom Fonts', () => {
  test('should display custom fonts in dropdown', ...);
  test('should select custom font', ...);
  test('should verify font categories in dropdown', ...);
});

test.describe('Customizer - Advanced Typography', () => {
  test('should select font for site title', ...);
  test('should select font for navigation', ...);
});

test.describe('Save and Publish', () => {
  test('should save changes and verify button state', ...);
  test('should persist changes after reload', ...);
});

test.describe('Frontend Rendering', () => {
  test('should apply fonts to correct selectors', ...);
  test('should apply font properties (weight, color, size)', ...);
});
```

## Priority Order

1. **High Priority**: 

   - Basic typography controls (body, headings, inputs) - core functionality
   - Custom fonts in dropdown - core feature validation
   - Save/publish flow - critical user workflow

2. **Medium Priority**: 

   - Typography properties (weight, size, color, line height) - common use cases
   - Font list validation - ensures data integrity

3. **Low Priority**: 

   - Advanced typography controls - less commonly used
   - Frontend rendering verification - important but can be tested manually
   - Live preview - nice to have but preview is visual

## Notes

- Use random font selection to ensure tests work regardless of previous state
- Wait for actual state changes (button value, element visibility) rather than timeouts
- Test one property at a time to isolate issues
- Verify both customizer preview and frontend rendering
- For custom fonts: Test that they appear if they exist, but don't fail if none exist (use conditional checks)
- Use `getComputedStyle()` for frontend CSS verification rather than checking HTML attributes