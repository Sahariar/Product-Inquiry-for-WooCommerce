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
		$cpt = new Product_Inquiry_CPT();
		$cpt->register_cpt();

		// Flush rewrite rules to register CPT permalinks
		flush_rewrite_rules();

		// Set default plugin options if not already set
		self::set_default_options();
		// Set default settings if they don't exist
		self::set_default_settings();

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
	private static function set_default_settings() {
		$defaults = array(
			'pi_admin_email'        => get_option( 'admin_email' ),
			'pi_success_message'    => __( 'Thank you for your inquiry! We will get back to you shortly.', 'product-inquiry' ),
			'pi_form_display_mode'  => 'popup',
			'pi_enable_auto_reply'  => 'yes',
			'pi_auto_reply_subject' => __( 'We received your inquiry', 'product-inquiry' ),
			'pi_auto_reply_message' => sprintf(
				__( "Hello {customer_name},\n\nThank you for your inquiry about {product_name}.\n\nWe have received your message and will respond as soon as possible. If you have any urgent questions, please feel free to contact us at {admin_email}.\n\nBest regards,\n%s", 'product-inquiry' ),
				get_bloginfo( 'name' )
			),
			'pi_button_text'        => __( 'Product Inquiry', 'product-inquiry' ),
			'pi_button_position'    => 'after_add_to_cart',
		);

		foreach ( $defaults as $key => $value ) {
			if ( false === get_option( $key ) ) {
				add_option( $key, $value );
			}
		}
	}
}
