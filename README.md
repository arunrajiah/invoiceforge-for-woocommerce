# InvoiceForge for WooCommerce

Beautiful PDF invoices and packing slips for WooCommerce — free on WordPress.org.

## Requirements

- WordPress 6.2+
- WooCommerce 7.0+
- PHP 7.4–8.3

## Local Development

```bash
# Install all dependencies (including dev tools)
composer install

# Lint
composer lint

# Auto-fix lint issues
composer lint-fix

# Run tests (requires WP test suite — see below)
composer test
```

### WP Test Suite Setup

```bash
bash tests/install-wp-tests.sh wordpress_test root root 127.0.0.1 latest
```

## Release Process

1. Bump version in `invoiceforge-for-woocommerce.php` and `readme.txt`.
2. Update `CHANGELOG.md`.
3. Tag: `git tag v0.x.x && git push --tags`.
4. The `deploy-wp-org.yml` CI workflow deploys to WordPress.org SVN automatically.

## Template Customisation

Copy `templates/pdf/invoice/default.php` to your theme at:
`wp-content/themes/{your-theme}/woocommerce/invoiceforge/invoice/default.php`

## Hooks Reference

See `includes/extensibility/class-hook-registry.php` for the full list of actions and filters.

## License

GPL v2 or later — see [LICENSE](LICENSE).
