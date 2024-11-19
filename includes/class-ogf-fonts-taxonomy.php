<?php
/**
 * Custom Fonts Upload Taxonomy
 *
 * @package olympus-google-fonts
 */

/**
 * OGF_Fonts_Taxonomy
 */
class OGF_Fonts_Taxonomy {
	/**
	 * Instance of OGF_Fonts_Taxonomy
	 *
	 * @var object OGF_Fonts_Taxonomy
	 */
	private static $instance = null;

	/**
	 * Fonts
	 *
	 * @var array $fonts
	 */
	public static $fonts = null;

	/**
	 * Capability required for this menu to be displayed
	 *
	 * @var string $capability
	 */
	public static $capability = 'edit_theme_options';

	/**
	 * Register Taxonomy
	 *
	 * @var string $register_taxonomy
	 */
	public static $taxonomy_slug = 'ogf_custom_fonts';

	/**
	 * Instance of OGF_Fonts_Taxonomy.
	 *
	 * @return object Class object.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->register_fonts_taxonomy();
	}

	/**
	 * Register custom font taxonomy
	 */
	public function register_fonts_taxonomy() {
		$labels = array(
			'name'              => __( 'Custom Fonts', 'olympus-google-fonts' ),
			'singular_name'     => __( 'Font', 'olympus-google-fonts' ),
			'menu_name'         => _x( 'Custom Fonts', 'Admin menu name', 'olympus-google-fonts' ),
			'search_items'      => __( 'Search Fonts', 'olympus-google-fonts' ),
			'all_items'         => __( 'All Fonts', 'olympus-google-fonts' ),
			'parent_item'       => __( 'Parent Font', 'olympus-google-fonts' ),
			'parent_item_colon' => __( 'Parent Font:', 'olympus-google-fonts' ),
			'edit_item'         => __( 'Edit Font', 'olympus-google-fonts' ),
			'update_item'       => __( 'Update Font', 'olympus-google-fonts' ),
			'add_new_item'      => __( 'Add New Font', 'olympus-google-fonts' ),
			'new_item_name'     => __( 'New Font Name', 'olympus-google-fonts' ),
			'not_found'         => __( 'No fonts found', 'olympus-google-fonts' ),
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'public'            => false,
			'show_in_nav_menus' => false,
			'show_ui'           => true,
			'capabilities'      => array( self::$capability ),
			'query_var'         => false,
			'rewrite'           => false,
		);

		register_taxonomy(
			self::$taxonomy_slug,
			array(),
			$args
		);
	}

	/**
	 * Default fonts
	 *
	 * @param array $fonts fonts array of fonts.
	 * @return array
	 */
	protected static function default_args( $fonts ) {
		return wp_parse_args(
			$fonts,
			array(
				'woff'    => '',
				'woff2'   => '',
				'ttf'     => '',
				'otf'     => '',
				'weight'  => '',
				'style'   => '',
				'family'  => '',
				'preload' => '1',
			)
		);
	}

	/**
	 * Get fonts
	 *
	 * @return array $fonts fonts array of fonts.
	 */
	public static function get_fonts() {

		if ( is_null( self::$fonts ) ) {
			self::$fonts = array();

			$terms = get_terms(
				self::$taxonomy_slug,
				array(
					'hide_empty' => false,
				)
			);

			if ( ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					self::$fonts[ $term->slug ]['id']    = $term->slug;
					self::$fonts[ $term->slug ]['label'] = $term->name;
					self::$fonts[ $term->slug ]['stack'] = $term->slug;
					self::$fonts[ $term->slug ]['files'] = self::get_font_data( $term->term_id );
					self::$fonts[ $term->slug ]['family'] = self::$fonts[ $term->slug ]['files']['family'];

				}
			}
		}
		return self::$fonts;
	}

	/**
	 * Get font data from name
	 *
	 * @param string $name custom font name.
	 * @return array $font_data custom font data.
	 */
	public static function get_by_name( $name ) {

		$term = get_term_by('slug', $name, self::$taxonomy_slug);
		if ( ! $term ) {
			return false;
		}

		$font_data = self::get_font_data( $term->term_id );

		return $font_data;
	}

	/**
	 * Get font links
	 *
	 * @param int $term_id custom font term id.
	 * @return array $links custom font data links.
	 */
	public static function get_font_data( $term_id ) {
		$data = get_option( 'taxonomy_' . self::$taxonomy_slug . "_{$term_id}", array() );
		return self::default_args( $data );
	}

	/**
	 * Update font data from name
	 *
	 * @param array $posted custom font data.
	 * @param int   $term_id custom font term id.
	 */
	public static function update_font_data( $posted, $term_id ) {
		$data = self::get_font_data( $term_id );
		foreach ( array_keys( $data ) as $key ) {
			if ( isset( $posted[ $key ] ) ) {
				$data[ $key ] = $posted[ $key ];
			} else {
				$data[ $key ] = '';
			}
		}
		update_option( 'taxonomy_' . self::$taxonomy_slug . "_{$term_id}", $data );
	}
}

OGF_Fonts_Taxonomy::get_instance();
