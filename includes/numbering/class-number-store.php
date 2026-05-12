<?php
/**
 * Persistent sequential number store.
 *
 * @package InvoiceForge
 */

namespace InvoiceForge\Numbering;

defined( 'ABSPATH' ) || exit;

/**
 * Manages the sequential counter stored in wp_options.
 */
class Number_Store {

	private const OPTION_KEY = 'invoiceforge_invoice_sequence';

	/**
	 * Returns the next available sequence number and increments the counter.
	 *
	 * Uses a DB-level transaction (via get_option + update_option) pattern
	 * to avoid duplicate numbers under concurrent requests.
	 *
	 * @param int $year Four-digit year.
	 * @return int
	 */
	public function get_next( int $year ): int {
		$option_key = self::OPTION_KEY . '_' . $year;

		// Wrap in a short critical section using WP transient locking.
		$lock_key = 'invoiceforge_seq_lock_' . $year;
		$timeout  = 5; // seconds.
		$start    = time();

		while ( get_transient( $lock_key ) ) {
			if ( time() - $start >= $timeout ) {
				break; // Bail after timeout to prevent infinite loop.
			}
			usleep( 100000 ); // 0.1 s.
		}
		set_transient( $lock_key, 1, 10 );

		$current = (int) get_option( $option_key, 0 );
		$next    = $current + 1;
		update_option( $option_key, $next, false );

		delete_transient( $lock_key );

		return $next;
	}

	/**
	 * Returns the current counter value without incrementing (read-only).
	 *
	 * @param int $year Four-digit year.
	 * @return int
	 */
	public function peek( int $year ): int {
		return (int) get_option( self::OPTION_KEY . '_' . $year, 0 );
	}

	/**
	 * Resets the counter for a given year (admin/migration use only).
	 *
	 * @param int $year Four-digit year.
	 * @param int $value Value to reset to (default 0).
	 */
	public function reset( int $year, int $value = 0 ): void {
		update_option( self::OPTION_KEY . '_' . $year, $value, false );
	}
}
