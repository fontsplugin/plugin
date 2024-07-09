<?php
/**
 * Compatibility file for WooCommerce.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Modify the default element selectors to improve compatibility with WooCommerce.
 *
 * @param array $elements The default elements.
 */
function ogf_woocommerce_controls( $elements ) {
	$new = array(
		'ogc_wc_single_title'             => array(
			'label'       => esc_html__( 'Product Title', 'olympus-google-fonts' ),
			'description' => '',
			'section'     => 'ogf_wc_single',
			'selectors'   => '.product_title',
		),
		'ogc_wc_single_short_desc'        => array(
			'label'       => esc_html__( 'Short Description', 'olympus-google-fonts' ),
			'description' => '',
			'section'     => 'ogf_wc_single',
			'selectors'   => '.woocommerce-product-details__short-description',
		),
		'ogc_wc_single_price'             => array(
			'label'       => esc_html__( 'Price', 'olympus-google-fonts' ),
			'description' => '',
			'section'     => 'ogf_wc_single',
			'selectors'   => '.single-product .price',
		),
		'ogc_wc_single_price_add_to_cart' => array(
			'label'       => esc_html__( 'Add to Cart Button', 'olympus-google-fonts' ),
			'description' => '',
			'section'     => 'ogf_wc_single',
			'selectors'   => '.single_add_to_cart_button',
		),
		'ogc_wc_single_tab_headings'      => array(
			'label'       => esc_html__( 'Tab Headings', 'olympus-google-fonts' ),
			'description' => '',
			'section'     => 'ogf_wc_single',
			'selectors'   => '.wc-tab h2',
		),
		'ogc_wc_single_tab_content'       => array(
			'label'       => esc_html__( 'Tab Content', 'olympus-google-fonts' ),
			'description' => '',
			'section'     => 'ogf_wc_single',
			'selectors'   => '.wc-tab p',
		),
		'ogf_wc_checkout_button'          => array(
			'label'       => esc_html__( 'Proceed to Checkout Button', 'olympus-google-fonts' ),
			'description' => '',
			'section'     => 'ogf_wc_cart',
			'selectors'   => '.checkout-button',
		),
		'ogf_wc_field_labels'             => array(
			'label'       => esc_html__( 'Field Labels', 'olympus-google-fonts' ),
			'description' => '',
			'section'     => 'ogf_wc_checkout',
			'selectors'   => '.woocommerce .form-row label',
		),
		'ogf_wc_order_button'             => array(
			'label'       => esc_html__( 'Place Order Button', 'olympus-google-fonts' ),
			'description' => '',
			'section'     => 'ogf_wc_checkout',
			'selectors'   => '#place_order',
		),
		'ogf_wc_block_name'               => array(
			'label'       => esc_html__( 'Product Title', 'olympus-google-fonts' ),
			'description' => '',
			'section'     => 'ogf_wc_block',
			'selectors'   => '.wc-block-grid__products .wc-block-grid__product .wc-block-components-product-name',
		),
		'ogf_wc_block_price'              => array(
			'label'       => esc_html__( 'Product Price', 'olympus-google-fonts' ),
			'description' => '',
			'section'     => 'ogf_wc_block',
			'selectors'   => '.wc-block-grid__products .wc-block-grid__product .wc-block-grid__product-price',
		),
		'ogf_wc_block_button'             => array(
			'label'       => esc_html__( 'Add to Cart Button', 'olympus-google-fonts' ),
			'description' => '',
			'section'     => 'ogf_wc_block',
			'selectors'   => '.wc-block-grid__products .wc-block-grid__product .add_to_cart_button',
		),
		'ogf_wc_shop_name'                => array(
			'label'       => esc_html__( 'Product Title', 'olympus-google-fonts' ),
			'description' => '',
			'section'     => 'ogf_wc_shop',
			'selectors'   => 'ul.products li.product .woocommerce-loop-product__title',
		),
		'ogf_wc_shop_price'               => array(
			'label'       => esc_html__( 'Product Price', 'olympus-google-fonts' ),
			'description' => '',
			'section'     => 'ogf_wc_shop',
			'selectors'   => 'ul.products li.product .price',
		),
		'ogf_wc_shop_button'              => array(
			'label'       => esc_html__( 'Add to Cart Button', 'olympus-google-fonts' ),
			'description' => '',
			'section'     => 'ogf_wc_shop',
			'selectors'   => 'ul.products li.product .button',
		),
	);

	return array_merge( $elements, $new );
}
add_filter( 'ogf_elements', 'ogf_woocommerce_controls' );
