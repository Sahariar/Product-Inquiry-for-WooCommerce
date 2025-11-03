<?php
/**
 * CSV Export functionality for Product Inquiry
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Product_Inquiry
 * @subpackage Product_Inquiry/admin
 */

/**
 * CSV Export class.
 *
 * Handles exporting inquiries to CSV format with streaming support
 * for large datasets.
 *
 * @package    Product_Inquiry
 * @subpackage Product_Inquiry/admin
 * @author     Your Name <email@example.com>
 */
class Product_Inquiry_Export {

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
	 * Maximum number of inquiries to export in one request.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      int    $max_export_limit    Export limit.
	 */
	private $max_export_limit = 5000;

	/**
	 * Number of inquiries to process per batch when streaming.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      int    $batch_size    Batch size for streaming.
	 */
	private $batch_size = 100;

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
	 * Add export option to bulk actions dropdown.
	 *
	 * @since    1.0.0
	 * @param    array $actions Existing bulk actions.
	 * @return   array Modified bulk actions.
	 */
	public function add_bulk_export_action( $actions ) {
		$actions['export_csv'] = __( 'Export to CSV', 'product-inquiry' );
		return $actions;
	}

	/**
	 * Handle bulk export action.
	 *
	 * @since    1.0.0
	 * @param    string $redirect_to Redirect URL.
	 * @param    string $doaction    Action name.
	 * @param    array  $post_ids    Selected post IDs.
	 * @return   string Modified redirect URL.
	 */
	public function handle_bulk_export( $redirect_to, $doaction, $post_ids ) {
		if ( 'export_csv' !== $doaction ) {
			return $redirect_to;
		}

		// Check capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You do not have permission to export inquiries.', 'product-inquiry' ) );
		}

		// Limit export size
		if ( count( $post_ids ) > $this->max_export_limit ) {
			return add_query_arg(
				array(
					'pi_export_error' => 'too_many',
					'count'           => count( $post_ids ),
					'limit'           => $this->max_export_limit,
				),
				$redirect_to
			);
		}

		// Perform export
		$this->export_inquiries( $post_ids );

		// This will never be reached because export_inquiries() exits
		exit;
	}

	/**
	 * Add export action to row actions.
	 *
	 * @since    1.0.0
	 * @param    array   $actions Existing actions.
	 * @param    WP_Post $post    Current post object.
	 * @return   array Modified actions.
	 */
	public function add_row_export_action( $actions, $post ) {
		if ( 'product_inquiry' !== $post->post_type ) {
			return $actions;
		}

		$export_url = wp_nonce_url(
			add_query_arg(
				array(
					'action'     => 'pi_export_single',
					'inquiry_id' => $post->ID,
				),
				admin_url( 'admin.php' )
			),
			'pi_export_single_' . $post->ID
		);

		$actions['export'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $export_url ),
			__( 'Export CSV', 'product-inquiry' )
		);

		return $actions;
	}

	/**
	 * Handle single inquiry export.
	 *
	 * @since    1.0.0
	 */
	public function handle_single_export() {
		if ( ! isset( $_GET['inquiry_id'] ) || ! isset( $_GET['_wpnonce'] ) ) {
			wp_die( esc_html__( 'Invalid request.', 'product-inquiry' ) );
		}

		$inquiry_id = absint( $_GET['inquiry_id'] );

		// Verify nonce
		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'pi_export_single_' . $inquiry_id ) ) {
			wp_die( esc_html__( 'Security check failed.', 'product-inquiry' ) );
		}

		// Check capabilities
		if ( ! current_user_can( 'edit_post', $inquiry_id ) ) {
			wp_die( esc_html__( 'You do not have permission to export this inquiry.', 'product-inquiry' ) );
		}

		// Verify post type
		if ( 'product_inquiry' !== get_post_type( $inquiry_id ) ) {
			wp_die( esc_html__( 'Invalid inquiry.', 'product-inquiry' ) );
		}

		// Export single inquiry
		$this->export_inquiries( array( $inquiry_id ) );

		exit;
	}

	/**
	 * Add export all button above list table.
	 *
	 * @since    1.0.0
	 * @param    string $which Top or bottom of the table.
	 */
	public function add_export_all_button( $which ) {
		global $typenow;

		if ( 'product_inquiry' !== $typenow || 'top' !== $which ) {
			return;
		}

		$export_url = wp_nonce_url(
			add_query_arg(
				array(
					'action' => 'pi_export_all',
				),
				admin_url( 'admin.php' )
			),
			'pi_export_all'
		);

		printf(
			'<a href="%s" class="button" style="margin: 1px 8px 0 0;">%s</a>',
			esc_url( $export_url ),
			esc_html__( 'Export All to CSV', 'product-inquiry' )
		);
	}

	/**
	 * Handle export all inquiries.
	 *
	 * @since    1.0.0
	 */
	public function handle_export_all() {
		if ( ! isset( $_GET['_wpnonce'] ) ) {
			wp_die( esc_html__( 'Invalid request.', 'product-inquiry' ) );
		}

		// Verify nonce
		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'pi_export_all' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'product-inquiry' ) );
		}

		// Check capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You do not have permission to export inquiries.', 'product-inquiry' ) );
		}

		// Get total count
		$total = wp_count_posts( 'product_inquiry' );
		$count = isset( $total->publish ) ? $total->publish : 0;

		if ( $count > $this->max_export_limit ) {
			wp_die(
				sprintf(
					/* translators: 1: Total count, 2: Export limit */
					esc_html__( 'Cannot export all inquiries. You have %1$d inquiries, but the export limit is %2$d. Please use bulk actions to export specific inquiries or contact support for a custom export solution.', 'product-inquiry' ),
					$count,
					$this->max_export_limit
				)
			);
		}

		// Get all inquiry IDs
		$args = array(
			'post_type'      => 'product_inquiry',
			'post_status'    => 'publish',
			'posts_per_page' => $this->max_export_limit,
			'fields'         => 'ids',
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$inquiry_ids = get_posts( $args );

		if ( empty( $inquiry_ids ) ) {
			wp_die( esc_html__( 'No inquiries found to export.', 'product-inquiry' ) );
		}

		// Export all inquiries
		$this->export_inquiries( $inquiry_ids );

		exit;
	}

	/**
	 * Export inquiries to CSV.
	 *
	 * Uses streaming output to handle large datasets efficiently.
	 *
	 * @since    1.0.0
	 * @param    array $inquiry_ids Array of inquiry post IDs.
	 */
	private function export_inquiries( $inquiry_ids ) {
		if ( empty( $inquiry_ids ) ) {
			wp_die( esc_html__( 'No inquiries selected for export.', 'product-inquiry' ) );
		}

		// Set execution time limit for large exports
		set_time_limit( 300 ); // 5 minutes

		// Generate filename
		$filename = $this->generate_filename( count( $inquiry_ids ) );

		// Set headers for CSV download
		$this->set_csv_headers( $filename );

		// Open output stream
		$output = fopen( 'php://output', 'w' );

		// Add BOM for UTF-8 (helps Excel recognize encoding)
		fprintf( $output, chr(0xEF) . chr(0xBB) . chr(0xBF) );

		// Write CSV header row
		fputcsv( $output, $this->get_csv_headers() );

		// Process inquiries in batches for memory efficiency
		$batches = array_chunk( $inquiry_ids, $this->batch_size );

		foreach ( $batches as $batch ) {
			$this->write_inquiry_batch( $output, $batch );

			// Flush output buffer to send data to browser
			if ( ob_get_level() > 0 ) {
				ob_flush();
			}
			flush();
		}

		// Close output stream
		fclose( $output );

		exit;
	}

	/**
	 * Generate filename for CSV export.
	 *
	 * @since    1.0.0
	 * @param    int $count Number of inquiries being exported.
	 * @return   string Filename.
	 */
	private function generate_filename( $count ) {
		$site_name = sanitize_title( get_bloginfo( 'name' ) );
		$date      = gmdate( 'Y-m-d-His' );
		$suffix    = $count > 1 ? 'bulk' : 'single';

		return sprintf(
			'product-inquiries-%s-%s-%s.csv',
			$site_name,
			$suffix,
			$date
		);
	}

	/**
	 * Set HTTP headers for CSV download.
	 *
	 * @since    1.0.0
	 * @param    string $filename Filename for download.
	 */
	private function set_csv_headers( $filename ) {
		// Prevent caching
		nocache_headers();

		// Set content type and disposition
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
	}

	/**
	 * Get CSV column headers.
	 *
	 * @since    1.0.0
	 * @return   array Column headers.
	 */
	private function get_csv_headers() {
		$headers = array(
			__( 'Inquiry ID', 'product-inquiry' ),
			__( 'Date', 'product-inquiry' ),
			__( 'Status', 'product-inquiry' ),
			__( 'Product ID', 'product-inquiry' ),
			__( 'Product Title', 'product-inquiry' ),
			__( 'Sender Name', 'product-inquiry' ),
			__( 'Sender Email', 'product-inquiry' ),
			__( 'Sender Phone', 'product-inquiry' ),
			__( 'Message', 'product-inquiry' ),
		);

		return apply_filters( 'pi_csv_export_headers', $headers );
	}

	/**
	 * Write a batch of inquiries to CSV output.
	 *
	 * @since    1.0.0
	 * @param    resource $output  Output stream.
	 * @param    array    $inquiry_ids Array of inquiry IDs.
	 */
	private function write_inquiry_batch( $output, $inquiry_ids ) {
		foreach ( $inquiry_ids as $inquiry_id ) {
			$row = $this->get_inquiry_row_data( $inquiry_id );
			if ( $row ) {
				fputcsv( $output, $row );
			}
		}
	}

	/**
	 * Get inquiry data as CSV row.
	 *
	 * @since    1.0.0
	 * @param    int $inquiry_id Inquiry post ID.
	 * @return   array|null Row data or null if inquiry not found.
	 */
	private function get_inquiry_row_data( $inquiry_id ) {
		$inquiry = get_post( $inquiry_id );

		if ( ! $inquiry || 'product_inquiry' !== $inquiry->post_type ) {
			return null;
		}

		// Get meta data
		$product_id = get_post_meta( $inquiry_id, '_pi_product_id', true );
		$name       = get_post_meta( $inquiry_id, '_pi_name', true );
		$email      = get_post_meta( $inquiry_id, '_pi_email', true );
		$phone      = get_post_meta( $inquiry_id, '_pi_phone', true );
		$message    = get_post_meta( $inquiry_id, '_pi_message', true );
		$status     = get_post_meta( $inquiry_id, '_pi_status', true );

		// Get product title
		$product_title = '';
		if ( $product_id ) {
			$product = wc_get_product( $product_id );
			if ( $product ) {
				$product_title = $product->get_name();
			} else {
				$product_title = __( '(Product Deleted)', 'product-inquiry' );
			}
		}

		// Format status
		$status_label = 'new' === $status ? __( 'New', 'product-inquiry' ) : __( 'Processed', 'product-inquiry' );

		// Format date
		$date = get_the_date( 'Y-m-d H:i:s', $inquiry );

		// Build row data
		$row = array(
			$inquiry_id,
			$date,
			$status_label,
			$product_id,
			$product_title,
			$name,
			$email,
			$phone,
			$this->sanitize_csv_field( $message ),
		);

		return apply_filters( 'pi_csv_export_row', $row, $inquiry_id );
	}

	/**
	 * Sanitize field for CSV export.
	 *
	 * Removes excess whitespace and normalizes line breaks.
	 *
	 * @since    1.0.0
	 * @param    string $value Field value.
	 * @return   string Sanitized value.
	 */
	private function sanitize_csv_field( $value ) {
		// Replace multiple line breaks with single line break
		$value = preg_replace( "/[\r\n]+/", "\n", $value );

		// Trim whitespace
		$value = trim( $value );

		return $value;
	}

	/**
	 * Display admin notice for export errors.
	 *
	 * @since    1.0.0
	 */
	public function display_export_notices() {
		$screen = get_current_screen();

		if ( ! $screen || 'edit-product_inquiry' !== $screen->id ) {
			return;
		}

		if ( isset( $_GET['pi_export_error'] ) && 'too_many' === $_GET['pi_export_error'] ) {
			$count = isset( $_GET['count'] ) ? absint( $_GET['count'] ) : 0;
			$limit = isset( $_GET['limit'] ) ? absint( $_GET['limit'] ) : $this->max_export_limit;

			printf(
				'<div class="notice notice-error is-dismissible"><p>%s</p></div>',
				sprintf(
					/* translators: 1: Selected count, 2: Export limit */
					esc_html__( 'Cannot export %1$d inquiries. The export limit is %2$d. Please select fewer inquiries or use the "Export All" button for a complete export.', 'product-inquiry' ),
					$count,
					$limit
				)
			);
		}
	}

	/**
	 * Get export statistics.
	 *
	 * Useful for displaying export info in admin.
	 *
	 * @since    1.0.0
	 * @return   array Export statistics.
	 */
	public function get_export_stats() {
		$total = wp_count_posts( 'product_inquiry' );
		$count = isset( $total->publish ) ? $total->publish : 0;

		return array(
			'total_inquiries'  => $count,
			'export_limit'     => $this->max_export_limit,
			'batch_size'       => $this->batch_size,
			'can_export_all'   => $count <= $this->max_export_limit,
			'needs_pagination' => $count > $this->max_export_limit,
		);
	}
}