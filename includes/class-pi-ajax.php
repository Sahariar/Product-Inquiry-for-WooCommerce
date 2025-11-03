<?php
/**
 * AJAX Handler for Product Inquiry
 *
 * Processes inquiry form submissions via AJAX.
 *
 * @link       https://sahariarkabir.com/
 * @since      1.0.0
 *
 * @package    Product_Inquiry
 * @subpackage Product_Inquiry/includes
 */

/**
 * AJAX Handler class.
 *
 * Handles form submission, validation, storage, and email notifications.
 *
 * @package    Product_Inquiry
 * @subpackage Product_Inquiry/includes
 * @author     Sahariar Kabir<sahariark@gmail.com>
 */
class Product_Inquiry_Ajax {

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
	 * Handle inquiry submission via AJAX.
	 *
	 * Validates, sanitizes, stores inquiry, sends admin email,
	 * and optionally sends auto-reply to customer.
	 *
	 * @since    1.0.0
	 */
	public function submit_inquiry() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'pi_inquiry_nonce' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Security verification failed. Please refresh the page and try again.', 'product-inquiry' ),
				)
			);
		}

		// Validate and sanitize inputs
		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$name       = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$email      = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$phone      = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
		$message    = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

		// Validation errors array
		$errors = array();

		// Validate product ID
		if ( empty( $product_id ) || 'product' !== get_post_type( $product_id ) ) {
			$errors[] = __( 'Invalid product.', 'product-inquiry' );
		}

		// Validate name
		if ( empty( $name ) || strlen( $name ) < 2 ) {
			$errors[] = __( 'Please enter a valid name.', 'product-inquiry' );
		}

		// Validate email
		if ( empty( $email ) || ! is_email( $email ) ) {
			$errors[] = __( 'Please enter a valid email address.', 'product-inquiry' );
		}

		// Validate message
		if ( empty( $message ) || strlen( $message ) < 10 ) {
			$errors[] = __( 'Please enter a message with at least 10 characters.', 'product-inquiry' );
		}

		// Return errors if any
		if ( ! empty( $errors ) ) {
			wp_send_json_error(
				array(
					'message' => implode( '<br>', $errors ),
				)
			);
		}

		// Get product details
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			wp_send_json_error(
				array(
					'message' => __( 'Product not found.', 'product-inquiry' ),
				)
			);
		}

		$product_title = $product->get_name();

		// Create inquiry post title
		$post_title = sprintf(
			/* translators: 1: Product title, 2: Sender name */
			__( '%1$s — %2$s', 'product-inquiry' ),
			$product_title,
			$name
		);

		// Prepare inquiry data - KEEP ORIGINAL META KEYS
		$inquiry_data = array(
			'post_title'  => $post_title,
			'post_type'   => 'product_inquiry',
			'post_status' => 'publish',
			'meta_input'  => array(
				'_pi_email'      => $email,
				'_pi_message'    => $message,
				'_pi_product_id' => $product_id,
				'_pi_name'       => $name,
				'_pi_phone'      => $phone,
				'_pi_date'       => current_time( 'mysql' ),
				'_pi_status'     => 'new',
			),
		);

		// Allow filtering before creation
		$inquiry_data = apply_filters( 'pi_before_create_inquiry', $inquiry_data, $_POST );

		// Insert the inquiry
		$inquiry_id = wp_insert_post( $inquiry_data );

		if ( is_wp_error( $inquiry_id ) ) {
			// Log error for debugging
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Product Inquiry Error: ' . $inquiry_id->get_error_message() );
			}

			wp_send_json_error(
				array(
					'message' => __( 'Failed to save inquiry. Please try again later.', 'product-inquiry' ),
				)
			);
		}

		// Prepare notification data
		$notification_data = array(
			'inquiry_id'    => $inquiry_id,
			'product_id'    => $product_id,
			'product_title' => $product_title,
			'product_url'   => $product->get_permalink(),
			'name'          => $name,
			'email'         => $email,
			'phone'         => $phone,
			'message'       => $message,
		);

		// Send admin notification email
		$this->send_admin_notification( $notification_data );

		// Send auto-reply to customer if enabled
		if ( Product_Inquiry_Settings::is_auto_reply_enabled() ) {
			$this->send_customer_auto_reply( $notification_data );
		}

		// Fire action hook after inquiry submitted
		do_action( 'pi_inquiry_submitted', $inquiry_id, $inquiry_data );

		// Get success message from settings
		$success_message = Product_Inquiry_Settings::get_success_message();

		// Success response
		wp_send_json_success(
			array(
				'message'    => $success_message,
				'inquiry_id' => $inquiry_id,
			)
		);
	}

	/**
	 * Send admin notification email.
	 *
	 * @since    1.0.0
	 * @param    array $data Inquiry notification data.
	 * @return   bool Whether email was sent successfully.
	 */
	private function send_admin_notification( $data ) {
		// Get admin email from settings
		$admin_email = Product_Inquiry_Settings::get_admin_email();

		if ( empty( $admin_email ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Product Inquiry Error: Admin email not configured.' );
			}
			return false;
		}

		$inquiry_url = admin_url( 'post.php?post=' . $data['inquiry_id'] . '&action=edit' );

		// Email subject
		$subject = sprintf(
			/* translators: %s: Product title */
			__( 'New Product Inquiry: %s', 'product-inquiry' ),
			$data['product_title']
		);

		// Build email body
		$body_parts = array(
			__( 'You have received a new product inquiry.', 'product-inquiry' ),
			'',
			sprintf( __( 'Product: %s', 'product-inquiry' ), $data['product_title'] ),
			sprintf( __( 'Product URL: %s', 'product-inquiry' ), $data['product_url'] ),
			'',
			'--- ' . __( 'Inquiry Details', 'product-inquiry' ) . ' ---',
			'',
			sprintf( __( 'Name: %s', 'product-inquiry' ), $data['name'] ),
			sprintf( __( 'Email: %s', 'product-inquiry' ), $data['email'] ),
		);

		// Add phone if provided
		if ( ! empty( $data['phone'] ) ) {
			$body_parts[] = sprintf( __( 'Phone: %s', 'product-inquiry' ), $data['phone'] );
		}

		$body_parts = array_merge(
			$body_parts,
			array(
				'',
				__( 'Message:', 'product-inquiry' ),
				$data['message'],
				'',
				'--- ' . __( 'Admin Actions', 'product-inquiry' ) . ' ---',
				'',
				sprintf( __( 'View/Reply: %s', 'product-inquiry' ), $inquiry_url ),
				'',
				__( 'This is an automated notification from your Product Inquiry plugin.', 'product-inquiry' ),
			)
		);

		$body = implode( "\n", $body_parts );

		// Email headers
		$headers = array(
			'Content-Type: text/plain; charset=UTF-8',
			'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
			'Reply-To: ' . $data['name'] . ' <' . $data['email'] . '>',
		);

		// Allow filtering of email params
		$subject = apply_filters( 'pi_admin_email_subject', $subject, $data );
		$body    = apply_filters( 'pi_admin_email_body', $body, $data );
		$headers = apply_filters( 'pi_admin_email_headers', $headers, $data );

		// Send email
		$sent = wp_mail( $admin_email, $subject, $body, $headers );

		// Log failure
		if ( ! $sent && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'Product Inquiry Error: Failed to send admin notification for inquiry #' . $data['inquiry_id'] );
		}

		return $sent;
	}

	/**
	 * Send auto-reply email to customer.
	 *
	 * @since    1.0.0
	 * @param    array $data Inquiry notification data.
	 * @return   bool Whether email was sent successfully.
	 */
	private function send_customer_auto_reply( $data ) {
		// Get auto-reply settings
		$subject  = Product_Inquiry_Settings::get_auto_reply_subject();
		$template = Product_Inquiry_Settings::get_auto_reply_message();

		// Parse placeholders in subject
		$subject = Product_Inquiry_Settings::parse_auto_reply_message(
			$subject,
			$data['name'],
			$data['product_title']
		);

		// Parse placeholders in message
		$message = Product_Inquiry_Settings::parse_auto_reply_message(
			$template,
			$data['name'],
			$data['product_title']
		);

		// Email headers
		$admin_email = Product_Inquiry_Settings::get_admin_email();
		$headers     = array(
			'Content-Type: text/plain; charset=UTF-8',
			'From: ' . get_bloginfo( 'name' ) . ' <' . $admin_email . '>',
		);

		// Allow filtering of auto-reply params
		$subject = apply_filters( 'pi_auto_reply_subject', $subject, $data );
		$message = apply_filters( 'pi_auto_reply_message', $message, $data );
		$headers = apply_filters( 'pi_auto_reply_headers', $headers, $data );

		// Send email to customer
		$sent = wp_mail( $data['email'], $subject, $message, $headers );

		// Log failure
		if ( ! $sent && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'Product Inquiry Error: Failed to send auto-reply for inquiry #' . $data['inquiry_id'] );
		}

		return $sent;
	}
}
