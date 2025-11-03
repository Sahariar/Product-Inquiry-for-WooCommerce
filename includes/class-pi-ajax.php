<?php
/**
 * AJAX Handler
 *
 * Processes inquiry form submissions via AJAX.
 *
 * @package Product_Inquiry
 * @since 0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PI_Ajax {

    /**
	 * Handle inquiry submission.
	 * 
	 * Validates, sanitizes, stores inquiry, and sends admin email.
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
        $phone = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
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

		// Create inquiry post
		$post_title = sprintf(
			/* translators: 1: Product title, 2: Sender name */
			__( '%1$s — %2$s', 'product-inquiry' ),
			$product_title,
			$name
		);

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
				error_log( 'PI Error: ' . $inquiry_id->get_error_message() );
			}

			wp_send_json_error(
				array(
					'message' => __( 'Failed to save inquiry. Please try again later.', 'product-inquiry' ),
				)
			);
		}

		// Send admin notification email
		$this->send_admin_notification(
			$inquiry_id,
			array(
				'product_id'    => $product_id,
				'product_title' => $product_title,
				'name'          => $name,
				'email'         => $email,
                'phone'         => $phone,
				'message'       => $message,
			)
		);

		// Fire action hook after inquiry submitted
		do_action( 'pi_inquiry_submitted', $inquiry_id, $inquiry_data );

		// Success response
		wp_send_json_success(
			array(
				'message'    => __( 'Thank you! Your inquiry has been submitted successfully. We will get back to you soon.', 'product-inquiry' ),
				'inquiry_id' => $inquiry_id,
			)
		);
	}

	/**
	 * Send admin notification email.
	 *
	 * @param int   $inquiry_id The inquiry post ID.
	 * @param array $data Inquiry data.
	 * @return bool Whether email was sent successfully.
	 */
	private function send_admin_notification( $inquiry_id, $data ) {
		// Get admin email (from settings or default)
		$admin_email = get_option( 'pi_admin_email', get_option( 'admin_email' ) );

		if ( empty( $admin_email ) ) {
			return false;
		}

		$product_url = get_permalink( $data['product_id'] );
		$inquiry_url = admin_url( 'post.php?post=' . $inquiry_id . '&action=edit' );

		// Email subject
		$subject = sprintf(
			/* translators: %s: Product title */
			__( 'New Product Inquiry: %s', 'product-inquiry' ),
			$data['product_title']
		);

		// Email body
		$body = sprintf(
			/* translators: 1: Product title, 2: Product URL, 3: Sender name, 4: Sender email, 5: Message, 6: Inquiry URL */
			__(
				"You have received a new product inquiry.\n\nProduct: %1\$s\nProduct URL: %2\$s\n\n--- Inquiry Details ---\n\nName: %3\$s\nEmail: %4\$s\n\nMessage:\n%5\$s\n\n--- Admin Actions ---\n\nView/Reply: %6\$s\n\nThis is an automated notification from your Product Inquiry plugin.",
				'product-inquiry'
			),
			$data['product_title'],
			$product_url,
			$data['name'],
			$data['email'],
            $data['phone'],
			$data['message'],
			$inquiry_url
		);

		// Email headers
		$headers = array(
			'Content-Type: text/plain; charset=UTF-8',
			'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
			'Reply-To: ' . $data['name'] . ' <' . $data['email'] . '>',
		);

		// Allow filtering of email params
		$subject = apply_filters( 'pi_admin_email_subject', $subject, $data );
		$body    = apply_filters( 'pi_admin_email_body', $body, $data, $inquiry_id );
		$headers = apply_filters( 'pi_admin_email_headers', $headers, $data );

		// Send email
		$sent = wp_mail( $admin_email, $subject, $body, $headers );

		// Log failure
		if ( ! $sent && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'PI Error: Failed to send admin notification for inquiry #' . $inquiry_id );
		}

		return $sent;
	}
}