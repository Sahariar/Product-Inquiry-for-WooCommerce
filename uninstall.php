<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * This file is called when the user uninstalls the plugin via WordPress admin.
 * It removes all plugin data from the database.
 *
 * @link       https://sahariarkabir.com
 * @since      1.0.0
 * @package    Product_Inquiry
 */

// If uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete all product inquiry posts and metadata.
 */
function pi_delete_all_inquiries() {
	global $wpdb;

	// Get all inquiry post IDs
	$inquiry_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s",
			'product_inquiry'
		)
	);

	// Delete each inquiry and its metadata
	foreach ( $inquiry_ids as $inquiry_id ) {
		// Delete all post meta
		$wpdb->delete(
			$wpdb->postmeta,
			array( 'post_id' => $inquiry_id ),
			array( '%d' )
		);

		// Delete the post
		$wpdb->delete(
			$wpdb->posts,
			array( 'ID' => $inquiry_id ),
			array( '%d' )
		);
	}
}

/**
 * Delete all plugin options.
 */
function pi_delete_plugin_options() {
	// Delete plugin settings
	delete_option( 'pi_admin_email' );
	delete_option( 'pi_success_message' );
	delete_option( 'pi_button_text' );
	delete_option( 'pi_display_mode' );
	delete_option( 'pi_enable_admin_email' );
	delete_option( 'pi_enable_auto_reply' );
	delete_option( 'pi_activated_time' );
	delete_option( 'pi_version' );

	// Delete any transients
	delete_transient( 'pi_inquiry_count' );

	// For multisite, delete site options
	if ( is_multisite() ) {
		delete_site_option( 'pi_admin_email' );
		delete_site_option( 'pi_version' );
	}
}

/**
 * Clean up custom database tables (if any added in future).
 */
function pi_drop_custom_tables() {
	global $wpdb;

	// Example: If you create custom tables in the future
	// $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}product_inquiry_log" );
}

// Only delete data if user explicitly chose to delete data on uninstall
// This check allows users to keep data even after uninstalling
if ( get_option( 'pi_delete_data_on_uninstall', false ) ) {
	pi_delete_all_inquiries();
	pi_delete_plugin_options();
	pi_drop_custom_tables();
}

// Always flush rewrite rules on uninstall
flush_rewrite_rules();