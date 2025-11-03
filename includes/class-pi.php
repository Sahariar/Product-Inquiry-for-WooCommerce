<?php
/**
 * The core plugin class.
 *
 * @package    Product_Inquiry
 * @subpackage Product_Inquiry/includes
 */

class Product_Inquiry {

	protected $loader;
	protected $plugin_name;
	protected $version;

	public function __construct() {
		$this->version     = PRODUCT_INQUIRY_VERSION;
		$this->plugin_name = 'product-inquiry';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->check_woocommerce();
	}

	private function load_dependencies() {
		require_once PRODUCT_INQUIRY_PLUGIN_DIR . 'includes/class-pi-loader.php';
		require_once PRODUCT_INQUIRY_PLUGIN_DIR . 'includes/class-pi-i18n.php';
		require_once PRODUCT_INQUIRY_PLUGIN_DIR . 'admin/class-pi-admin.php';
		require_once PRODUCT_INQUIRY_PLUGIN_DIR . 'public/class-pi-public.php';

		$this->loader = new Product_Inquiry_Loader();
	}

	private function set_locale() {
		$plugin_i18n = new Product_Inquiry_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	private function define_admin_hooks() {
		$plugin_admin = new Product_Inquiry_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
	}

	private function define_public_hooks() {
		$plugin_public = new Product_Inquiry_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'woocommerce_after_add_to_cart_button', $plugin_public, 'render_inquiry_button' );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'render_inquiry_modal' );
	}

	private function check_woocommerce() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			$this->loader->add_action( 'admin_notices', $this, 'woocommerce_missing_notice' );
		}
	}

	public function woocommerce_missing_notice() {
		?>
		<div class="notice notice-error">
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						/* translators: %s: WooCommerce plugin link */
						__( '<strong>Product Inquiry</strong> requires WooCommerce to be installed and active. Please install %s first.', 'product-inquiry' ),
						'<a href="' . esc_url( admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) ) . '">WooCommerce</a>'
					)
				);
				?>
			</p>
		</div>
		<?php
	}

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_loader() {
		return $this->loader;
	}

	public function get_version() {
		return $this->version;
	}
}