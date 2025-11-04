<?php

/**
 *
 * @link              https://sahariarkabir.com
 * @since             1.0.0
 * @package           Product_Inquiry
 *
 * @wordpress-plugin
 * Plugin Name:       Product Inquiry for WooCommerce
 * Plugin URI:        https://github.com/sahariar/product-inquiry-for-woocommerce
 * Description:       Allow customers to send product inquiries from WooCommerce product pages. Admins can manage inquiries in the dashboard.
 * Version:           1.0.0
 * Author:            Sahariar kabir
 * Author URI:        https://sahariarkabir.com/
 * Text Domain:       product-inquiry-for-woocommerce
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 9.0
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'PRODUCT_INQUIRY_VERSION', '1.0.0' );
define( 'PRODUCT_INQUIRY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PRODUCT_INQUIRY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PRODUCT_INQUIRY_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Declare HPOS compatibility.
 */
function product_inquiry_declare_hpos_compatibility() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables',
			__FILE__,
			true
		);
	}
}
add_action( 'before_woocommerce_init', 'product_inquiry_declare_hpos_compatibility' );

/**
 * The code that runs during plugin activation.
 */
function activate_product_inquiry() {
	require_once PRODUCT_INQUIRY_PLUGIN_DIR . 'includes/class-product-inquiry-for-woocommerce-activator.php';
	Product_Inquiry_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_product_inquiry() {
	require_once PRODUCT_INQUIRY_PLUGIN_DIR . 'includes/class-product-inquiry-for-woocommerce-deactivator.php';
	Product_Inquiry_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_product_inquiry' );
register_deactivation_hook( __FILE__, 'deactivate_product_inquiry' );

/**
 * The core plugin class.
 */
require PRODUCT_INQUIRY_PLUGIN_DIR . 'includes/class-product-inquiry-for-woocommerce.php';

/**
 * Begins execution of the plugin.
 */
function run_product_inquiry() {
	$plugin = new Product_Inquiry();
	$plugin->run();
}
run_product_inquiry();
