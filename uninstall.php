<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * This file is called when the user uninstalls the plugin via WordPress admin.
 * It removes all plugin data from the database if configured to do so.
 *
 * @link       https://sahariarkabir.com
 * @since      1.0.0
 * @package    Product_Inquiry
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete all product inquiry posts and metadata.
 *
 * Uses WP_Query instead of direct DB calls for better compatibility.
 * Falls back to direct query only for bulk deletion performance.
 */
function pi_delete_all_inquiries() {
	// Query all inquiry posts.
	$args = array(
		'post_type'      => 'product_inquiry',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'fields'         => 'ids', // Only get IDs for performance.
	);

	$inquiry_ids = get_posts( $args );

	if ( empty( $inquiry_ids ) ) {
		return;
	}

	// Delete each inquiry post.
	// Using wp_delete_post() ensures proper cleanup of metadata and relationships.
	foreach ( $inquiry_ids as $inquiry_id ) {
		wp_delete_post( $inquiry_id, true ); // Force delete, bypass trash.
	}

	// Clean up any orphaned postmeta (edge case protection).
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	// Reason: Cleanup operation during uninstall - no caching needed, direct query necessary for orphaned meta.
	global $wpdb;
	$wpdb->query(
		$wpdb->prepare(
			"DELETE pm FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE p.ID IS NULL AND pm.meta_key LIKE %s",
			$wpdb->esc_like( '_pi_' ) . '%'
		)
	);
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
}

/**
 * Delete all plugin options.
 *
 * Uses native WordPress functions for option cleanup.
 */
function pi_delete_plugin_options() {
	// Array of all plugin options.
	$options = array(
		'pi_admin_email',
		'pi_success_message',
		'pi_button_text',
		'pi_display_mode',
		'pi_enable_admin_email',
		'pi_enable_auto_reply',
		'pi_activated_time',
		'pi_version',
		'pi_delete_data_on_uninstall',
	);

	// Delete each option.
	foreach ( $options as $option ) {
		delete_option( $option );
	}

	// Delete any transients.
	delete_transient( 'pi_inquiry_count' );

	// For multisite, delete site options.
	if ( is_multisite() ) {
		foreach ( $options as $option ) {
			delete_site_option( $option );
		}
	}
}

/**
 * Clean up custom database tables (if any added in future).
 *
 * Placeholder for future custom table cleanup.
 */
function pi_drop_custom_tables() {
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	// Reason: Dropping tables during uninstall - direct query required, no caching applicable.
	global $wpdb;

	// Example: If you create custom tables in the future.
	// $table_name = $wpdb->prefix . 'product_inquiry_log';
	// $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
}

/**
 * Main uninstall routine.
 *
 * Only delete data if user explicitly chose to delete data on uninstall.
 * This allows users to keep inquiry data even after uninstalling the plugin.
 */
if ( get_option( 'pi_delete_data_on_uninstall', false ) ) {
	pi_delete_all_inquiries();
	pi_delete_plugin_options();
	pi_drop_custom_tables();
}

// Clean up rewrite rules.
flush_rewrite_rules();