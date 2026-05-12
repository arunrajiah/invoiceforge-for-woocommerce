# Changelog

All notable changes to InvoiceForge for WooCommerce will be documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [0.1.0] — 2026-05-12

### Added
- Auto-generate PDF invoice on order completion (configurable statuses).
- Auto-generate packing slip alongside invoice.
- Default modern invoice template rendered via mPDF.
- Attach invoice PDF to WooCommerce "Order Completed" email.
- Customer "Download Invoice" button on My Account → Orders.
- Admin: Generate / Regenerate invoice from order edit screen and orders list.
- Bulk download invoices as ZIP from orders list (current page).
- Sequential invoice numbering with configurable prefix and suffix.
- Settings page under WooCommerce → InvoiceForge.
- REST API: `GET /wp-json/invoiceforge/v1/invoices/{order_id}`.
- REST API: `POST /wp-json/invoiceforge/v1/invoices/{order_id}/regenerate`.
- Full hook/filter registry for Pro plugin and third-party extensibility.
- HPOS (High-Performance Order Storage) compatibility declared.
- GitHub Actions: lint, PHPUnit, Plugin Check, WP.org deploy.
