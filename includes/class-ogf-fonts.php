<?php
/**
 * Build the URL to load the chosen Google Fonts.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * This class builds the Google Fonts URL.
 */
class OGF_Fonts {

	/**
	 * All Google Fonts.
	 *
	 * @var array
	 */
	public static $google_fonts = array();

	/**
	 * The users font choices.
	 *
	 * @var array
	 */
	public $choices = array();

	/**
	 * Let's get started.
	 */
	public function __construct() {
		self::$google_fonts = ogf_fonts_array();
		$this->get_choices();
	}

	/**
	 * Get the users font choices.
	 */
	public function get_choices() {
		$elements = array_keys( ogf_get_elements() );

		foreach ( $elements as $element ) {
			if ( get_theme_mod( $element . '_font' ) && get_theme_mod( $element . '_font' ) !== 'default' ) {
				$this->choices[] = get_theme_mod( $element . '_font' );
			}
		}

		$elements = array_keys( ogf_get_custom_elements() );

		foreach ( $elements as $element ) {
			if ( get_theme_mod( $element . '_font' ) && get_theme_mod( $element . '_font' ) !== 'default' ) {
				$this->choices[] = get_theme_mod( $element . '_font' );
			}
		}

		$load_fonts_css = get_theme_mod( 'ogf_load_fonts', array() );

		if ( is_array( $load_fonts_css ) ) {
			foreach ( $load_fonts_css as $key => $value ) {
				$this->choices[] = $value;
			}
		}
	}

	/**
	 * Make the font name safe for use in URLs
	 *
	 * @param string $font The font we are getting the id of.
	 */
	public function get_font_id( $font ) {
		return str_replace( ' ', '+', $font );
	}

	/**
	 * Get the font weights from ID.
	 *
	 * @param string $font_id The font ID.
	 */
	public function get_font_weights( $font_id ) {
		$weights = self::$google_fonts[ $font_id ]['v'];

		if ( ! is_array( $weights ) ) {
			return array();
		}

		unset( $weights['0'] );

		foreach ( $weights as $key => $value ) {
			$weights[ $key . 'i' ] = $value . ' Italic';
		}

		return $weights;
	}

	/**
	 * Get the font name from ID.
	 *
	 * @param string $font_id The font ID.
	 */
	public function get_font_name( $font_id ) {
		if ( array_key_exists( $font_id, self::$google_fonts ) ) {
			return self::$google_fonts[ $font_id ]['f'];
		} else {
			return __( 'Font Missing', 'olympus-google-fonts' );
		}
	}

	/**
	 * DEPRECATED use has_google_fonts() instead.
	 */
	public function has_custom_fonts() {
		return $this->has_google_fonts();
	}

	/**
	 * Helper to check if the user is using any Google fonts.
	 */
	public function has_google_fonts() {

		if ( empty( $this->choices ) ) {
			return false;
		}

		foreach ( $this->choices as $choice ) {
			if ( ! ogf_is_system_font( $choice ) && ! ogf_is_custom_font( $choice ) && ! ogf_is_typekit_font( $choice ) ) {
				return true;
			}
		}

		return false;

	}

	/**
	 * Remove the fonts the user has chosen not to load.
	 *
	 * @param string $font_id The font ID.
	 * @param string $weights The font weights.
	 */
	public function filter_selected_weights( $font_id, $weights ) {
		unset( $weights['0'] );

		foreach ( $weights as $key => $value ) {
			$weights[ $key . 'i' ] = $value . ' Italic';
		}

		$selected_weights = get_theme_mod( $font_id . '_weights', false );

		if ( ! $selected_weights ) {
			return $weights;
		}
		return array_intersect_key( $weights, array_flip( $selected_weights ) );
	}

	/**
	 * Return the Google Fonts url.
	 */
	public function build_url() {
		$families = array();
		$subsets  = array();

		if ( empty( $this->choices ) ) {
			return;
		}

		$fonts = array_unique( $this->choices );

		foreach ( $fonts as $font_id ) {

			// Check the users choice is a real font.
			if ( array_key_exists( $font_id, self::$google_fonts ) ) {

				$font_id_for_url = $this->get_font_id( self::$google_fonts[ $font_id ]['f'] );

				$weights = $this->filter_selected_weights( $font_id, self::$google_fonts[ $font_id ]['v'] );

				$families[] = $font_id_for_url . ':' . implode( ',', array_keys( $weights ) );

			}
		}

		$query_args = array(
			'family'  => implode( '|', $families ),
			'display' => get_theme_mod( 'ogf_font_display', 'swap' ),
		);

		return add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );
	}

}
