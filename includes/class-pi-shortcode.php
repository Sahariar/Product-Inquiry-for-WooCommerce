<?php
/**
 * Shortcode functionality for Product Inquiry
 *
 * @link       https://sahariarkabir.com/
 * @since      1.0.0
 *
 * @package    Product_Inquiry
 * @subpackage Product_Inquiry/includes
 */

/**
 * Shortcode class.
 *
 * Registers and renders the [product_inquiry_form] shortcode.
 *
 * @package    Product_Inquiry
 * @subpackage Product_Inquiry/includes
 * @author     Sahariar Kabir<sahariark@gmail.com>
 */
class Product_Inquiry_Shortcode {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name The name of this plugin.
	 * @param    string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register shortcode.
	 *
	 * @since    1.0.0
	 */
	public function register_shortcode() {
		add_shortcode( 'product_inquiry_form', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Render shortcode output.
	 *
	 * @since    1.0.0
	 * @param    array $atts Shortcode attributes.
	 * @return   string Shortcode output.
	 */
	public function render_shortcode( $atts ) {
		// Parse attributes
		$atts = shortcode_atts(
			array(
				'product_id' => 0,
				'show_title' => 'true',
			),
			$atts,
			'product_inquiry_form'
		);

		$product_id = absint( $atts['product_id'] );
		$show_title = filter_var( $atts['show_title'], FILTER_VALIDATE_BOOLEAN );

		// Validate product ID
		if ( ! $product_id ) {
			return $this->render_error( __( 'Product ID is required.', 'product-inquiry' ) );
		}

		// Check if WooCommerce is active
		if ( ! function_exists( 'wc_get_product' ) ) {
			return $this->render_error( __( 'WooCommerce is not active.', 'product-inquiry' ) );
		}

		// Get product
		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			return $this->render_error( __( 'Product not found.', 'product-inquiry' ) );
		}

		// Enqueue required assets
		$this->enqueue_shortcode_assets();

		// Get form display mode from settings
		$display_mode = Product_Inquiry_Settings::get_form_display_mode();

		// For shortcode, always force inline mode
		$display_mode = 'inline';

		// Render form
		ob_start();

		if ( $show_title ) {
			$this->render_form_title( $product );
		}

		$this->render_inquiry_form( $product, $display_mode );

		return ob_get_clean();
	}

	/**
	 * Render error message.
	 *
	 * @since    1.0.0
	 * @param    string $message Error message.
	 * @return   string Error HTML.
	 */
	private function render_error( $message ) {
		return sprintf(
			'<div class="pi-shortcode-error" style="padding: 15px; background: #f9f9f9; border-left: 4px solid #d63638; color: #d63638;">%s</div>',
			esc_html( $message )
		);
	}

	/**
	 * Enqueue shortcode assets.
	 *
	 * @since    1.0.0
	 */
	private function enqueue_shortcode_assets() {
		// Enqueue CSS
		wp_enqueue_style(
			$this->plugin_name . '-public',
			plugin_dir_url( __DIR__ ) . 'public/css/product-inquiry-public.css',
			array(),
			$this->version,
			'all'
		);

		// Enqueue JS
		wp_enqueue_script(
			$this->plugin_name . '-public',
			plugin_dir_url( __DIR__ ) . 'public/js/product-inquiry-public.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		// Localize script
		wp_localize_script(
			$this->plugin_name . '-public',
			'productInquiry',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'pi_inquiry_nonce' ),
			)
		);
	}

	/**
	 * Render form title.
	 *
	 * @since    1.0.0
	 * @param    WC_Product $product Product object.
	 */
	private function render_form_title( $product ) {
		?>
		<div class="pi-form-title">
			<h3>
				<?php
				printf(
					/* translators: %s: Product name */
					esc_html__( 'Inquire About: %s', 'product-inquiry' ),
					esc_html( $product->get_name() )
				);
				?>
			</h3>
		</div>
		<?php
	}

	/**
	 * Render inquiry form.
	 *
	 * @since    1.0.0
	 * @param    WC_Product $product      Product object.
	 * @param    string     $display_mode Display mode (inline or popup).
	 */
	private function render_inquiry_form( $product, $display_mode ) {
		$product_id = $product->get_id();
		?>
		<div class="pi-form-wrapper pi-shortcode-form">
			<form class="pi-inquiry-form" data-product-id="<?php echo esc_attr( $product_id ); ?>">
				<?php wp_nonce_field( 'pi_inquiry_nonce', 'pi_nonce' ); ?>
				
				<input type="hidden" name="product_id" value="<?php echo esc_attr( $product_id ); ?>">

				<div class="pi-form-row">
					<label for="pi_name_<?php echo esc_attr( $product_id ); ?>">
						<?php esc_html_e( 'Your Name', 'product-inquiry' ); ?>
						<span class="required">*</span>
					</label>
					<input 
						type="text" 
						id="pi_name_<?php echo esc_attr( $product_id ); ?>" 
						name="name" 
						required
						placeholder="<?php esc_attr_e( 'Enter your name', 'product-inquiry' ); ?>"
					>
				</div>

				<div class="pi-form-row">
					<label for="pi_email_<?php echo esc_attr( $product_id ); ?>">
						<?php esc_html_e( 'Your Email', 'product-inquiry' ); ?>
						<span class="required">*</span>
					</label>
					<input 
						type="email" 
						id="pi_email_<?php echo esc_attr( $product_id ); ?>" 
						name="email" 
						required
						placeholder="<?php esc_attr_e( 'Enter your email', 'product-inquiry' ); ?>"
					>
				</div>

				<div class="pi-form-row">
					<label for="pi_phone_<?php echo esc_attr( $product_id ); ?>">
						<?php esc_html_e( 'Phone Number', 'product-inquiry' ); ?>
						<span class="optional"><?php esc_html_e( '(Optional)', 'product-inquiry' ); ?></span>
					</label>
					<input 
						type="tel" 
						id="pi_phone_<?php echo esc_attr( $product_id ); ?>" 
						name="phone"
						placeholder="<?php esc_attr_e( 'Enter your phone number', 'product-inquiry' ); ?>"
					>
				</div>

				<div class="pi-form-row">
					<label for="pi_message_<?php echo esc_attr( $product_id ); ?>">
						<?php esc_html_e( 'Your Message', 'product-inquiry' ); ?>
						<span class="required">*</span>
					</label>
					<textarea 
						id="pi_message_<?php echo esc_attr( $product_id ); ?>" 
						name="message" 
						rows="5" 
						required
						placeholder="<?php esc_attr_e( 'Enter your inquiry message', 'product-inquiry' ); ?>"
					></textarea>
				</div>

				<div class="pi-form-actions">
					<button type="submit" class="button pi-submit-btn">
						<?php esc_html_e( 'Send Inquiry', 'product-inquiry' ); ?>
					</button>
					<span class="pi-spinner"></span>
				</div>

				<div class="pi-form-response"></div>
			</form>
		</div>
		<?php
	}
}
