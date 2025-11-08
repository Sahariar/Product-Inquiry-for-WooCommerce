<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @package    Product_Inquiry
 * @subpackage Product_Inquiry/public
 */

class Product_Inquiry_Public
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
		// Only enqueue on product pages or if shortcode is present
		if (! $this->should_enqueue_assets()) {
			return;
		}
		if (! is_product()) {
			return;
		}

		$dependencies = array();
		if ( wp_style_is( 'woocommerce-general', 'registered' ) ) {
			$dependencies[] = 'woocommerce-general';
		}

		wp_enqueue_style(
			$this->plugin_name,
			PRODUCT_INQUIRY_PLUGIN_URL . 'public/css/product-inquiry-for-woocommerce-public.css',
			$dependencies,
			$this->version,
			'all'
		);
	}

	public function enqueue_scripts()
	{
		// Only enqueue on product pages or if shortcode is present
		if (! $this->should_enqueue_assets()) {
			return;
		}
		if (! is_product()) {
			return;
		}

		wp_enqueue_script(
			$this->plugin_name,
			PRODUCT_INQUIRY_PLUGIN_URL . 'public/js/product-inquiry-for-woocommerce-public.js',
			array('jquery'),
			$this->version,
			true
		);

		wp_localize_script(
			$this->plugin_name,
			'productInquiryForWooCommerceData',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
			)
		);
	}

	public function render_inquiry_button()
	{
		$button_text = Product_Inquiry_Settings::get_button_text();
		if (! is_product()) {
			return;
		}

?>
		<button type="button" class="product-inquiry-for-woocommerce-inquiry-button button alt" id="product-inquiry-for-woocommerce-open-modal">
			<?php echo esc_html($button_text); ?>
		</button>
	<?php
	}

	public function render_inquiry_modal()
	{
		if (! is_product()) {
			return;
		}

		global $product;

		if (! $product) {
			return;
		}

		$product_id    = $product->get_id();
		$product_title = $product->get_name();

	?>
		<div id="product-inquiry-for-woocommerce-modal-overlay" class="product-inquiry-for-woocommerce-modal-overlay" aria-hidden="true">
			<div id="product-inquiry-for-woocommerce-modal" class="product-inquiry-for-woocommerce-modal" role="dialog" aria-labelledby="product-inquiry-for-woocommerce-modal-title" aria-modal="true">
				<div class="product-inquiry-for-woocommerce-modal-content">
					<button type="button" class="product-inquiry-for-woocommerce-modal-close" aria-label="<?php esc_attr_e('Close inquiry form', 'product-inquiry-for-woocommerce'); ?>">
						<span aria-hidden="true">&times;</span>
					</button>

					<h2 id="product-inquiry-for-woocommerce-modal-title" class="product-inquiry-for-woocommerce-modal-title">
						<?php
						/* translators: %s: Product name */
						echo esc_html(sprintf(__('Inquire About: %s', 'product-inquiry-for-woocommerce'), $product_title));
						?>
					</h2>

					<form id="product-inquiry-for-woocommerce-inquiry-form" class="product-inquiry-for-woocommerce-inquiry-form">
						<?php wp_nonce_field('product_inquiry_for_woocommerce_submit_inquiry', 'product_inquiry_for_woocommerce_nonce'); ?>

						<input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>">

						<div class="product-inquiry-for-woocommerce-form-row">
							<label for="product-inquiry-for-woocommerce-name">
								<?php esc_html_e('Your Name', 'product-inquiry-for-woocommerce'); ?>
								<span class="product-inquiry-for-woocommerce-required">*</span>
							</label>
							<input type="text" id="product-inquiry-for-woocommerce-name" name="name" required aria-required="true">
						</div>

						<div class="product-inquiry-for-woocommerce-form-row">
							<label for="product-inquiry-for-woocommerce-email">
								<?php esc_html_e('Your Email', 'product-inquiry-for-woocommerce'); ?>
								<span class="product-inquiry-for-woocommerce-required">*</span>
							</label>
							<input type="email" id="product-inquiry-for-woocommerce-email" name="email" required aria-required="true">
						</div>

						<div class="product-inquiry-for-woocommerce-form-row">
							<label for="product-inquiry-for-woocommerce-phone">
								<?php esc_html_e('Phone Number', 'product-inquiry-for-woocommerce'); ?>
							</label>
							<input type="tel" id="product-inquiry-for-woocommerce-phone" name="phone">
						</div>

						<div class="product-inquiry-for-woocommerce-form-row">
							<label for="product-inquiry-for-woocommerce-message">
								<?php esc_html_e('Your Message', 'product-inquiry-for-woocommerce'); ?>
								<span class="product-inquiry-for-woocommerce-required">*</span>
							</label>
							<textarea id="product-inquiry-for-woocommerce-message" name="message" rows="5" required aria-required="true"></textarea>
						</div>

						<div class="product-inquiry-for-woocommerce-form-actions">
							<button type="submit" class="product-inquiry-for-woocommerce-submit-button button alt">
								<?php esc_html_e('Send Inquiry', 'product-inquiry-for-woocommerce'); ?>
							</button>
							<button type="button" class="product-inquiry-for-woocommerce-cancel-button button">
								<?php esc_html_e('Cancel', 'product-inquiry-for-woocommerce'); ?>
							</button>
						</div>

						<div class="product-inquiry-for-woocommerce-form-messages" role="alert" aria-live="polite"></div>
					</form>
				</div>
			</div>
		</div>
<?php
	}

	/**
	 * Check if assets should be enqueued.
	 *
	 * @since    1.0.0
	 * @return   bool Whether to enqueue assets.
	 */
	private function should_enqueue_assets()
	{
		global $post;

		// Check if on product page
		if (is_product()) {
			return true;
		}

		// Check if shortcode is present in post content
		if ($post && has_shortcode($post->post_content, 'product_inquiry_form')) {
			return true;
		}

		// Check if block is present
		if ($post && has_block('product-inquiry-for-woocommerce/inquiry-form', $post)) {
			return true;
		}

		return false;
	}
/**
 * Add custom class to product when inquiry is active
 */
public function add_product_inquiry_class( $classes ) {
    // Only on single product pages
    if ( is_product() ) {
        $classes[] = 'pi-inquiry-active';
    }
    return $classes;
}
}
