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

		return $weights;
	}

	/**
	 * Get the font subsets from ID.
	 *
	 * @param string $font_id The font ID.
	 */
	public function get_font_subsets( $font_id ) {
		$subsets = self::$google_fonts[ $font_id ]['s'];

		if ( ! is_array( $subsets ) ) {
			return array();
		}

		// We need both the key and value to be the subset name.
		$combined = array_combine( $subsets, $subsets );
		unset($combined['latin']);
		return $combined;
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
			if ( ogf_is_google_font( $choice ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Remove the font variants the user has chosen not to load.
	 *
	 * @param string $font_id The font ID.
	 * @param array  $weights The font weights.
	 * @return array
	 */
	public function filter_selected_weights( $font_id, $weights ) {
		unset( $weights['0'] );

		$selected_weights = get_theme_mod( $font_id . '_weights', false );

		if ( ! $selected_weights ) {
			return $weights;
		}

		return array_intersect_key( $weights, array_flip( $selected_weights ) );
	}

	/**
	 * Get contents from remote URL.
	 *
	 * @param string $url The Google Fonts URL.
	 *
	 * @return string
	 */
	public function get_remote_url_contents( $url ) {
		$user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:108.0) Gecko/20100101 Firefox/108.0';

		// Get the response.
		$response = wp_remote_get( $url, array( 'user-agent' => $user_agent ) );

		// Early exit if there was an error.
		if ( is_wp_error( $response ) ) {
			return '';
		}
		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return '';
		}

		// Get the CSS from our response.
		$contents = wp_remote_retrieve_body( $response );

		return $contents;
	}

	/**
	 * Store CSS from URL provided.
	 *
	 * @param string $url The Google Fonts URL.
	 *
	 * @return string
	 */
	public function stored_css( $url ) {
		$url_to_id         = md5( $url );
		$external_font_css = get_transient( 'ogf_external_font_css_' . $url_to_id );

		if ( false === ( $external_font_css ) ) {
			// It wasn't there, so regenerate the data and save the transient.
			$external_font_css = '/* Cached: ' . date( 'F j, Y \a\t g:ia' ) . ' */' . PHP_EOL;
			$external_font_css .= $this->get_remote_url_contents( $url ) . PHP_EOL;
			set_transient( 'ogf_external_font_css_' . $url_to_id, $external_font_css, DAY_IN_SECONDS );
		}

		return $external_font_css;
	}

	/**
	 * Return the Google Fonts URL.
	 *
	 * @return false|string
	 */
	public function build_url() {
		$families = array();

		if ( empty( $this->choices ) ) {
			return false;
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
