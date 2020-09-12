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
	 * @var (Object) OGF_Fonts_Taxonomy
	 */
	private static $instance = null;

	/**
	 * Fonts
	 *
	 * @var (string) $fonts
	 */
	public static $fonts = null;

	/**
	 * Capability required for this menu to be displayed
	 *
	 * @var (string) $capability
	 */
	public static $capability = 'edit_theme_options';

	/**
	 * Register Taxonomy
	 *
	 * @var (string) $register_taxonomy
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
	 */
	protected static function default_args( $fonts ) {
		return wp_parse_args(
			$fonts,
			array(
				'woff'  => '',
				'woff2' => '',
				'ttf'   => '',
				'otf'   => '',
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
					self::$fonts[ $term->slug ]['id'] = $term->slug;
					self::$fonts[ $term->slug ]['label'] = $term->name;
					self::$fonts[ $term->slug ]['stack'] = $term->slug;
					self::$fonts[ $term->slug ]['files'] = self::get_font_links( $term->term_id );
				}
			}
		}
		return self::$fonts;
	}

	/**
	 * Get font data from name
	 *
	 * @param string $name custom font name.
	 * @return array $font_links custom font data.
	 */
	public static function get_links_by_name( $name ) {

		$terms = get_terms(
			self::$taxonomy_slug,
			array(
				'hide_empty' => false,
			)
		);

		$font_links = array();

		if ( ! empty( $terms ) ) {

			foreach ( $terms as $term ) {
				if ( $term->name == $name ) {
					$font_links[ $term->slug ] = self::get_font_links( $term->term_id );
				}
			}
		}

		return $font_links;

	}

	/**
	 * Get font links
	 *
	 * @param int $term_id custom font term id.
	 * @return array $links custom font data links.
	 */
	public static function get_font_links( $term_id ) {
		$links = get_option( 'taxonomy_' . self::$taxonomy_slug . "_{$term_id}", array() );
		return self::default_args( $links );
	}

	/**
	 * Update font data from name
	 *
	 * @param array $posted custom font data.
	 * @param int   $term_id custom font term id.
	 */
	public static function update_font_links( $posted, $term_id ) {

		$links = self::get_font_links( $term_id );
		foreach ( array_keys( $links ) as $key ) {
			if ( isset( $posted[ $key ] ) ) {
				$links[ $key ] = $posted[ $key ];
			} else {
				$links[ $key ] = '';
			}
		}
		update_option( 'taxonomy_' . self::$taxonomy_slug . "_{$term_id}", $links );
	}

}

OGF_Fonts_Taxonomy::get_instance();
