<?php
/**
 * Plugin settings page.
 *
 * @package InvoiceForge
 */

namespace InvoiceForge\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and renders the InvoiceForge settings page under WooCommerce.
 */
class Settings_Page {

	private const MENU_SLUG = 'invoiceforge-settings';

	public function add_menu_page(): void {
		add_submenu_page(
			'woocommerce',
			__( 'InvoiceForge Settings', 'invoiceforge-for-woocommerce' ),
			__( 'InvoiceForge', 'invoiceforge-for-woocommerce' ),
			'manage_woocommerce',
			self::MENU_SLUG,
			[ $this, 'render_page' ]
		);
	}

	public function register_settings(): void {
		// General section.
		add_settings_section( 'invoiceforge_general', __( 'General', 'invoiceforge-for-woocommerce' ), '__return_false', self::MENU_SLUG );

		$this->register_field( 'invoiceforge_shop_name', __( 'Shop Name', 'invoiceforge-for-woocommerce' ), 'text' );
		$this->register_field( 'invoiceforge_shop_address', __( 'Shop Address', 'invoiceforge-for-woocommerce' ), 'textarea' );
		$this->register_field( 'invoiceforge_shop_vat_number', __( 'VAT / Tax Number', 'invoiceforge-for-woocommerce' ), 'text' );
		$this->register_field( 'invoiceforge_tax_label', __( 'Tax Label', 'invoiceforge-for-woocommerce' ), 'text' );
		$this->register_field( 'invoiceforge_footer_text', __( 'Footer Text', 'invoiceforge-for-woocommerce' ), 'textarea' );
		$this->register_field( 'invoiceforge_logo_url', __( 'Logo URL', 'invoiceforge-for-woocommerce' ), 'url' );

		// Numbering section.
		add_settings_section( 'invoiceforge_numbering', __( 'Invoice Numbering', 'invoiceforge-for-woocommerce' ), '__return_false', self::MENU_SLUG );
		$this->register_field( 'invoiceforge_invoice_prefix', __( 'Invoice Prefix', 'invoiceforge-for-woocommerce' ), 'text', 'invoiceforge_numbering' );
		$this->register_field( 'invoiceforge_invoice_suffix', __( 'Invoice Suffix', 'invoiceforge-for-woocommerce' ), 'text', 'invoiceforge_numbering' );

		// Automation section.
		add_settings_section( 'invoiceforge_automation', __( 'Automation', 'invoiceforge-for-woocommerce' ), '__return_false', self::MENU_SLUG );
		$this->register_field( 'invoiceforge_generate_on_statuses', __( 'Generate PDF on Status', 'invoiceforge-for-woocommerce' ), 'statuses', 'invoiceforge_automation' );
		$this->register_field( 'invoiceforge_attach_to_emails', __( 'Attach to Emails', 'invoiceforge-for-woocommerce' ), 'emails', 'invoiceforge_automation' );
		$this->register_field( 'invoiceforge_generate_packing_slip', __( 'Auto-generate Packing Slip', 'invoiceforge-for-woocommerce' ), 'checkbox', 'invoiceforge_automation' );

		// Advanced section.
		add_settings_section( 'invoiceforge_advanced', __( 'Advanced', 'invoiceforge-for-woocommerce' ), '__return_false', self::MENU_SLUG );
		$this->register_field( 'invoiceforge_remove_data_on_uninstall', __( 'Remove All Data on Uninstall', 'invoiceforge-for-woocommerce' ), 'checkbox', 'invoiceforge_advanced' );

		// Register sanitise callbacks.
		$text_fields = [
			'invoiceforge_shop_name', 'invoiceforge_shop_vat_number', 'invoiceforge_tax_label',
			'invoiceforge_logo_url', 'invoiceforge_invoice_prefix', 'invoiceforge_invoice_suffix',
		];
		foreach ( $text_fields as $field ) {
			register_setting( self::MENU_SLUG, $field, [ 'sanitize_callback' => 'sanitize_text_field' ] );
		}

		$textarea_fields = [ 'invoiceforge_shop_address', 'invoiceforge_footer_text' ];
		foreach ( $textarea_fields as $field ) {
			register_setting( self::MENU_SLUG, $field, [ 'sanitize_callback' => 'sanitize_textarea_field' ] );
		}

		$array_fields = [ 'invoiceforge_generate_on_statuses', 'invoiceforge_attach_to_emails' ];
		foreach ( $array_fields as $field ) {
			register_setting( self::MENU_SLUG, $field, [ 'sanitize_callback' => [ $this, 'sanitize_array' ] ] );
		}

		$checkbox_fields = [ 'invoiceforge_generate_packing_slip', 'invoiceforge_remove_data_on_uninstall' ];
		foreach ( $checkbox_fields as $field ) {
			register_setting( self::MENU_SLUG, $field, [ 'sanitize_callback' => [ $this, 'sanitize_checkbox' ] ] );
		}
	}

	/**
	 * @param array<int, string> $value Raw value from POST.
	 * @return array<int, string>
	 */
	public function sanitize_array( $value ): array {
		if ( ! is_array( $value ) ) {
			return [];
		}
		return array_map( 'sanitize_text_field', $value );
	}

	public function sanitize_checkbox( $value ): string {
		return ( 'yes' === $value || '1' === $value || true === $value ) ? 'yes' : 'no';
	}

	public function render_page(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'InvoiceForge Settings', 'invoiceforge-for-woocommerce' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( self::MENU_SLUG );
				do_settings_sections( self::MENU_SLUG );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	private function register_field( string $key, string $label, string $type, string $section = 'invoiceforge_general' ): void {
		add_settings_field(
			$key,
			$label,
			[ $this, 'render_field' ],
			self::MENU_SLUG,
			$section,
			[ 'key' => $key, 'type' => $type, 'label' => $label ]
		);
	}

	public function render_field( array $args ): void {
		$key   = $args['key'];
		$type  = $args['type'];
		$value = get_option( $key, '' );

		switch ( $type ) {
			case 'textarea':
				printf(
					'<textarea name="%s" rows="4" cols="50" class="large-text">%s</textarea>',
					esc_attr( $key ),
					esc_textarea( (string) $value )
				);
				break;

			case 'checkbox':
				printf(
					'<input type="checkbox" name="%s" value="yes" %s>',
					esc_attr( $key ),
					checked( 'yes', (string) $value, false )
				);
				break;

			case 'statuses':
				$statuses  = wc_get_order_statuses();
				$selected  = (array) $value;
				$clean_selected = array_map( static fn( $s ) => ltrim( $s, 'wc-' ), $selected );
				foreach ( $statuses as $slug => $label ) {
					$slug_clean = ltrim( $slug, 'wc-' );
					printf(
						'<label style="display:block;"><input type="checkbox" name="%s[]" value="%s" %s> %s</label>',
						esc_attr( $key ),
						esc_attr( $slug_clean ),
						checked( in_array( $slug_clean, $clean_selected, true ), true, false ),
						esc_html( $label )
					);
				}
				break;

			case 'emails':
				$emails   = WC()->mailer()->get_emails();
				$selected = (array) $value;
				foreach ( $emails as $email_id => $email ) {
					printf(
						'<label style="display:block;"><input type="checkbox" name="%s[]" value="%s" %s> %s</label>',
						esc_attr( $key ),
						esc_attr( $email_id ),
						checked( in_array( $email_id, $selected, true ), true, false ),
						esc_html( $email->get_title() )
					);
				}
				break;

			default:
				printf(
					'<input type="%s" name="%s" value="%s" class="regular-text">',
					esc_attr( $type ),
					esc_attr( $key ),
					esc_attr( (string) $value )
				);
		}
	}
}
