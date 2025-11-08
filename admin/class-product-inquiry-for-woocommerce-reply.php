<?php
/**
 * Reply functionality for Product Inquiry
 *
 * @link       https://sahariarkabir.com/
 * @since      1.0.0
 *
 * @package    Product_Inquiry
 * @subpackage Product_Inquiry/admin
 */

/**
 * Reply handling class.
 *
 * Allows admins to reply to inquiries via email with logging.
 *
 * @package    Product_Inquiry
 * @subpackage Product_Inquiry/admin
 * @author     Sahariar Kabir<sahariark@gmail.com>
 */
class Product_Inquiry_Reply {

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
	 * Handle AJAX reply submission.
	 *
	 * @since    1.0.0
	 */
	public function handle_ajax_reply() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'pi_reply_nonce' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Security verification failed.', 'product-inquiry-for-woocommerce' ),
				)
			);
		}

		// Get and validate inquiry ID
		$inquiry_id = isset( $_POST['inquiry_id'] ) ? absint( $_POST['inquiry_id'] ) : 0;

		if ( ! $inquiry_id || 'product_inquiry' !== get_post_type( $inquiry_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid inquiry.', 'product-inquiry-for-woocommerce' ),
				)
			);
		}

		// Check capabilities
		if ( ! current_user_can( 'edit_post', $inquiry_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to reply to this inquiry.', 'product-inquiry-for-woocommerce' ),
				)
			);
		}

		// Get and sanitize reply message
		$reply_message = isset( $_POST['reply_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['reply_message'] ) ) : '';

		// Validate reply message
		if ( empty( $reply_message ) || strlen( $reply_message ) < 10 ) {
			wp_send_json_error(
				array(
					'message' => __( 'Please enter a reply message with at least 10 characters.', 'product-inquiry-for-woocommerce' ),
				)
			);
		}

		// Get inquiry data
		$inquiry_data = $this->get_inquiry_data( $inquiry_id );

		if ( ! $inquiry_data ) {
			wp_send_json_error(
				array(
					'message' => __( 'Failed to retrieve inquiry data.', 'product-inquiry-for-woocommerce' ),
				)
			);
		}

		// Send reply email
		$email_sent = $this->send_reply_email( $inquiry_id, $inquiry_data, $reply_message );

		if ( ! $email_sent ) {
			wp_send_json_error(
				array(
					'message' => __( 'Failed to send reply email. Please check your email configuration.', 'product-inquiry-for-woocommerce' ),
				)
			);
		}

		// Log reply in post meta
		$this->log_reply( $inquiry_id, $reply_message );

		// Update status to "replied"
		update_post_meta( $inquiry_id, 'product_inquiry_for_woocommerce_status', 'replied' );

		// Fire action hook
		do_action( 'pi_inquiry_replied', $inquiry_id, $reply_message, $inquiry_data );

		// Success response
		wp_send_json_success(
			array(
				'message'     => __( 'Reply sent successfully!', 'product-inquiry-for-woocommerce' ),
				'reply_count' => $this->get_reply_count( $inquiry_id ),
				'last_reply'  => $this->format_last_reply( $inquiry_id ),
			)
		);
	}

	/**
	 * Get inquiry data for reply.
	 *
	 * @since    1.0.0
	 * @param    int $inquiry_id Inquiry post ID.
	 * @return   array|false Inquiry data or false on failure.
	 */
	private function get_inquiry_data( $inquiry_id ) {
		$inquiry = get_post( $inquiry_id );

		if ( ! $inquiry ) {
			return false;
		}

		$product_id = get_post_meta( $inquiry_id, 'product_inquiry_for_woocommerce_product_id', true );
		$name       = get_post_meta( $inquiry_id, 'product_inquiry_for_woocommerce_name', true );
		$email      = get_post_meta( $inquiry_id, 'product_inquiry_for_woocommerce_email', true );
		$phone      = get_post_meta( $inquiry_id, 'product_inquiry_for_woocommerce_phone', true );
		$message    = get_post_meta( $inquiry_id, 'product_inquiry_for_woocommerce_message', true );

		// Get product details
		$product       = $product_id ? wc_get_product( $product_id ) : null;
		$product_title = $product ? $product->get_name() : __( '(Product Deleted)', 'product-inquiry-for-woocommerce' );
		$product_url   = $product ? $product->get_permalink() : '';

		return array(
			'inquiry_id'     => $inquiry_id,
			'product_id'     => $product_id,
			'product_title'  => $product_title,
			'product_url'    => $product_url,
			'customer_name'  => $name,
			'customer_email' => $email,
			'customer_phone' => $phone,
			'message'        => $message,
			'date'           => get_the_date( 'Y-m-d H:i:s', $inquiry ),
		);
	}

	/**
	 * Send reply email to customer.
	 *
	 * @since    1.0.0
	 * @param    int    $inquiry_id   Inquiry post ID.
	 * @param    array  $inquiry_data Inquiry data.
	 * @param    string $reply_message Admin reply message.
	 * @return   bool Whether email was sent successfully.
	 */
	private function send_reply_email( $inquiry_id, $inquiry_data, $reply_message ) {
		$admin_email  = Product_Inquiry_Settings::get_admin_email();
		$current_user = wp_get_current_user();

		// Email subject
		$subject = sprintf(
			/* translators: %s: Product title */
			__( 'Response to your inquiry about: %s', 'product-inquiry-for-woocommerce' ),
			$inquiry_data['product_title']
		);

		// Build email body
		$body = $this->build_reply_email_body( $inquiry_data, $reply_message, $current_user );

		// Email headers
		$headers = array(
			'Content-Type: text/plain; charset=UTF-8',
			'From: ' . get_bloginfo( 'name' ) . ' <' . $admin_email . '>',
			'Reply-To: ' . $admin_email,
		);

		// Allow filtering
		$subject = apply_filters( 'pi_reply_email_subject', $subject, $inquiry_data, $reply_message );
		$body    = apply_filters( 'pi_reply_email_body', $body, $inquiry_data, $reply_message );
		$headers = apply_filters( 'pi_reply_email_headers', $headers, $inquiry_data, $reply_message );

		// Send email
		$sent = wp_mail( $inquiry_data['customer_email'], $subject, $body, $headers );
		return $sent;
	}

	/**
	 * Build reply email body.
	 *
	 * @since    1.0.0
	 * @param    array   $inquiry_data Inquiry data.
	 * @param    string  $reply_message Admin reply message.
	 * @param    WP_User $current_user  Current admin user.
	 * @return   string Email body.
	 */
	private function build_reply_email_body( $inquiry_data, $reply_message, $current_user ) {
		$admin_name = ! empty( $current_user->display_name ) ? $current_user->display_name : __( 'Store Admin', 'product-inquiry-for-woocommerce' );

		$body_parts = array(
			sprintf(
				/* translators: %s: Customer name */
				__( 'Hello %s,', 'product-inquiry-for-woocommerce' ),
				$inquiry_data['customer_name']
			),
			'',
			__( 'Thank you for your inquiry. Here is our response:', 'product-inquiry-for-woocommerce' ),
			'',
			'--- ' . __( 'Our Response', 'product-inquiry-for-woocommerce' ) . ' ---',
			'',
			$reply_message,
			'',
			'--- ' . __( 'Your Original Message', 'product-inquiry-for-woocommerce' ) . ' ---',
			'',
			sprintf(
				/* translators: %s: Product title */
				__( 'Product: %s', 'product-inquiry-for-woocommerce' ),
				$inquiry_data['product_title']
			),
		);

		// Add product link if available
		if ( ! empty( $inquiry_data['product_url'] ) ) {
			$body_parts[] = sprintf(
				/* translators: %s: Product URL */
				__( 'Product Link: %s', 'product-inquiry-for-woocommerce' ),
				$inquiry_data['product_url']
			);
		}

		$body_parts = array_merge(
			$body_parts,
			array(
				'',
				sprintf(
					/* translators: %s: Date */
					__( 'Date: %s', 'product-inquiry-for-woocommerce' ),
					$inquiry_data['date']
				),
				'',
				__( 'Your Message:', 'product-inquiry-for-woocommerce' ),
				$inquiry_data['message'],
				'',
				'--- ' . __( 'Contact Information', 'product-inquiry-for-woocommerce' ) . ' ---',
				'',
				sprintf(
					/* translators: %s: Admin name */
					__( 'Best regards,', 'product-inquiry-for-woocommerce' ) . "\n%s",
					$admin_name
				),
				get_bloginfo( 'name' ),
				Product_Inquiry_Settings::get_admin_email(),
			)
		);

		return implode( "\n", $body_parts );
	}

	/**
	 * Log reply in post meta.
	 *
	 * @since    1.0.0
	 * @param    int    $inquiry_id    Inquiry post ID.
	 * @param    string $reply_message Reply message.
	 */
	private function log_reply( $inquiry_id, $reply_message ) {
		$current_user = wp_get_current_user();

		$reply_entry = array(
			'date'       => current_time( 'mysql' ),
			'user_id'    => $current_user->ID,
			'user_name'  => $current_user->display_name,
			'user_email' => $current_user->user_email,
			'message'    => $reply_message,
		);

		// Get existing replies
		$replies = get_post_meta( $inquiry_id, 'product_inquiry_for_woocommerce_replies', true );

		if ( ! is_array( $replies ) ) {
			$replies = array();
		}

		// Add new reply
		$replies[] = $reply_entry;

		// Update meta
		update_post_meta( $inquiry_id, 'product_inquiry_for_woocommerce_replies', $replies );
	}

	/**
	 * Get reply count for an inquiry.
	 *
	 * @since    1.0.0
	 * @param    int $inquiry_id Inquiry post ID.
	 * @return   int Reply count.
	 */
	private function get_reply_count( $inquiry_id ) {
		$replies = get_post_meta( $inquiry_id, 'product_inquiry_for_woocommerce_replies', true );
		return is_array( $replies ) ? count( $replies ) : 0;
	}

	/**
	 * Format last reply for display.
	 *
	 * @since    1.0.0
	 * @param    int $inquiry_id Inquiry post ID.
	 * @return   string Formatted last reply info.
	 */
	private function format_last_reply( $inquiry_id ) {
		$replies = get_post_meta( $inquiry_id, 'product_inquiry_for_woocommerce_replies', true );

		if ( ! is_array( $replies ) || empty( $replies ) ) {
			return '';
		}

		$last_reply = end( $replies );

		return sprintf(
			/* translators: 1: User name, 2: Date */
			__( 'Last reply by %1$s on %2$s', 'product-inquiry-for-woocommerce' ),
			$last_reply['user_name'],
			date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $last_reply['date'] ) )
		);
	}

	/**
	 * Display replies history in metabox.
	 *
	 * @since    1.0.0
	 * @param    WP_Post $post Current post object.
	 */
	public function render_replies_metabox( $post ) {
		$replies = get_post_meta( $post->ID, 'product_inquiry_for_woocommerce_replies', true );

		if ( ! is_array( $replies ) || empty( $replies ) ) {
			echo '<p>' . esc_html__( 'No replies sent yet.', 'product-inquiry-for-woocommerce' ) . '</p>';
			return;
		}

		echo '<div class="pi-replies-history">';

		foreach ( $replies as $index => $reply ) {
			$reply_number = $index + 1;
			$reply_date   = date_i18n(
				get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
				strtotime( $reply['date'] )
			);

			?>
			<div class="pi-reply-item">
				<div class="pi-reply-header">
					<strong>
						<?php
						echo esc_html(
							sprintf(
								/* translators: 1: Reply number, 2: User name */
								__( 'Reply #%1$d by %2$s', 'product-inquiry-for-woocommerce' ),
								$reply_number,
								$reply['user_name']
							)
						);
						?>
					</strong>
					<span class="pi-reply-date"><?php echo esc_html( $reply_date ); ?></span>
				</div>
				<div class="pi-reply-message">
					<?php echo wp_kses_post( nl2br( $reply['message'] ) ); ?>
				</div>
			</div>
			<?php
		}

		echo '</div>';
	}

	/**
	 * Render reply form metabox.
	 *
	 * @since    1.0.0
	 * @param    WP_Post $post Current post object.
	 */
	public function render_reply_form_metabox( $post ) {
		$customer_email = get_post_meta( $post->ID, 'product_inquiry_for_woocommerce_email', true );
		$customer_name  = get_post_meta( $post->ID, 'product_inquiry_for_woocommerce_name', true );

		wp_nonce_field( 'pi_reply_nonce', 'pi_reply_nonce_field' );
		?>
		<div class="pi-reply-form">
			<p>
				<strong><?php esc_html_e( 'Send Reply To:', 'product-inquiry-for-woocommerce' ); ?></strong><br>
				<?php
				printf(
					'%s (%s)',
					esc_html( $customer_name ),
					'<a href="mailto:' . esc_attr( $customer_email ) . '">' . esc_html( $customer_email ) . '</a>'
				);
				?>
			</p>

			<p>
				<label for="pi_reply_message">
					<?php esc_html_e( 'Your Reply:', 'product-inquiry-for-woocommerce' ); ?>
				</label>
				<textarea 
					id="pi_reply_message" 
					name="pi_reply_message" 
					rows="8" 
					class="widefat"
					placeholder="<?php esc_attr_e( 'Type your reply here...', 'product-inquiry-for-woocommerce' ); ?>"
				></textarea>
			</p>

			<p class="pi-reply-actions">
				<button type="button" id="pi_send_reply_btn" class="button button-primary button-large">
					<?php esc_html_e( 'Send Reply', 'product-inquiry-for-woocommerce' ); ?>
				</button>
				<span class="spinner"></span>
			</p>

			<div id="pi_reply_message_container" style="display:none; margin-top: 10px;"></div>
		</div>
		<?php
	}

	/**
	 * Add reply metaboxes.
	 *
	 * @since    1.0.0
	 */
	public function add_reply_metaboxes() {
		add_meta_box(
			'pi_reply_form',
			__( 'Send Reply', 'product-inquiry-for-woocommerce' ),
			array( $this, 'render_reply_form_metabox' ),
			'product_inquiry',
			'normal',
			'high'
		);

		add_meta_box(
			'pi_replies_history',
			__( 'Reply History', 'product-inquiry-for-woocommerce' ),
			array( $this, 'render_replies_metabox' ),
			'product_inquiry',
			'normal',
			'default'
		);
	}

	/**
	 * Enqueue reply admin scripts.
	 *
	 * @since    1.0.0
	 * @param    string $hook Current admin page hook.
	 */
	public function enqueue_reply_scripts( $hook ) {
		if ( 'post.php' !== $hook ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'product_inquiry' !== $screen->post_type ) {
			return;
		}

		wp_localize_script(
			$this->plugin_name,
			'piReply',
			array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'pi_reply_nonce' ),
				'inquiry_id' => get_the_ID(),
				'messages'   => array(
					'sending'      => __( 'Sending reply...', 'product-inquiry-for-woocommerce' ),
					'success'      => __( 'Reply sent successfully!', 'product-inquiry-for-woocommerce' ),
					'error'        => __( 'Failed to send reply. Please try again.', 'product-inquiry-for-woocommerce' ),
					'empty'        => __( 'Please enter a reply message.', 'product-inquiry-for-woocommerce' ),
					'confirm_send' => __( 'Are you sure you want to send this reply?', 'product-inquiry-for-woocommerce' ),
				),
			)
		);
	}
}
