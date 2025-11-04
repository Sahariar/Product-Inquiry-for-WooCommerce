<?php
/**
 * Custom Post Type Handler
 *
 * Registers and manages the product_inquiry CPT.
 *
 * @package Product_Inquiry
 * @since 0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Product_Inquiry_CPT {

	/**
	 * Initialize the CPT.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_cpt' ) );
	}

	/**
	 * Register the product_inquiry custom post type.
	 *
	 * This CPT is not publicly queryable and only visible to admins.
	 */
	public function register_cpt() {
		$labels = array(
			'name'               => _x( 'Product Inquiries', 'Post Type General Name', 'product-inquiry-for-woocommerce' ),
			'singular_name'      => _x( 'Product Inquiry', 'Post Type Singular Name', 'product-inquiry-for-woocommerce' ),
			'menu_name'          => __( 'Product Inquiries', 'product-inquiry-for-woocommerce' ),
			'name_admin_bar'     => __( 'Product Inquiry', 'product-inquiry-for-woocommerce' ),
			'archives'           => __( 'Inquiry Archives', 'product-inquiry-for-woocommerce' ),
			'attributes'         => __( 'Inquiry Attributes', 'product-inquiry-for-woocommerce' ),
			'all_items'          => __( 'All Inquiries', 'product-inquiry-for-woocommerce' ),
			'add_new_item'       => __( 'Add New Inquiry', 'product-inquiry-for-woocommerce' ),
			'add_new'            => __( 'Add New', 'product-inquiry-for-woocommerce' ),
			'new_item'           => __( 'New Inquiry', 'product-inquiry-for-woocommerce' ),
			'edit_item'          => __( 'View Inquiry', 'product-inquiry-for-woocommerce' ),
			'update_item'        => __( 'Update Inquiry', 'product-inquiry-for-woocommerce' ),
			'view_item'          => __( 'View Inquiry', 'product-inquiry-for-woocommerce' ),
			'view_items'         => __( 'View Inquiries', 'product-inquiry-for-woocommerce' ),
			'search_items'       => __( 'Search Inquiry', 'product-inquiry-for-woocommerce' ),
			'not_found'          => __( 'Not found', 'product-inquiry-for-woocommerce' ),
			'not_found_in_trash' => __( 'Not found in Trash', 'product-inquiry-for-woocommerce' ),
		);

		$args = array(
			'label'               => __( 'Product Inquiry', 'product-inquiry-for-woocommerce' ),
			'description'         => __( 'Product inquiry submissions', 'product-inquiry-for-woocommerce' ),
			'labels'              => $labels,
			'supports'            => array( 'title' ),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 56, // Below WooCommerce
			'menu_icon'           => 'dashicons-email-alt',
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => false,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'capability_type'     => 'post',
			'capabilities'        => array(
				'create_posts' => 'do_not_allow', // Removes "Add New" button
			),
			'map_meta_cap'        => true,
		);

		register_post_type( 'product_inquiry', $args );
	}
}
