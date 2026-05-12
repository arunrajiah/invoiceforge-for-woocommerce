<?php
/**
 * Core plugin class.
 *
 * @package InvoiceForge
 */

namespace InvoiceForge;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin orchestrator — singleton.
 */
final class InvoiceForge {

	/** @var InvoiceForge|null */
	private static ?InvoiceForge $instance = null;

	/** @var InvoiceForge_Loader */
	private InvoiceForge_Loader $loader;

	/** @var string */
	private string $version;

	private function __construct() {
		$this->version = INVOICEFORGE_VERSION;
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_frontend_hooks();
		$this->define_pdf_hooks();
		$this->define_api_hooks();
	}

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function load_dependencies(): void {
		require_once INVOICEFORGE_PLUGIN_DIR . 'includes/class-invoiceforge-loader.php';
		require_once INVOICEFORGE_PLUGIN_DIR . 'includes/class-invoiceforge-i18n.php';
		require_once INVOICEFORGE_PLUGIN_DIR . 'includes/class-invoiceforge-activator.php';
		require_once INVOICEFORGE_PLUGIN_DIR . 'includes/class-invoiceforge-deactivator.php';
		require_once INVOICEFORGE_PLUGIN_DIR . 'includes/extensibility/class-hook-registry.php';
		require_once INVOICEFORGE_PLUGIN_DIR . 'includes/helpers/functions.php';

		// PDF layer.
		require_once INVOICEFORGE_PLUGIN_DIR . 'includes/pdf/class-document.php';
		require_once INVOICEFORGE_PLUGIN_DIR . 'includes/pdf/class-invoice-document.php';
		require_once INVOICEFORGE_PLUGIN_DIR . 'includes/pdf/class-packing-slip-document.php';
		require_once INVOICEFORGE_PLUGIN_DIR . 'includes/pdf/class-template-loader.php';
		require_once INVOICEFORGE_PLUGIN_DIR . 'includes/pdf/class-pdf-generator.php';

		// Numbering.
		require_once INVOICEFORGE_PLUGIN_DIR . 'includes/numbering/class-number-store.php';
		require_once INVOICEFORGE_PLUGIN_DIR . 'includes/numbering/class-number-generator.php';

		// Admin.
		require_once INVOICEFORGE_PLUGIN_DIR . 'includes/admin/class-settings-page.php';
		require_once INVOICEFORGE_PLUGIN_DIR . 'includes/admin/class-order-actions.php';
		require_once INVOICEFORGE_PLUGIN_DIR . 'includes/admin/class-bulk-actions.php';
		require_once INVOICEFORGE_PLUGIN_DIR . 'includes/admin/class-download-handler.php';
		require_once INVOICEFORGE_PLUGIN_DIR . 'includes/admin/class-admin.php';

		// Emails.
		require_once INVOICEFORGE_PLUGIN_DIR . 'includes/emails/class-email-attachment.php';

		// Frontend.
		require_once INVOICEFORGE_PLUGIN_DIR . 'includes/frontend/class-my-account-invoices.php';

		// REST API.
		require_once INVOICEFORGE_PLUGIN_DIR . 'includes/api/class-rest-api.php';

		$this->loader = new InvoiceForge_Loader();
	}

	private function set_locale(): void {
		$i18n = new InvoiceForge_I18n();
		$this->loader->add_action( 'plugins_loaded', $i18n, 'load_plugin_textdomain' );
	}

	private function define_admin_hooks(): void {
		// Download handler runs on both admin_init and init (for frontend My Account downloads).
		$download_handler = new Admin\Download_Handler();
		$download_handler->init();

		if ( ! is_admin() ) {
			return;
		}
		$admin = new Admin\Admin();
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );

		$settings = new Admin\Settings_Page();
		$this->loader->add_action( 'admin_menu', $settings, 'add_menu_page' );
		$this->loader->add_action( 'admin_init', $settings, 'register_settings' );

		$order_actions = new Admin\Order_Actions();
		$this->loader->add_filter( 'woocommerce_order_actions', $order_actions, 'add_order_actions' );
		$this->loader->add_action( 'woocommerce_order_action_invoiceforge_generate_invoice', $order_actions, 'handle_generate_invoice' );
		$this->loader->add_action( 'woocommerce_order_action_invoiceforge_generate_packing_slip', $order_actions, 'handle_generate_packing_slip' );
		$this->loader->add_filter( 'woocommerce_admin_order_actions', $order_actions, 'add_list_actions', 10, 2 );

		$bulk = new Admin\Bulk_Actions();
		$this->loader->add_filter( 'bulk_actions-edit-shop_order', $bulk, 'register_bulk_actions' );
		$this->loader->add_filter( 'bulk_actions-woocommerce_page_wc-orders', $bulk, 'register_bulk_actions' );
		$this->loader->add_filter( 'handle_bulk_actions-edit-shop_order', $bulk, 'handle_bulk_actions', 10, 3 );
		$this->loader->add_filter( 'handle_bulk_actions-woocommerce_page_wc-orders', $bulk, 'handle_bulk_actions', 10, 3 );
	}

	private function define_frontend_hooks(): void {
		$my_account = new Frontend\My_Account_Invoices();
		$this->loader->add_filter( 'woocommerce_my_account_my_orders_actions', $my_account, 'add_download_invoice_action', 10, 2 );
		$this->loader->add_action( 'wp_enqueue_scripts', $my_account, 'enqueue_styles' );
	}

	private function define_pdf_hooks(): void {
		$email_attachment = new Emails\Email_Attachment();
		$this->loader->add_filter( 'woocommerce_email_attachments', $email_attachment, 'attach_invoice', 10, 3 );

		// Auto-generate PDF on order status change.
		$generator = new PDF\PDF_Generator();
		$this->loader->add_action( 'woocommerce_order_status_completed', $generator, 'auto_generate_on_status_change' );
	}

	private function define_api_hooks(): void {
		$rest = new API\REST_API();
		$this->loader->add_action( 'rest_api_init', $rest, 'register_routes' );
	}

	public function run(): void {
		$this->loader->run();
		do_action( 'invoiceforge_loaded' );
	}

	public function get_version(): string {
		return $this->version;
	}

	public function get_loader(): InvoiceForge_Loader {
		return $this->loader;
	}
}
