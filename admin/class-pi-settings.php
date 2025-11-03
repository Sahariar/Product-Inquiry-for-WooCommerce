<?php
/**
 * Settings functionality for Product Inquiry
 *
 *
 * @package    Product_Inquiry
 * @subpackage Product_Inquiry/admin
 */

/**
 * Settings page for Product Inquiry plugin.
 *
 * Adds a settings tab under WooCommerce → Settings → Inquiries.
 * Uses WooCommerce Settings API for consistency.
 *
 * @package    Product_Inquiry
 * @subpackage Product_Inquiry/admin
 * @author     Your Name <email@example.com>
 */
class Product_Inquiry_Settings {

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
	 * Settings option group name.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $option_group    Settings option group.
	 */
	private $option_group = 'pi_inquiry_settings';

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
	 * Add settings tab to WooCommerce settings.
	 *
	 * @since    1.0.0
	 * @param    array $settings_tabs Existing settings tabs.
	 * @return   array Modified settings tabs.
	 */
	public function add_settings_tab( $settings_tabs ) {
		$settings_tabs['product_inquiry'] = __( 'Inquiries', 'product-inquiry' );
		return $settings_tabs;
	}

	/**
	 * Output settings fields for our tab.
	 *
	 * @since    1.0.0
	 */
	public function output_settings() {
		woocommerce_admin_fields( $this->get_settings() );
	}

	/**
	 * Save settings fields.
	 *
	 * @since    1.0.0
	 */
	public function save_settings() {
		woocommerce_update_options( $this->get_settings() );
	}

	/**
	 * Get all settings fields.
	 *
	 * @since    1.0.0
	 * @return   array Settings fields.
	 */
	public function get_settings() {
		$settings = array(
			array(
				'name' => __( 'Product Inquiry Settings', 'product-inquiry' ),
				'type' => 'title',
				'desc' => __( 'Configure how product inquiries work on your store.', 'product-inquiry' ),
				'id'   => 'pi_inquiry_section_title',
			),

			array(
				'name'     => __( 'Admin Email', 'product-inquiry' ),
				'type'     => 'email',
				'desc'     => __( 'Email address where inquiry notifications will be sent.', 'product-inquiry' ),
				'id'       => 'pi_admin_email',
				'default'  => get_option( 'admin_email' ),
				'css'      => 'min-width:300px;',
				'desc_tip' => true,
			),

			array(
				'name'     => __( 'Success Message', 'product-inquiry' ),
				'type'     => 'textarea',
				'desc'     => __( 'Message shown to customers after successfully submitting an inquiry.', 'product-inquiry' ),
				'id'       => 'pi_success_message',
				'default'  => __( 'Thank you for your inquiry! We will get back to you shortly.', 'product-inquiry' ),
				'css'      => 'min-width:500px; min-height:75px;',
				'desc_tip' => true,
			),

			array(
				'name'    => __( 'Form Display Mode', 'product-inquiry' ),
				'type'    => 'radio',
				'desc'    => __( 'Choose how the inquiry form should be displayed.', 'product-inquiry' ),
				'id'      => 'pi_form_display_mode',
				'default' => 'popup',
				'options' => array(
					'popup' => __( 'Popup Modal (recommended)', 'product-inquiry' ),
					'inline' => __( 'Inline Below Product', 'product-inquiry' ),
				),
				'desc_tip' => true,
			),

			array(
				'name'    => __( 'Enable Auto-Reply', 'product-inquiry' ),
				'type'    => 'checkbox',
				'desc'    => __( 'Automatically send a confirmation email to customers when they submit an inquiry.', 'product-inquiry' ),
				'id'      => 'pi_enable_auto_reply',
				'default' => 'yes',
			),

			array(
				'name'     => __( 'Auto-Reply Subject', 'product-inquiry' ),
				'type'     => 'text',
				'desc'     => __( 'Subject line for the auto-reply email.', 'product-inquiry' ),
				'id'       => 'pi_auto_reply_subject',
				'default'  => __( 'We received your inquiry', 'product-inquiry' ),
				'css'      => 'min-width:400px;',
				'desc_tip' => true,
			),

			array(
				'name'     => __( 'Auto-Reply Message', 'product-inquiry' ),
				'type'     => 'textarea',
				'desc'     => __( 'Email message sent to customers. Use {customer_name}, {product_name}, {admin_email} as placeholders.', 'product-inquiry' ),
				'id'       => 'pi_auto_reply_message',
				'default'  => $this->get_default_auto_reply_message(),
				'css'      => 'min-width:500px; min-height:150px;',
				'desc_tip' => true,
			),

			array(
				'name'    => __( 'Button Text', 'product-inquiry' ),
				'type'    => 'text',
				'desc'    => __( 'Text displayed on the inquiry button.', 'product-inquiry' ),
				'id'      => 'pi_button_text',
				'default' => __( 'Product Inquiry', 'product-inquiry' ),
				'css'     => 'min-width:300px;',
				'desc_tip' => true,
			),

			array(
				'name'    => __( 'Button Position', 'product-inquiry' ),
				'type'    => 'select',
				'desc'    => __( 'Where to display the inquiry button on product pages.', 'product-inquiry' ),
				'id'      => 'pi_button_position',
				'default' => 'after_add_to_cart',
				'options' => array(
					'before_add_to_cart' => __( 'Before Add to Cart Button', 'product-inquiry' ),
					'after_add_to_cart'  => __( 'After Add to Cart Button', 'product-inquiry' ),
					'after_summary'      => __( 'After Product Summary', 'product-inquiry' ),
				),
				'desc_tip' => true,
			),

			array(
				'type' => 'sectionend',
				'id'   => 'pi_inquiry_section_end',
			),

			// Quick Links Section
			array(
				'name' => __( 'Quick Links', 'product-inquiry' ),
				'type' => 'title',
				'desc' => sprintf(
					'<a href="%s" class="button button-secondary">%s</a>',
					esc_url( admin_url( 'edit.php?post_type=product_inquiry' ) ),
					__( 'View All Inquiries', 'product-inquiry' )
				),
				'id'   => 'pi_quick_links_section',
			),

			array(
				'type' => 'sectionend',
				'id'   => 'pi_quick_links_section_end',
			),
		);

		return apply_filters( 'pi_inquiry_settings', $settings );
	}

	/**
	 * Get default auto-reply message template.
	 *
	 * @since    1.0.0
	 * @return   string Default auto-reply message.
	 */
	private function get_default_auto_reply_message() {
		return sprintf(
			/* translators: Auto-reply email template */
			__( "Hello {customer_name},\n\nThank you for your inquiry about {product_name}.\n\nWe have received your message and will respond as soon as possible. If you have any urgent questions, please feel free to contact us at {admin_email}.\n\nBest regards,\n%s", 'product-inquiry' ),
			get_bloginfo( 'name' )
		);
	}

	/**
	 * Get a setting value with default fallback.
	 *
	 * @since    1.0.0
	 * @param    string $key     Setting key.
	 * @param    mixed  $default Default value.
	 * @return   mixed Setting value.
	 */
	public static function get_option( $key, $default = false ) {
		return get_option( $key, $default );
	}

	/**
	 * Get admin email from settings.
	 *
	 * @since    1.0.0
	 * @return   string Admin email address.
	 */
	public static function get_admin_email() {
		return self::get_option( 'pi_admin_email', get_option( 'admin_email' ) );
	}

	/**
	 * Get success message from settings.
	 *
	 * @since    1.0.0
	 * @return   string Success message.
	 */
	public static function get_success_message() {
		return self::get_option(
			'pi_success_message',
			__( 'Thank you for your inquiry! We will get back to you shortly.', 'product-inquiry' )
		);
	}

	/**
	 * Get form display mode.
	 *
	 * @since    1.0.0
	 * @return   string Form display mode (popup or inline).
	 */
	public static function get_form_display_mode() {
		return self::get_option( 'pi_form_display_mode', 'popup' );
	}

	/**
	 * Check if auto-reply is enabled.
	 *
	 * @since    1.0.0
	 * @return   bool True if auto-reply is enabled.
	 */
	public static function is_auto_reply_enabled() {
		return 'yes' === self::get_option( 'pi_enable_auto_reply', 'yes' );
	}

	/**
	 * Get auto-reply subject.
	 *
	 * @since    1.0.0
	 * @return   string Auto-reply subject.
	 */
	public static function get_auto_reply_subject() {
		return self::get_option(
			'pi_auto_reply_subject',
			__( 'We received your inquiry', 'product-inquiry' )
		);
	}

	/**
	 * Get auto-reply message.
	 *
	 * @since    1.0.0
	 * @return   string Auto-reply message.
	 */
	public static function get_auto_reply_message() {
		$default = sprintf(
			__( "Hello {customer_name},\n\nThank you for your inquiry about {product_name}.\n\nWe have received your message and will respond as soon as possible. If you have any urgent questions, please feel free to contact us at {admin_email}.\n\nBest regards,\n%s", 'product-inquiry' ),
			get_bloginfo( 'name' )
		);

		return self::get_option( 'pi_auto_reply_message', $default );
	}

	/**
	 * Get button text.
	 *
	 * @since    1.0.0
	 * @return   string Button text.
	 */
	public static function get_button_text() {
		return self::get_option( 'pi_button_text', __( 'Product Inquiry', 'product-inquiry' ) );
	}

	/**
	 * Get button position.
	 *
	 * @since    1.0.0
	 * @return   string Button position hook name.
	 */
	public static function get_button_position() {
		return self::get_option( 'pi_button_position', 'after_add_to_cart' );
	}

	/**
	 * Parse auto-reply message with placeholders.
	 *
	 * @since    1.0.0
	 * @param    string $message      Message template.
	 * @param    string $customer_name Customer name.
	 * @param    string $product_name  Product name.
	 * @return   string Parsed message.
	 */
	public static function parse_auto_reply_message( $message, $customer_name, $product_name ) {
		$replacements = array(
			'{customer_name}' => $customer_name,
			'{product_name}'  => $product_name,
			'{admin_email}'   => self::get_admin_email(),
			'{site_name}'     => get_bloginfo( 'name' ),
			'{site_url}'      => home_url(),
		);

		return str_replace(
			array_keys( $replacements ),
			array_values( $replacements ),
			$message
		);
	}

	/**
	 * Add settings link to plugins page.
	 *
	 * @since    1.0.0
	 * @param    array $links Existing plugin action links.
	 * @return   array Modified plugin action links.
	 */
	public function add_plugin_action_links( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=wc-settings&tab=product_inquiry' ) ),
			__( 'Settings', 'product-inquiry' )
		);

		array_unshift( $links, $settings_link );

		return $links;
	}
}