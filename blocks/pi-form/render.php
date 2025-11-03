<?php
/**
 * Server-side rendering for Product Inquiry Form block
 *
 * @package Product_Inquiry
 * @since   1.0.0
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product_id = isset( $attributes['productId'] ) ? absint( $attributes['productId'] ) : 0;
$show_title = isset( $attributes['showTitle'] ) ? (bool) $attributes['showTitle'] : true;

// Build shortcode
$shortcode = sprintf(
	'[product_inquiry_form product_id="%d" show_title="%s"]',
	$product_id,
	$show_title ? 'true' : 'false'
);

// Get wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'pi-inquiry-form-block',
	)
);

// Render block
printf(
	'<div %s>%s</div>',
	$wrapper_attributes,
	do_shortcode( $shortcode )
);
