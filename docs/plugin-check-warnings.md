# Documented Plugin Check warnings — InvoiceForge for WooCommerce

This document records intentional or unavoidable Plugin Check warnings and explains why they are safe and acceptable. Link this file in CI failure comments so reviewers can quickly find the rationale.

---

## `direct_file_operations` — mPDF `$mpdf->Output()` calls

**File:** `includes/pdf/class-pdf-generator.php` (line ~69)

mPDF's `Output($path, \Mpdf\Output\Destination::FILE)` writes the rendered PDF directly to disk using PHP's internal file functions. This is a third-party library call that cannot be replaced with WP_Filesystem without forking mPDF.

The destination path is always:

1. Rooted at `wp_upload_dir()['basedir'] . '/invoiceforge/'` (see `invoiceforge_get_storage_dir()` in `includes/helpers/functions.php`)
2. The filename portion is sanitized via `sanitize_file_name()` before being passed to mPDF
3. The per-order subdirectory is constructed from `absint( $order->get_id() )` and `gmdate('Y')`

No user-controlled input reaches the file path without sanitization. The write destination is within the standard WordPress uploads directory.

**Why WP_Filesystem cannot be used here:** `$mpdf->Output()` is an internal mPDF method. Routing it through WP_Filesystem would require patching the library. All *plugin-owned* file operations (reading CSS, writing `.htaccess`/`index.php`, reading stored PDFs for streaming) use `invoiceforge_fs_read()` and `invoiceforge_fs_write()` wrappers.

---

## `direct_file_operations` — mPDF temp directory creation

**File:** `includes/pdf/class-pdf-generator.php` (line ~189)

`wp_mkdir_p()` is used (WordPress API), but mPDF internally writes its font cache and temp files to the directory using PHP file functions. This is unavoidable library behavior.

The temp directory is `get_temp_dir() . 'invoiceforge/'`, which resolves to the system temp directory or the value configured in `WP_TEMP_DIR`. No user input affects this path.

---

## `direct_db_query` — Invoice number sequence counter

**File:** `includes/numbering/class-number-store.php`

Invoice numbers use `$wpdb->query()` and `$wpdb->get_var()` directly (rather than `get_option`/`update_option`) to enable atomic `SELECT ... FOR UPDATE`-style locking via transients. This prevents duplicate invoice numbers under concurrent order creation (e.g., flash sales, background WP-Cron jobs).

All queries use `$wpdb->prepare()` with `%d`/`%s` placeholders. The `// phpcs:ignore WordPress.DB.DirectDatabaseQuery` inline suppression is present on each call.

---

## `vendor` directory size

The `vendor/mpdf/` directory is committed to version control and included in the plugin ZIP. mPDF requires bundled fonts for Unicode/RTL support; removing them would break non-Latin invoice rendering.

**Approximate sizes:**
- `vendor/mpdf/mpdf/ttfonts/` — ~15 MB (Unicode fonts)
- Total vendor/ — ~20 MB

The release ZIP size guard in `.github/workflows/release.yml` (Pro) flags builds >30 MB to catch unexpected growth. For the Free plugin, this is documented here for Plugin Check reviewers.

If WordPress.org flags the ZIP size: the `ttfonts/` directory can be pruned to Latin-only fonts (DejaVu, FreeSerif), reducing vendor size to ~3 MB. A separate PR will implement font pruning if required.

---

## Summary table

| Warning | File | Suppressed? | Reason |
|---------|------|-------------|--------|
| `direct_file_operations` (mPDF Output) | `class-pdf-generator.php:69` | No (third-party) | mPDF internal — path is sanitized, within uploads dir |
| `direct_file_operations` (mPDF temp) | `class-pdf-generator.php:189` | No (third-party) | mPDF internal — path is system temp dir |
| `direct_db_query` (number store) | `class-number-store.php` | Inline `phpcs:ignore` | Atomic sequence counter — uses `$wpdb->prepare()` |
| Large vendor directory | `vendor/mpdf/` | N/A | mPDF bundled fonts required for Unicode PDF rendering |
