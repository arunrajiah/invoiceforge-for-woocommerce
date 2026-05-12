=== InvoiceForge for WooCommerce ===
Contributors: arunrajiah
Tags: pdf, invoice, packing-slip, woocommerce-pdf-invoices, pdf-invoice-woocommerce, woocommerce, invoice-generator, order-invoice
Requires at least: 6.2
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 0.1.0
WC requires at least: 7.0
WC tested up to: 9.9
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Beautiful PDF invoices and packing slips for WooCommerce — auto-generated, attached to order emails, and downloadable from My Account.

== Description ==

**InvoiceForge for WooCommerce** gives your store professional PDF invoices and packing slips, automatically.

= Features =

* **Auto-generate PDF invoices** when an order reaches "Completed" status (or any status you choose)
* **Auto-generate packing slips** alongside invoices
* **Beautiful default template** — clean, modern, with your logo and branding
* **Attach invoice to emails** — automatically included in the Order Completed email sent to customers
* **Customer downloads** — a "Download Invoice" button appears on My Account → Orders for every completed order
* **Admin controls** — generate or regenerate invoices directly from the order edit screen and orders list
* **Bulk download** — select multiple orders and download all invoices as a single ZIP
* **Sequential invoice numbering** — configurable prefix/suffix (e.g. INV-2026-00042)
* **Settings page** — logo upload, shop info, tax label, footer text, status and email configuration
* **REST API** — programmatic access to invoices
* **Theme overrides** — copy templates into your theme for full customisation
* **Developer-friendly** — extensive hook/filter registry for Pro plugins and integrations

= Advanced features available separately =

Visual template designer with 10+ templates, credit notes, proforma invoices, delivery notes, bulk export with date filters, EU e-invoicing (ZUGFeRD/XRechnung), B2B fields (VAT ID, NET-30 payment terms), multi-language invoices, and cloud backup (Dropbox, Google Drive, S3).

= Compatibility =

* WooCommerce 7.0+
* High-Performance Order Storage (HPOS) fully supported
* PHP 7.4–8.3

= Template Customisation =

Copy `wp-content/plugins/invoiceforge-for-woocommerce/templates/pdf/invoice/default.php` to `wp-content/themes/your-theme/woocommerce/invoiceforge/invoice/default.php` and edit freely.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/invoiceforge-for-woocommerce` directory, or install via the WordPress plugin screen.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Go to **WooCommerce → InvoiceForge** and configure your shop details.
4. Place a test order and complete it — an invoice PDF will be generated automatically.

== Frequently Asked Questions ==

= Does it work with HPOS (High-Performance Order Storage)? =

Yes. InvoiceForge fully supports WooCommerce's High-Performance Order Storage and declares compatibility.

= Can I customise the invoice template? =

Yes. Copy the template file from the plugin's `templates/pdf/invoice/` directory into your theme at `woocommerce/invoiceforge/invoice/` and edit the HTML/CSS. mPDF supports most CSS properties.

= Where are the PDF files stored? =

PDFs are stored in `wp-content/uploads/invoiceforge/{year}/{order-id}/` and are protected from direct URL access via `.htaccess`.

= Will it slow down order processing? =

PDF generation typically takes under 0.5 seconds and happens asynchronously after the order status changes. For large stores, consider using a caching layer.

= What PDF library does it use? =

InvoiceForge bundles mPDF (LGPL v2.1), a mature open-source PHP library with excellent HTML/CSS support including custom fonts, RTL text, and Unicode characters.

= Where is the invoice number stored? =

Invoice numbers are stored in WooCommerce order meta and are fully searchable in the admin. The sequential counter is stored in `wp_options`.

== Screenshots ==

1. Default invoice template — clean, modern, branded.
2. InvoiceForge settings page under WooCommerce menu.
3. Download Invoice button on My Account → Orders.
4. Bulk download invoices as ZIP from orders list.

== Changelog ==

= 0.1.0 =
* Initial release.
* Auto-generate PDF invoice and packing slip on order completion.
* Attach invoice to Order Completed email.
* Customer download from My Account.
* Sequential invoice numbering with prefix/suffix.
* Settings page for shop info, logo, and automation.
* Bulk download as ZIP.
* REST API endpoints.

== Upgrade Notice ==

= 0.1.0 =
Initial release — no upgrade steps required.
