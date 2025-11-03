<?php
/**
 * Frontend functionality for Product Inquiry plugin.
 *
 * @package ProductInquiry
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * PI_Frontend Class
 */
class PI_Frontend {

    /**
     * Single instance.
     *
     * @var PI_Frontend
     */
    private static $instance = null;

    /**
     * Get instance.
     *
     * @return PI_Frontend
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        // Add inquiry button after add to cart button.
        add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'render_inquiry_button' ) );
        
        // Add modal to footer.
        add_action( 'wp_footer', array( $this, 'render_inquiry_modal' ) );
        
        // Enqueue assets on single product pages.
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Render the "Product Inquiry" button.
     */
    public function render_inquiry_button() {
        if ( ! is_product() ) {
            return;
        }

        ?>
        <button type="button" class="pi-inquiry-button button alt" id="pi-open-modal">
            <?php esc_html_e( 'Product Inquiry', 'product-inquiry' ); ?>
        </button>
        <?php
    }

    /**
     * Render the inquiry modal markup.
     */
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
            <div 
                id="pi-modal" 
                class="pi-modal" 
                role="dialog" 
                aria-labelledby="pi-modal-title" 
                aria-modal="true"
            >
                <div class="pi-modal-content">
                    <button 
                        type="button" 
                        class="pi-modal-close" 
                        aria-label="<?php esc_attr_e( 'Close inquiry form', 'product-inquiry' ); ?>"
                    >
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
                        
                        <input 
                            type="hidden" 
                            name="product_id" 
                            value="<?php echo esc_attr( $product_id ); ?>"
                        >

                        <div class="pi-form-row">
                            <label for="pi-name">
                                <?php esc_html_e( 'Your Name', 'product-inquiry' ); ?> 
                                <span class="pi-required">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="pi-name" 
                                name="name" 
                                required 
                                aria-required="true"
                            >
                        </div>

                        <div class="pi-form-row">
                            <label for="pi-email">
                                <?php esc_html_e( 'Your Email', 'product-inquiry' ); ?> 
                                <span class="pi-required">*</span>
                            </label>
                            <input 
                                type="email" 
                                id="pi-email" 
                                name="email" 
                                required 
                                aria-required="true"
                            >
                        </div>

                        <div class="pi-form-row">
                            <label for="pi-phone">
                                <?php esc_html_e( 'Phone Number', 'product-inquiry' ); ?>
                            </label>
                            <input 
                                type="tel" 
                                id="pi-phone" 
                                name="phone"
                            >
                        </div>

                        <div class="pi-form-row">
                            <label for="pi-message">
                                <?php esc_html_e( 'Your Message', 'product-inquiry' ); ?> 
                                <span class="pi-required">*</span>
                            </label>
                            <textarea 
                                id="pi-message" 
                                name="message" 
                                rows="5" 
                                required 
                                aria-required="true"
                            ></textarea>
                        </div>

                        <div class="pi-form-actions">
                            <button 
                                type="submit" 
                                class="pi-submit-button button alt"
                            >
                                <?php esc_html_e( 'Send Inquiry', 'product-inquiry' ); ?>
                            </button>
                            <button 
                                type="button" 
                                class="pi-cancel-button button"
                            >
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

    /**
     * Enqueue frontend assets.
     */
    public function enqueue_assets() {
        if ( ! is_product() ) {
            return;
        }

        // Enqueue CSS.
        wp_enqueue_style(
            'pi-frontend',
            PI_PLUGIN_URL . 'assets/css/pi-frontend.css',
            array(),
            PI_VERSION
        );

        // Enqueue JS.
        wp_enqueue_script(
            'pi-frontend',
            PI_PLUGIN_URL . 'assets/js/pi-frontend.js',
            array( 'jquery' ),
            PI_VERSION,
            true
        );

        // Localize script for AJAX (prepared for Feature 2).
        wp_localize_script(
            'pi-frontend',
            'piData',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'pi_ajax_nonce' ),
            )
        );
    }
}