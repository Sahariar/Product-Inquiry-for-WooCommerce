<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Product_Inquiry
 * @subpackage Product_Inquiry/admin
 */

class Product_Inquiry_Admin {


	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		$screen = get_current_screen();

		if ( ! $screen || 'product_inquiry' !== $screen->post_type ) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/pi-admin.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();

		if ( ! $screen || 'product_inquiry' !== $screen->post_type ) {
			return;
		}

		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/pi-admin.js',
			array( 'jquery' ),
			$this->version,
			false
		);

		wp_localize_script(
			$this->plugin_name,
			'piAdmin',
			array(
				'confirmDelete'   => __( 'Are you sure you want to delete this inquiry?', 'product-inquiry-for-woocommerce' ),
				'replyComingSoon' => __( 'Reply feature will be available in the next update.', 'product-inquiry-for-woocommerce' ),
			)
		);
	}

	/**
	 * Add notification bubble to admin menu.
	 *
	 * @since    1.0.0
	 */
	public function add_menu_bubble() {
		global $menu;

		$unread_count = $this->get_unread_count();

		if ( $unread_count < 1 ) {
			return;
		}

		foreach ( $menu as $key => $item ) {
			if ( isset( $item[2] ) && 'edit.php?post_type=product_inquiry' === $item[2] ) {
				$menu[ $key ][0] .= sprintf(
					' <span class="update-plugins count-%d"><span class="plugin-count">%d</span></span>',
					absint( $unread_count ),
					number_format_i18n( $unread_count )
				);
				break;
			}
		}
	}

	/**
	 * Get count of unread inquiries.
	 *
	 * @since    1.0.0
	 * @return   int Number of unread inquiries.
	 */
	private function get_unread_count() {
		$args = array(
			'post_type'      => 'product_inquiry',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => 'product_inquiry_for_woocommerce_status',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'product_inquiry_for_woocommerce_status',
					'value'   => 'processed',
					'compare' => '!=',
				),
			),
			'fields'         => 'ids',
		);

		$query = new WP_Query( $args );
		return $query->post_count;
	}

	/**
	 * Define custom columns for list table.
	 *
	 * @since    1.0.0
	 * @param    array $columns Existing columns.
	 * @return   array Modified columns.
	 */
	public function set_custom_columns( $columns ) {
		unset( $columns['date'] );
		unset( $columns['title'] );

		$new_columns = array(
			'cb'          => $columns['cb'],
			'title'       => __( 'Title', 'product-inquiry-for-woocommerce' ),
			'product'     => __( 'Product', 'product-inquiry-for-woocommerce' ),
			'sender_name' => __( 'Sender Name', 'product-inquiry-for-woocommerce' ),
			'email'       => __( 'Email', 'product-inquiry-for-woocommerce' ),
			'status'      => __( 'Status', 'product-inquiry-for-woocommerce' ),
			'date'        => __( 'Date', 'product-inquiry-for-woocommerce' ),
		);

		return $new_columns;
	}

	/**
	 * Populate custom column content.
	 *
	 * @since    1.0.0
	 * @param    string $column  Column name.
	 * @param    int    $post_id Post ID.
	 */
	public function custom_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'product':
				$this->render_product_column( $post_id );
				break;

			case 'sender_name':
				$this->render_sender_name_column( $post_id );
				break;

			case 'email':
				$this->render_email_column( $post_id );
				break;

			case 'status':
				$this->render_status_column( $post_id );
				break;
		}
	}

	/**
	 * Render product column content.
	 *
	 * @since    1.0.0
	 * @param    int $post_id Post ID.
	 */
	private function render_product_column( $post_id ) {
		$product_id = get_post_meta( $post_id, 'product_inquiry_for_woocommerce_product_id', true );

		if ( ! $product_id ) {
			echo '—';
			return;
		}

		$product = wc_get_product( $product_id );

		if ( $product ) {
			printf(
				'<a href="%s" target="_blank">%s</a>',
				esc_url( get_edit_post_link( $product_id ) ),
				esc_html( $product->get_name() )
			);
		} else {
			echo '<span class="pi-product-deleted">' . esc_html__( 'Product deleted', 'product-inquiry-for-woocommerce' ) . '</span>';
		}
	}

	/**
	 * Render sender name column content.
	 *
	 * @since    1.0.0
	 * @param    int $post_id Post ID.
	 */
	private function render_sender_name_column( $post_id ) {
		$name = get_post_meta( $post_id, 'product_inquiry_for_woocommerce_name', true );
		echo esc_html( $name ? $name : '—' );
	}

	/**
	 * Render email column content.
	 *
	 * @since    1.0.0
	 * @param    int $post_id Post ID.
	 */
	private function render_email_column( $post_id ) {
		$email = get_post_meta( $post_id, 'product_inquiry_for_woocommerce_email', true );

		if ( $email ) {
			printf(
				'<a href="mailto:%s">%s</a>',
				esc_attr( $email ),
				esc_html( $email )
			);
		} else {
			echo '—';
		}
	}

	/**
	 * Render status column content.
	 *
	 * @since    1.0.0
	 * @param    int $post_id Post ID.
	 */
	private function render_status_column( $post_id ) {
		$status = get_post_meta( $post_id, 'product_inquiry_for_woocommerce_status', true );

		switch ( $status ) {
			case 'processed':
				echo '<span class="pi-status pi-status-processed">' . esc_html__( 'Processed', 'product-inquiry-for-woocommerce' ) . '</span>';
				break;
			case 'replied':
				echo '<span class="pi-status pi-status-replied">' . esc_html__( 'Replied', 'product-inquiry-for-woocommerce' ) . '</span>';
				break;
			default:
				echo '<span class="pi-status pi-status-unread">' . esc_html__( 'Unread', 'product-inquiry-for-woocommerce' ) . '</span>';
				break;
		}
	}

	/**
	 * Make custom columns sortable.
	 *
	 * @since    1.0.0
	 * @param    array $columns Sortable columns.
	 * @return   array Modified sortable columns.
	 */
	public function sortable_columns( $columns ) {
		$columns['sender_name'] = 'sender_name';
		$columns['email']       = 'email';
		$columns['status']      = 'status';
		return $columns;
	}

	/**
	 * Modify row actions for inquiry posts.
	 *
	 * @since    1.0.0
	 * @param    array   $actions Existing actions.
	 * @param    WP_Post $post    Current post object.
	 * @return   array Modified actions.
	 */
	public function modify_row_actions( $actions, $post ) {
		if ( 'product_inquiry' !== $post->post_type ) {
			return $actions;
		}

		// Remove quick edit
		unset( $actions['inline hide-if-no-js'] );

		$status = get_post_meta( $post->ID, 'product_inquiry_for_woocommerce_status', true );

		if ( 'processed' === $status ) {
			$actions['mark_unprocessed'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( $this->get_status_action_url( $post->ID, 'unprocessed' ) ),
				__( 'Mark Unread', 'product-inquiry-for-woocommerce' )
			);
		} else {
			$actions['mark_processed'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( $this->get_status_action_url( $post->ID, 'processed' ) ),
				__( 'Mark Processed', 'product-inquiry-for-woocommerce' )
			);
		}

		// Add reply action placeholder
		$actions['reply'] = sprintf(
			'<a href="#" class="pi-reply-link">%s</a>',
			__( 'Reply', 'product-inquiry-for-woocommerce' )
		);

		return $actions;
	}

	/**
	 * Get status change action URL with nonce.
	 *
	 * @since    1.0.0
	 * @param    int    $post_id Post ID.
	 * @param    string $action  Action type (processed or unprocessed).
	 * @return   string Action URL.
	 */
	private function get_status_action_url( $post_id, $action ) {
		return wp_nonce_url(
			admin_url( 'admin-post.php?action=pi_mark_' . $action . '&post_id=' . $post_id ),
			'pi_mark_' . $action . '_' . $post_id
		);
	}

	/**
	 * Handle mark as processed action.
	 *
	 * @since    1.0.0
	 */
	public function handle_mark_processed() {
		$this->handle_status_change( 'processed', 'marked_processed' );
	}

	/**
	 * Handle mark as unprocessed action.
	 *
	 * @since    1.0.0
	 */
	public function handle_mark_unprocessed() {
		$this->handle_status_change( 'unprocessed', 'marked_unread' );
	}

	/**
	 * Handle status change for an inquiry.
	 *
	 * @since    1.0.0
	 * @param    string $status       New status (processed or unprocessed).
	 * @param    string $message_code Message code for redirect.
	 */
	private function handle_status_change( $status, $message_code ) {
		if ( ! isset( $_GET['post_id'] ) || ! isset( $_GET['_wpnonce'] ) ) {
			wp_die( esc_html__( 'Invalid request.', 'product-inquiry-for-woocommerce' ) );
		}

		$post_id = absint( $_GET['post_id'] );

		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'pi_mark_' . $status . '_' . $post_id ) ) {
			wp_die( esc_html__( 'Security check failed.', 'product-inquiry-for-woocommerce' ) );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'product-inquiry-for-woocommerce' ) );
		}

		if ( 'processed' === $status ) {
			update_post_meta( $post_id, 'product_inquiry_for_woocommerce_status', 'processed' );
		} else {
			delete_post_meta( $post_id, 'product_inquiry_for_woocommerce_status' );
		}

		wp_safe_redirect( add_query_arg( 'pi_message', $message_code, wp_get_referer() ) );
		exit;
	}

	/**
	 * Register bulk actions.
	 *
	 * @since    1.0.0
	 * @param    array $actions Existing bulk actions.
	 * @return   array Modified bulk actions.
	 */
	public function register_bulk_actions( $actions ) {
		$actions['mark_processed']   = __( 'Mark as Processed', 'product-inquiry-for-woocommerce' );
		$actions['mark_unprocessed'] = __( 'Mark as Unread', 'product-inquiry-for-woocommerce' );
		return $actions;
	}

	/**
	 * Handle bulk actions.
	 *
	 * @since    1.0.0
	 * @param    string $redirect_to Redirect URL.
	 * @param    string $doaction    Action name.
	 * @param    array  $post_ids    Selected post IDs.
	 * @return   string Modified redirect URL.
	 */
	public function handle_bulk_actions( $redirect_to, $doaction, $post_ids ) {
		if ( 'mark_processed' === $doaction ) {
			foreach ( $post_ids as $post_id ) {
				update_post_meta( $post_id, 'product_inquiry_for_woocommerce_status', 'processed' );
			}
			$redirect_to = add_query_arg( 'pi_bulk_processed', count( $post_ids ), $redirect_to );
		}

		if ( 'mark_unprocessed' === $doaction ) {
			foreach ( $post_ids as $post_id ) {
				delete_post_meta( $post_id, 'product_inquiry_for_woocommerce_status' );
			}
			$redirect_to = add_query_arg( 'pi_bulk_unread', count( $post_ids ), $redirect_to );
		}

		return $redirect_to;
	}

	/**
	 * Display admin notices.
	 *
	 * @since    1.0.0
	 */
	public function display_admin_notices() {
		$screen = get_current_screen();

		if ( ! $screen || 'product_inquiry' !== $screen->post_type ) {
			return;
		}

		if ( isset( $_GET['pi_message'] ) ) {
			$this->render_single_action_notice( sanitize_text_field( $_GET['pi_message'] ) );
		}

		if ( isset( $_GET['pi_bulk_processed'] ) ) {
			$this->render_bulk_notice( absint( $_GET['pi_bulk_processed'] ), 'processed' );
		}

		if ( isset( $_GET['pi_bulk_unread'] ) ) {
			$this->render_bulk_notice( absint( $_GET['pi_bulk_unread'] ), 'unread' );
		}
	}

	/**
	 * Render single action notice.
	 *
	 * @since    1.0.0
	 * @param    string $message Message code.
	 */
	private function render_single_action_notice( $message ) {
		$messages = array(
			'marked_processed' => __( 'Inquiry marked as processed.', 'product-inquiry-for-woocommerce' ),
			'marked_unread'    => __( 'Inquiry marked as unread.', 'product-inquiry-for-woocommerce' ),
		);

		if ( isset( $messages[ $message ] ) ) {
			printf(
				'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				esc_html( $messages[ $message ] )
			);
		}
	}

	/**
	 * Render bulk action notice.
	 *
	 * @since    1.0.0
	 * @param    int    $count  Number of items affected.
	 * @param    string $action Action type (processed or unread).
	 */
	private function render_bulk_notice( $count, $action ) {
		if ( 'processed' === $action ) {
			$message = sprintf(
				/* translators: %d: number of inquiries */
				_n(
					'%d inquiry marked as processed.',
					'%d inquiries marked as processed.',
					$count,
					'product-inquiry-for-woocommerce'
				),
				$count
			);
		} else {
			$message = sprintf(
				/* translators: %d: number of inquiries */
				_n(
					'%d inquiry marked as unread.',
					'%d inquiries marked as unread.',
					$count,
					'product-inquiry-for-woocommerce'
				),
				$count
			);
		}

		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html( $message )
		);
	}

	/**
	 * Add metabox for inquiry details.
	 *
	 * @since    1.0.0
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'pi_inquiry_details',
			__( 'Inquiry Details', 'product-inquiry-for-woocommerce' ),
			array( $this, 'render_details_metabox' ),
			'product_inquiry',
			'normal',
			'high'
		);

		add_meta_box(
			'pi_inquiry_actions',
			__( 'Actions', 'product-inquiry-for-woocommerce' ),
			array( $this, 'render_actions_metabox' ),
			'product_inquiry',
			'side',
			'high'
		);
	}

	/**
	 * Render inquiry details metabox.
	 *
	 * @since    1.0.0
	 * @param    WP_Post $post Current post object.
	 */
	public function render_details_metabox( $post ) {
		$product_id   = get_post_meta( $post->ID, 'product_inquiry_for_woocommerce_product_id', true );
		$sender_name  = get_post_meta( $post->ID, 'product_inquiry_for_woocommerce_name', true );
		$sender_email = get_post_meta( $post->ID, 'product_inquiry_for_woocommerce_email', true );
		$message      = get_post_meta( $post->ID, 'product_inquiry_for_woocommerce_message', true );
		$status       = get_post_meta( $post->ID, 'product_inquiry_for_woocommerce_status', true );

		$product = $product_id ? wc_get_product( $product_id ) : null;
		?>
		<div class="pi-inquiry-details">
			<table class="form-table">
				<tr>
				<th scope="row"><?php esc_html_e( 'Status', 'product-inquiry-for-woocommerce' ); ?></th>
				<td>
					<?php
					switch ( $status ) {
						case 'processed':
							echo '<span class="pi-status pi-status-processed">' . esc_html__( 'Processed', 'product-inquiry-for-woocommerce' ) . '</span>';
							break;
						case 'replied':
							echo '<span class="pi-status pi-status-replied">' . esc_html__( 'Replied', 'product-inquiry-for-woocommerce' ) . '</span>';
							break;
						default:
							echo '<span class="pi-status pi-status-unread">' . esc_html__( 'Unread', 'product-inquiry-for-woocommerce' ) . '</span>';
							break;
					}
					?>
				</td>
			</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Product', 'product-inquiry-for-woocommerce' ); ?></th>
					<td>
						<?php if ( $product ) : ?>
							<a href="<?php echo esc_url( get_edit_post_link( $product_id ) ); ?>" target="_blank">
								<?php echo esc_html( $product->get_name() ); ?>
							</a>
							<br>
							<a href="<?php echo esc_url( $product->get_permalink() ); ?>" target="_blank" class="pi-view-product">
								<?php esc_html_e( 'View Product Page', 'product-inquiry-for-woocommerce' ); ?>
							</a>
						<?php else : ?>
							<span class="pi-product-deleted"><?php esc_html_e( 'Product deleted', 'product-inquiry-for-woocommerce' ); ?></span>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Sender Name', 'product-inquiry-for-woocommerce' ); ?></th>
					<td><?php echo esc_html( $sender_name ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Email', 'product-inquiry-for-woocommerce' ); ?></th>
					<td>
						<a href="mailto:<?php echo esc_attr( $sender_email ); ?>">
							<?php echo esc_html( $sender_email ); ?>
						</a>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Message', 'product-inquiry-for-woocommerce' ); ?></th>
					<td>
						<div class="pi-message-content">
							<?php echo wp_kses_post( nl2br( $message ) ); ?>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Submitted', 'product-inquiry-for-woocommerce' ); ?></th>
					<td>
						<?php echo esc_html( get_the_date( '', $post ) . ' ' . get_the_time( '', $post ) ); ?>
					</td>
				</tr>
				
			</table>
		</div>
		<?php
	}

	/**
	 * Render inquiry actions metabox.
	 *
	 * @since    1.0.0
	 * @param    WP_Post $post Current post object.
	 */
	public function render_actions_metabox( $post ) {
		$status = get_post_meta( $post->ID, 'product_inquiry_for_woocommerce_status', true );
		?>
		<div class="pi-inquiry-actions">
			<?php if ( 'processed' === $status ) : ?>
				<a href="<?php echo esc_url( $this->get_status_action_url( $post->ID, 'unprocessed' ) ); ?>" 
					class="button button-secondary button-large">
					<?php esc_html_e( 'Mark as Unread', 'product-inquiry-for-woocommerce' ); ?>
				</a>
			<?php else : ?>
				<a href="<?php echo esc_url( $this->get_status_action_url( $post->ID, 'processed' ) ); ?>" 
					class="button button-primary button-large">
					<?php esc_html_e( 'Mark as Processed', 'product-inquiry-for-woocommerce' ); ?>
				</a>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Show welcome notice after activation.
	 */
	public function show_welcome_notice() {
		// Only show to users who can manage options
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if notice was dismissed
		if ( get_option( 'pi_welcome_dismissed' ) ) {
			return;
		}

		// Check if activated recently (within 7 days)
		$activated_time = get_option( 'pi_activated_time' );
		if ( ! $activated_time || ( current_time( 'timestamp' ) - $activated_time ) > WEEK_IN_SECONDS ) {
			return;
		}

		?>
		<div class="notice notice-success is-dismissible" data-notice="pi-welcome">
			<p>
				<strong><?php esc_html_e( 'Product Inquiry for WooCommerce', 'product-inquiry-for-woocommerce' ); ?></strong>
				<?php esc_html_e( 'is now active! Start receiving customer inquiries on your product pages.', 'product-inquiry-for-woocommerce' ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product_inquiry' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'View Inquiries', 'product-inquiry-for-woocommerce' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=product-inquiry-for-woocommerce-settings' ) ); ?>" class="button">
					<?php esc_html_e( 'Settings', 'product-inquiry-for-woocommerce' ); ?>
				</a>
			</p>
		</div>
		<?php
	}
}
