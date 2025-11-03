<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Product_Inquiry
 * @subpackage Product_Inquiry/admin
 */

class Product_Inquiry_Admin {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	public function enqueue_styles() {
		wp_enqueue_style(
			$this->plugin_name,
			PRODUCT_INQUIRY_PLUGIN_URL . 'admin/css/pi-admin.css',
			array(),
			$this->version,
			'all'
		);
	}

	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name,
			PRODUCT_INQUIRY_PLUGIN_URL . 'admin/js/pi-admin.js',
			array( 'jquery' ),
			$this->version,
			true
		);
	}
}