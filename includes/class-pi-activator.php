<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @link       https://sahariarkabir.com
 * @since      1.0.0
 * @package    Product_Inquiry
 * @subpackage Product_Inquiry/includes
 */

class Product_Inquiry_Activator {

	/**
	 * Activation tasks.
	 *
	 * Registers CPT, flushes rewrite rules, and sets default options.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		// Check if WooCommerce is active
		if ( ! class_exists( 'WooCommerce' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die(
				esc_html__(
					'Product Inquiry for WooCommerce requires WooCommerce to be installed and active. Please install WooCommerce first.',
					'product-inquiry'
				),
				esc_html__( 'Plugin Activation Error', 'product-inquiry' ),
				array( 'back_link' => true )
			);
		}

		// Register CPT so rewrite rules are aware of it
		require_once PRODUCT_INQUIRY_PLUGIN_DIR . 'includes/class-pi-cpt.php';
		$cpt = new PI_CPT();
		$cpt->register_cpt();

		// Flush rewrite rules to register CPT permalinks
		flush_rewrite_rules();

		// Set default plugin options if not already set
		self::set_default_options();

		// Set activation timestamp
		if ( ! get_option( 'pi_activated_time' ) ) {
			update_option( 'pi_activated_time', current_time( 'timestamp' ) );
		}

		// Set plugin version
		update_option( 'pi_version', PRODUCT_INQUIRY_VERSION );
	}

	/**
	 * Set default plugin options.
	 *
	 * @since 1.0.0
	 */
	private static function set_default_options() {
		// Default admin notification email
		if ( ! get_option( 'pi_admin_email' ) ) {
			add_option( 'pi_admin_email', get_option( 'admin_email' ) );
		}

		// Default success message
		if ( ! get_option( 'pi_success_message' ) ) {
			add_option(
				'pi_success_message',
				__( 'Thank you! Your inquiry has been submitted successfully. We will get back to you soon.', 'product-inquiry' )
			);
		}

		// Default button text
		if ( ! get_option( 'pi_button_text' ) ) {
			add_option( 'pi_button_text', __( 'Product Inquiry', 'product-inquiry' ) );
		}

		// Default display mode (modal or inline)
		if ( ! get_option( 'pi_display_mode' ) ) {
			add_option( 'pi_display_mode', 'modal' );
		}

		// Enable admin notifications by default
		if ( ! get_option( 'pi_enable_admin_email' ) ) {
			add_option( 'pi_enable_admin_email', '1' );
		}

		// Disable auto-reply by default (for future feature)
		if ( ! get_option( 'pi_enable_auto_reply' ) ) {
			add_option( 'pi_enable_auto_reply', '0' );
		}
	}
}