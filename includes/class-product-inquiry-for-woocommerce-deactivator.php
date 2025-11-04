<?php
/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @link       https://sahariarkabir.com
 * @since      1.0.0
 * @package    Product_Inquiry
 * @subpackage Product_Inquiry/includes
 */

class Product_Inquiry_Deactivator {

	/**
	 * Deactivation tasks.
	 *
	 * Flushes rewrite rules to clean up CPT permalinks.
	 * Note: We don't delete data on deactivation - only on uninstall.
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {
		// Flush rewrite rules to remove CPT permalinks
		flush_rewrite_rules();

		// Clear any scheduled cron jobs (if implemented in future)
		$timestamp = wp_next_scheduled( 'product_inquiry_for_woocommerce_daily_cleanup' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'product_inquiry_for_woocommerce_daily_cleanup' );
		}

		// Log deactivation (only in debug mode)
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'Product Inquiry Plugin deactivated at ' . current_time( 'mysql' ) );
		}
	}
}
