# Testing & Safe Release Guide

> **For a plugin with 200,000+ users, rigorous testing is essential.**

This guide outlines how to safely make changes without breaking sites.

---

## 🧪 Testing Infrastructure

### Quick Start

```bash
# Install dependencies
composer install

# Run unit tests (fast, no WordPress needed)
composer test:unit

# Run code quality checks
composer lint
```

### Test Types

| Type | Command | What It Tests | Speed |
|------|---------|---------------|-------|
| Unit | `composer test:unit` | Isolated functions, data structures | ⚡ Fast |
| Integration | `composer test:integration` | WordPress hooks, database | 🐢 Slow |
| PHPCS | `composer lint` | Code style, PHP compatibility | ⚡ Fast |

---

## 🔒 Safe Release Process

### 1. Development Phase

```
┌─────────────────────────────────────────────────────────┐
│  feature branch → tests pass → code review → develop   │
└─────────────────────────────────────────────────────────┘
```

- **Never commit directly to main/master**
- All changes must pass CI pipeline
- Request code review for significant changes

### 2. Pre-Release Checklist

Before any release, verify:

- [ ] All tests pass locally: `composer test`
- [ ] No PHPCS errors: `composer lint`
- [ ] Blocks build successfully: `cd blocks && npm run build`
- [ ] Changelog updated with clear descriptions
- [ ] Version bumped in main plugin file
- [ ] Tested on minimum supported versions (PHP 5.6, WP 6.0)

### 3. Beta Testing (Recommended for Major Changes)

For significant updates, create a beta release:

```bash
# Tag a beta release
git tag v3.5.0-beta1
git push origin v3.5.0-beta1
```

Announce to beta testers:
- WordPress.org support forum
- Plugin website/newsletter
- Twitter/social media

**Wait 1-2 weeks for feedback before full release.**

### 4. Production Release

```bash
# After beta period
git tag v3.5.0
git push origin v3.5.0
```

The GitHub Actions workflow will:
1. Run all tests
2. Build production assets
3. Create a draft release for your review
4. Generate release notes from changelog

---

## 📊 What to Test Before Each Release

### Critical Paths (Always Test)

| Feature | How to Test |
|---------|-------------|
| Font loading | Assign a font, verify it loads on frontend |
| Customizer controls | Each control type renders and saves |
| Block editor | Google Fonts block works in new post |
| Classic Editor | Font dropdown works |
| Performance | No render-blocking resources |

### Compatibility Matrix

Test with popular themes/plugins:

| Integration | Test File | What to Verify |
|-------------|-----------|----------------|
| Elementor | `compatibility/elementor.php` | Fonts appear in Elementor |
| Divi | `compatibility/divi-builder.php` | Fonts in Divi settings |
| WooCommerce | `compatibility/woocommerce.php` | Shop pages styled |

### PHP Version Testing

Use Docker to test across PHP versions:

```bash
# Quick PHP version test
docker run --rm -v $(pwd):/app -w /app php:7.4-cli vendor/bin/phpunit --testsuite unit
docker run --rm -v $(pwd):/app -w /app php:8.0-cli vendor/bin/phpunit --testsuite unit
docker run --rm -v $(pwd):/app -w /app php:8.2-cli vendor/bin/phpunit --testsuite unit
```

---

## 🚨 When Things Go Wrong

### Hotfix Process

If a critical bug is found in production:

```bash
# 1. Create hotfix branch from main
git checkout main
git checkout -b hotfix/critical-bug

# 2. Fix the issue with minimal changes
# 3. Test thoroughly
# 4. Create patch release
git tag v3.4.1
git push origin v3.4.1
```

### Rollback on WordPress.org

Contact WordPress.org plugin team to:
- Remove broken version from SVN
- Restore previous stable version

---

## 📝 Writing Good Tests

### Unit Test Example

```php
<?php
class MyFeatureTest extends OGF_Unit_TestCase {
    
    public function test_font_weight_is_valid() {
        $weight = '400';
        $this->assertTrue(
            $this->is_valid_weight($weight),
            'Normal weight should be valid'
        );
    }
    
    public function test_invalid_weight_rejected() {
        $weight = '999';
        $this->assertFalse(
            $this->is_valid_weight($weight),
            'Invalid weight should be rejected'
        );
    }
}
```

### Test Coverage Goals

| Area | Target Coverage |
|------|-----------------|
| Core font functions | 80%+ |
| CSS output | 70%+ |
| Compatibility files | 50%+ |
| Admin UI | Manual testing |

---

## 🔄 Continuous Integration

GitHub Actions runs on every push/PR:

1. **PHPCS** - Code style enforcement
2. **Unit Tests** - PHP 7.4, 8.0, 8.1, 8.2, 8.3
3. **Integration Tests** - WP 6.0, latest, trunk
4. **JS Build** - Blocks compile successfully

### Required Status Checks

Configure in GitHub repo settings:
- `phpcs`
- `unit-tests (7.4)`
- `unit-tests (8.2)`
- `integration-tests (WP latest)`

---

## 💡 Tips for Confident Releases

1. **Small, focused changes** - Easier to test and debug
2. **One feature per release** - Simpler rollback if needed
3. **Semantic versioning** - Users know what to expect
   - PATCH (3.4.x): Bug fixes only
   - MINOR (3.x.0): New features, backwards compatible
   - MAJOR (x.0.0): Breaking changes
4. **Detailed changelogs** - Users understand what changed
5. **Monitor support forums** - 24-48h after release
6. **Gradual rollout** - Use beta tags for major changes

---

## 📚 Resources

- [WordPress Plugin Handbook - Testing](https://developer.wordpress.org/plugins/testing/)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Brain\Monkey (WP Mocking)](https://brain-wp.github.io/BrainMonkey/)
- [WordPress.org Plugin Guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/)
