# InvoiceForge for WooCommerce

> Beautiful, automatic PDF invoices and packing slips for WooCommerce — free, open-source, and ready to go in minutes.

[![WordPress tested](https://img.shields.io/badge/WordPress-6.2%2B-blue?logo=wordpress)](https://wordpress.org/plugins/invoiceforge-for-woocommerce/)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-7.0%2B-purple?logo=woocommerce)](https://woocommerce.com/)
[![PHP](https://img.shields.io/badge/PHP-7.4%E2%80%938.3-777BB4?logo=php)](https://php.net/)
[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-green.svg)](https://www.gnu.org/licenses/gpl-2.0)

---

## What it does

InvoiceForge automatically generates a professional PDF invoice the moment an order is completed. The invoice is attached to the customer's order confirmation email and available for download from My Account. No setup beyond entering your shop details — it just works.

**For store admins:**
- Download or regenerate invoices from the order edit screen and the orders list
- Bulk-download all invoices for a date range as a single ZIP file
- Sequential, configurable invoice numbering (e.g. `INV-2026-00042`)

**For customers:**
- "Download Invoice" button on My Account → Orders for every completed order

---

## Features

| Feature | Details |
|---|---|
| 📄 Auto PDF invoices | Generated on order completion, attached to the confirmation email |
| 📦 Packing slips | One-click from the order admin page |
| 🔢 Sequential numbering | Configurable prefix, suffix, and year/month tokens |
| ⬇️ Bulk ZIP export | Select orders → download all invoices as a ZIP |
| 🎨 Customisable template | Copy to your theme and edit HTML/CSS freely |
| 📧 Email attachment | Invoice auto-attached to Order Completed email |
| 👤 My Account download | Customers download their own invoices |
| 🔌 WooCommerce native | HPOS-compatible, no external dependencies |
| 🪝 Developer hooks | Actions and filters throughout for custom integrations |
| 🔗 REST API | Programmatic access to invoices |

---

## Installation

### From WordPress.org (recommended)

1. In your WordPress admin go to **Plugins → Add New**
2. Search for **InvoiceForge**
3. Click **Install Now** then **Activate**

### Manual install

1. Download the latest ZIP from the [Releases](../../releases) page
2. Go to **Plugins → Add New → Upload Plugin**
3. Upload the ZIP and activate

### After activation

1. Go to **WooCommerce → InvoiceForge → Settings**
2. Enter your shop name, address, and upload your logo
3. Place a test order and complete it — a PDF invoice will be generated and attached to the confirmation email

---

## Configuration

All settings are under **WooCommerce → InvoiceForge → Settings**:

| Setting | Description |
|---|---|
| **Shop name / address** | Printed in the invoice header |
| **Logo** | Uploaded via the WordPress media library |
| **Invoice number prefix** | e.g. `INV-` or `2026-` |
| **Next invoice number** | Override the counter if migrating from another plugin |
| **Trigger status** | Which WooCommerce order status triggers generation (default: Completed) |
| **Email attachment** | Which outgoing email the PDF is attached to |
| **Tax label** | How tax is labelled on the invoice (e.g. VAT, GST) |
| **Footer text** | Bank details, payment instructions, or legal copy |

---

## Template customisation

The default template is a clean, modern HTML/CSS document rendered by [mPDF](https://mpdf.github.io/).

To customise it, copy the template into your theme:

```
wp-content/plugins/invoiceforge-for-woocommerce/templates/pdf/invoice/default.php
  → wp-content/themes/{your-theme}/woocommerce/invoiceforge/invoice/default.php
```

mPDF supports most CSS 2.1 properties including custom fonts, page margins, headers/footers, and table borders. The template receives a `$data` array — see `includes/pdf/class-invoice-document.php` for the full structure.

---

## Hooks reference

All actions and filters are documented in `includes/extensibility/class-hook-registry.php`.

Key examples:

```php
// Modify invoice data before PDF generation
add_filter( 'invoiceforge_invoice_data', function( array $data, WC_Order $order ): array {
    $data['custom_field'] = get_post_meta( $order->get_id(), '_my_field', true );
    return $data;
}, 10, 2 );

// Run code after a PDF is generated
add_action( 'invoiceforge_pdf_generated', function( int $order_id, string $type, string $path ): void {
    // $type is 'invoice' or 'packing_slip'
    // $path is the absolute path to the PDF file
}, 10, 3 );

// Customise the invoice number format
add_filter( 'invoiceforge_invoice_number', function( string $number, int $order_id ): string {
    return 'MY-' . $number;
}, 10, 2 );
```

---

## Pro add-on

[InvoiceForge Pro](https://hub.arunrajiah.com/products/invoiceforge-pro) extends the free plugin with:

- 🎨 **Visual template designer** — 10 pre-built professional themes, live preview
- 📝 **Credit notes** — auto-generated on WooCommerce refunds
- 📋 **Proforma invoices** — for quotes and pre-payment orders
- 🚚 **Delivery notes** — separate document type for fulfilment
- 🇪🇺 **ZUGFeRD / Factur-X** — embedded XML for EU e-invoicing compliance
- 🇩🇪 **XRechnung UBL 2.1** — for German public-sector buyers
- 🏢 **B2B fields** — VAT ID at checkout, injected into invoices
- 🌍 **WPML & Polylang** — invoices in the customer's language
- ☁️ **Cloud backup** — auto-upload to Dropbox, Google Drive, or S3
- 🎨 **Custom CSS** — override any template style without editing files

[**Get InvoiceForge Pro — $49/yr →**](https://hub.arunrajiah.com/products/invoiceforge-pro)

---

## Local development

```bash
# Install all dependencies (including dev tools)
composer install

# Lint (PHPCS + WordPress Coding Standards)
composer lint

# Auto-fix fixable lint issues
composer lint-fix

# Run PHPUnit tests
composer test
```

### WP test suite setup

```bash
bash tests/install-wp-tests.sh wordpress_test root root 127.0.0.1 latest
```

### Release process

1. Bump the version in `invoiceforge-for-woocommerce.php` (header) and `readme.txt` (`Stable tag:`)
2. Add a changelog entry to `CHANGELOG.md` and `readme.txt`
3. Push to `main` — CI runs lint, PHPUnit, and Plugin Check automatically
4. Tag: `git tag v0.x.x && git push origin v0.x.x`
5. The `deploy-wp-org.yml` workflow deploys to WordPress.org SVN automatically

---

## Support

- **WordPress.org forums** — for general questions and community help
- **Bug reports** — [GitHub Issues](../../issues)
- **Pro support** — [hub.arunrajiah.com/support](https://hub.arunrajiah.com/support)

---

## License

GPL v2 or later — see [LICENSE](LICENSE). Free to use, modify, and distribute.
