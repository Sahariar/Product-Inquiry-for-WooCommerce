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
		$this->plugin_name = 'product-inquiry-for-woocommerce';

		$this->load_dependencies();
		$this->set_locale();

		// Check WooCommerce before defining hooks.
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Initialize plugin after all plugins are loaded.
	 */
	public function init() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
			return;
		}

		// WooCommerce is active, define hooks.
		$this->define_cpt_hooks();
		$this->define_ajax_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		// NOW run the loader to register all hooks.
		$this->loader->run();
	}

	private function load_dependencies() {
		require_once PRODUCT_INQUIRY_PLUGIN_DIR . 'includes/class-product-inquiry-for-woocommerce-loader.php';
		require_once PRODUCT_INQUIRY_PLUGIN_DIR . 'includes/class-product-inquiry-for-woocommerce-i18n.php';
		require_once PRODUCT_INQUIRY_PLUGIN_DIR . 'includes/class-product-inquiry-for-woocommerce-cpt.php';
		require_once PRODUCT_INQUIRY_PLUGIN_DIR . 'includes/class-product-inquiry-for-woocommerce-ajax.php';
		require_once PRODUCT_INQUIRY_PLUGIN_DIR . 'includes/class-product-inquiry-for-woocommerce-shortcode.php';
		require_once PRODUCT_INQUIRY_PLUGIN_DIR . 'admin/class-product-inquiry-for-woocommerce-admin.php';
		require_once PRODUCT_INQUIRY_PLUGIN_DIR . 'admin/class-product-inquiry-for-woocommerce-settings.php';
		require_once PRODUCT_INQUIRY_PLUGIN_DIR . 'admin/class-product-inquiry-for-woocommerce-reply.php';
		require_once PRODUCT_INQUIRY_PLUGIN_DIR . 'admin/class-product-inquiry-for-woocommerce-export.php';
		require_once PRODUCT_INQUIRY_PLUGIN_DIR . 'public/class-product-inquiry-for-woocommerce-public.php';

		$this->loader = new Product_Inquiry_Loader();
	}

	private function set_locale() {
		$plugin_i18n = new Product_Inquiry_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register CPT hooks.
	 */
	private function define_cpt_hooks() {
		$plugin_cpt = new Product_Inquiry_CPT();
		$this->loader->add_action( 'init', $plugin_cpt, 'register_cpt' );
	}

	/**
	 * Register AJAX hooks.
	 */
	private function define_ajax_hooks() {
		$plugin_ajax = new Product_Inquiry_Ajax( $this->get_plugin_name(), $this->get_version() );

		// Logged-in users
		$this->loader->add_action( 'wp_ajax_product_inquiry_for_woocommerce_submit_inquiry', $plugin_ajax, 'submit_inquiry' );

		// Non-logged-in users
		$this->loader->add_action( 'wp_ajax_nopriv_product_inquiry_for_woocommerce_submit_inquiry', $plugin_ajax, 'submit_inquiry' );
	}

	private function define_admin_hooks() {
		$plugin_admin = new Product_Inquiry_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'show_welcome_notice' );
		// Admin menu bubble
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_menu_bubble' );

		// Custom columns
		$this->loader->add_filter( 'manage_product_inquiry_posts_columns', $plugin_admin, 'set_custom_columns' );
		$this->loader->add_action( 'manage_product_inquiry_posts_custom_column', $plugin_admin, 'custom_column_content', 10, 2 );
		$this->loader->add_filter( 'manage_edit-product_inquiry_sortable_columns', $plugin_admin, 'sortable_columns' );

		// Row actions
		$this->loader->add_filter( 'post_row_actions', $plugin_admin, 'modify_row_actions', 10, 2 );

		// Status change handlers
		$this->loader->add_action( 'admin_post_product_inquiry_for_woocommerce_mark_processed', $plugin_admin, 'handle_mark_processed' );
		$this->loader->add_action( 'admin_post_product_inquiry_for_woocommerce_mark_unprocessed', $plugin_admin, 'handle_mark_unprocessed' );

		// Bulk actions
		$this->loader->add_filter( 'bulk_actions-edit-product_inquiry', $plugin_admin, 'register_bulk_actions' );
		$this->loader->add_filter( 'handle_bulk_actions-edit-product_inquiry', $plugin_admin, 'handle_bulk_actions', 10, 3 );

		// Admin notices
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'display_admin_notices' );

		// Metaboxes
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_meta_boxes' );
		// Settings
		$plugin_settings = new Product_Inquiry_Settings( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_filter( 'woocommerce_settings_tabs_array', $plugin_settings, 'add_settings_tab', 50 );
		$this->loader->add_action( 'woocommerce_settings_tabs_product_inquiry', $plugin_settings, 'output_settings' );
		$this->loader->add_action( 'woocommerce_update_options_product_inquiry', $plugin_settings, 'save_settings' );
		$this->loader->add_filter( 'plugin_action_links_' . PRODUCT_INQUIRY_BASENAME, $plugin_settings, 'add_plugin_action_links' );

		// Export
		$plugin_export = new Product_Inquiry_Export( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_filter( 'bulk_actions-edit-product_inquiry', $plugin_export, 'add_bulk_export_action' );
		$this->loader->add_filter( 'handle_bulk_actions-edit-product_inquiry', $plugin_export, 'handle_bulk_export', 10, 3 );
		$this->loader->add_filter( 'post_row_actions', $plugin_export, 'add_row_export_action', 10, 2 );
		$this->loader->add_action( 'admin_action_product_inquiry_for_woocommerce_export_single', $plugin_export, 'handle_single_export' );
		$this->loader->add_action( 'admin_action_product_inquiry_for_woocommerce_export_all', $plugin_export, 'handle_export_all' );
		$this->loader->add_action( 'manage_posts_extra_tablenav', $plugin_export, 'add_export_all_button' );
		$this->loader->add_action( 'admin_notices', $plugin_export, 'display_export_notices' );

		// Reply functionality
		$plugin_reply = new Product_Inquiry_Reply( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'add_meta_boxes', $plugin_reply, 'add_reply_metaboxes' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_reply, 'enqueue_reply_scripts' );
		$this->loader->add_action( 'wp_ajax_product_inquiry_for_woocommerce_send_reply', $plugin_reply, 'handle_ajax_reply' );
	}

	private function define_public_hooks() {
		$button_position = Product_Inquiry_Settings::get_button_position();
		$plugin_public   = new Product_Inquiry_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		// Map position to WooCommerce hook
		$hook = 'woocommerce_after_add_to_cart_button'; // default
		if ( 'before_add_to_cart' === $button_position ) {
			$hook = 'woocommerce_before_add_to_cart_button';
		} elseif ( 'after_summary' === $button_position ) {
			$hook = 'woocommerce_after_single_product_summary';
		}
		$this->loader->add_action( $hook, $plugin_public, 'render_inquiry_button' );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'render_inquiry_modal' );

		// Shortcode
		$plugin_shortcode = new Product_Inquiry_Shortcode( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'init', $plugin_shortcode, 'register_shortcode' );

		// Gutenberg block
		$this->loader->add_action( 'init', $this, 'register_blocks' );
	}

	public function woocommerce_missing_notice() {
		?>
		<div class="notice notice-error">
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						/* translators: %s: WooCommerce plugin link */
						__( '<strong>Product Inquiry</strong> requires WooCommerce to be installed and active. Please install %s first.', 'product-inquiry-for-woocommerce' ),
						'<a href="' . esc_url( admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) ) . '">WooCommerce</a>'
					)
				);
				?>
			</p>
		</div>
		<?php
	}

/**
 * Register Gutenberg blocks.
 *
 * @since    1.0.0
 */
/**
 * Register Gutenberg blocks.
 *
 * @since    1.0.0
 */
public function register_blocks() {
	// Only register if block editor is available
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}
	$plugin_file = PRODUCT_INQUIRY_PLUGIN_DIR . 'product-inquiry-for-woocommerce.php';
	// Register block editor script
	$script_registered = wp_register_script(
		'pi-inquiry-form-block-editor',
		plugins_url( 'blocks/product-inquiry-for-woocommerce-form/index.js', $plugin_file ),
		array(
			'wp-blocks',
			'wp-element',
			'wp-i18n',
			'wp-block-editor',
			'wp-components',
			'wp-api-fetch',
			'wp-data',
		),
		$this->version,
		true
	);
	// Register block editor style
	$style_registered = wp_register_style(
		'pi-inquiry-form-block-editor',
		plugins_url( 'blocks/product-inquiry-for-woocommerce-form/editor.css', $plugin_file ),
		array( 'wp-edit-blocks' ),
		$this->version
	);
	

	// Register the block
	$block_path = plugin_dir_path( __DIR__ ) . 'blocks/product-inquiry-for-woocommerce-form';
	
	$block_registered = register_block_type(
		$block_path,
		array(
			'editor_script' => 'pi-inquiry-form-block-editor',
			'editor_style'  => 'pi-inquiry-form-block-editor',
		)
	);
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
