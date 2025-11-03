<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package    Product_Inquiry
 * @subpackage Product_Inquiry/public
 */

class Product_Inquiry_Public {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	public function enqueue_styles() {
		if ( ! is_product() ) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_name,
			PRODUCT_INQUIRY_PLUGIN_URL . 'public/css/pi-public.css',
			array(),
			$this->version,
			'all'
		);
	}

	public function enqueue_scripts() {
		if ( ! is_product() ) {
			return;
		}

		wp_enqueue_script(
			$this->plugin_name,
			PRODUCT_INQUIRY_PLUGIN_URL . 'public/js/pi-public.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		wp_localize_script(
			$this->plugin_name,
			'piData',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'pi_inquiry_nonce' ),
			)
		);
	}

	public function render_inquiry_button() {
		$button_text = Product_Inquiry_Settings::get_button_text();
		if ( ! is_product() ) {
			return;
		}

		?>
		<button type="button" class="pi-inquiry-button button alt" id="pi-open-modal">
			<?php esc_html_e( $button_text, 'product-inquiry' ); ?>
		</button>
		<?php
	}

	public function render_inquiry_modal() {
		if ( ! is_product() ) {
			return;
		}

		global $product;

		if ( ! $product ) {
			return;
		}

		$product_id    = $product->get_id();
		$product_title = $product->get_name();

		?>
		<div id="pi-modal-overlay" class="pi-modal-overlay" aria-hidden="true">
			<div id="pi-modal" class="pi-modal" role="dialog" aria-labelledby="pi-modal-title" aria-modal="true">
				<div class="pi-modal-content">
					<button type="button" class="pi-modal-close" aria-label="<?php esc_attr_e( 'Close inquiry form', 'product-inquiry' ); ?>">
						<span aria-hidden="true">&times;</span>
					</button>

					<h2 id="pi-modal-title" class="pi-modal-title">
						<?php
						/* translators: %s: Product name */
						echo esc_html( sprintf( __( 'Inquire About: %s', 'product-inquiry' ), $product_title ) );
						?>
					</h2>

					<form id="pi-inquiry-form" class="pi-inquiry-form">
						<?php wp_nonce_field( 'pi_submit_inquiry', 'pi_nonce' ); ?>
						
						<input type="hidden" name="product_id" value="<?php echo esc_attr( $product_id ); ?>">

						<div class="pi-form-row">
							<label for="pi-name">
								<?php esc_html_e( 'Your Name', 'product-inquiry' ); ?> 
								<span class="pi-required">*</span>
							</label>
							<input type="text" id="pi-name" name="name" required aria-required="true">
						</div>

						<div class="pi-form-row">
							<label for="pi-email">
								<?php esc_html_e( 'Your Email', 'product-inquiry' ); ?> 
								<span class="pi-required">*</span>
							</label>
							<input type="email" id="pi-email" name="email" required aria-required="true">
						</div>

						<div class="pi-form-row">
							<label for="pi-phone">
								<?php esc_html_e( 'Phone Number', 'product-inquiry' ); ?>
							</label>
							<input type="tel" id="pi-phone" name="phone">
						</div>

						<div class="pi-form-row">
							<label for="pi-message">
								<?php esc_html_e( 'Your Message', 'product-inquiry' ); ?> 
								<span class="pi-required">*</span>
							</label>
							<textarea id="pi-message" name="message" rows="5" required aria-required="true"></textarea>
						</div>

						<div class="pi-form-actions">
							<button type="submit" class="pi-submit-button button alt">
								<?php esc_html_e( 'Send Inquiry', 'product-inquiry' ); ?>
							</button>
							<button type="button" class="pi-cancel-button button">
								<?php esc_html_e( 'Cancel', 'product-inquiry' ); ?>
							</button>
						</div>

						<div class="pi-form-messages" role="alert" aria-live="polite"></div>
					</form>
				</div>
			</div>
		</div>
		<?php
	}
}