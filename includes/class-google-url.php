<?php
/**
 * Build the URL to load the chosen Google Fonts.
 *
 * @package     google-fonts-wp
 * @copyright   Copyright (c) 2017, Danny Cooper
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * This class builds the Google Fonts URL.
 */
class GFWP_Google_URL {

	/**
	 * All Google Fonts start with this URL
	 *
	 * @var string
	 */
	public $url_base = 'https://fonts.googleapis.com/css?family=';

	/**
	 * All Google Fonts start with this URL
	 *
	 * @var array
	 */
	public $google_fonts = array();

	/**
	 * All Google Fonts start with this URL
	 *
	 * @var array
	 */
	public $choices = array();

	/**
	 * Let's get started.
	 */
	public function __construct() {
		$this->get_fonts();
		$this->get_choices();
	}

	/**
	 * Load the Google fonts array.
	 */
	public function get_fonts() {
		$this->google_fonts = gfwp_fonts_array();
	}

	/**
	 * Get the users font choices.
	 */
	public function get_choices() {
		$this->choices[] = get_theme_mod( 'gfwp_body_font' );
		$this->choices[] = get_theme_mod( 'gfwp_headings_font' );
		$this->choices[] = get_theme_mod( 'gfwp_inputs_font' );

		// Remove the defaults.
		unset( $this->choices['default'] );

	}

	/**
	 * Helper to check if the user is using any Google fonts.
	 */
	public function has_custom_fonts() {

		if ( ! empty( $this->choices ) ) {
			return true;
		} else {
			return false;
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
	 * Return the Google Fonts url.
	 */
	public function build() {

		$families = array();
		$subsets = array();

		if ( ! empty( $this->choices ) ) {

			foreach ( $this->choices as $font ) {

				// Check the users choice is a real font.
				if ( array_key_exists( $font, $this->google_fonts ) ) {

					$id = $this->get_font_id( $this->google_fonts[ $font ]['family'] );

					$families[] = $id . ':' . implode( ',', $this->google_fonts[ $font ]['variants'] );

					$subsets_array = $this->google_fonts[ $font ]['subsets'];

					// Build an array of the subsets that need to be loaded.
					foreach ( $subsets_array as $subset ) {

						if ( ! in_array( $subset, $subsets, true ) ) {
							$subsets[] = $subset;
						}
					}
				}
			}

			$families_output = implode( '|', $families );
			$subset_output = implode( ',', $subsets );

			return $this->url_base . $families_output . '&amp;' . $subset_output;

		}

	}

}
