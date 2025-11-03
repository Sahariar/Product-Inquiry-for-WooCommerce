<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Product_Inquiry
 * @subpackage Product_Inquiry/admin
 */

class Product_Inquiry_Admin
{

	private $plugin_name;
	private $version;

	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	public function enqueue_styles()
	{
		wp_enqueue_style(
			$this->plugin_name,
			PRODUCT_INQUIRY_PLUGIN_URL . 'admin/css/pi-admin.css',
			array(),
			$this->version,
			'all'
		);
	}

	public function enqueue_scripts()
	{
		wp_enqueue_script(
			$this->plugin_name,
			PRODUCT_INQUIRY_PLUGIN_URL . 'admin/js/pi-admin.js',
			array('jquery'),
			$this->version,
			true
		);
	}
	/**
	 * Show welcome notice after activation.
	 */
	public function show_welcome_notice()
	{
		// Only show to users who can manage options
		if (! current_user_can('manage_options')) {
			return;
		}

		// Check if notice was dismissed
		if (get_option('pi_welcome_dismissed')) {
			return;
		}

		// Check if activated recently (within 7 days)
		$activated_time = get_option('pi_activated_time');
		if (! $activated_time || (current_time('timestamp') - $activated_time) > WEEK_IN_SECONDS) {
			return;
		}

?>
		<div class="notice notice-success is-dismissible" data-notice="pi-welcome">
			<p>
				<strong><?php esc_html_e('Product Inquiry for WooCommerce', 'product-inquiry'); ?></strong>
				<?php esc_html_e('is now active! Start receiving customer inquiries on your product pages.', 'product-inquiry'); ?>
			</p>
			<p>
				<a href="<?php echo esc_url(admin_url('edit.php?post_type=product_inquiry')); ?>" class="button button-primary">
					<?php esc_html_e('View Inquiries', 'product-inquiry'); ?>
				</a>
				<a href="<?php echo esc_url(admin_url('admin.php?page=product-inquiry-settings')); ?>" class="button">
					<?php esc_html_e('Settings', 'product-inquiry'); ?>
				</a>
			</p>
		</div>
<?php
	}
}
