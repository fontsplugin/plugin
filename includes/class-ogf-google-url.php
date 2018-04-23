<?php
/**
 * Build the URL to load the chosen Google Fonts.
 *
 * @package     olympus-google-fonts
 * @copyright   Copyright (c) 2017, Danny Cooper
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * This class builds the Google Fonts URL.
 */
class OGF_Google_URL {

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
		$this->google_fonts = ogf_fonts_array();
	}

	/**
	 * Get the users font choices.
	 */
	public function get_choices() {

		$options = array(
			'ogf_body_font',
			'ogf_headings_font',
			'ogf_inputs_font',
			'ogf_site_title_font',
			'ogf_site_description_font',
			'ogf_navigation_font',
			'ogf_post_page_headings_font',
			'ogf_post_page_content_font',
			'ogf_sidebar_headings_font',
			'ogf_sidebar_content_font',
			'ogf_footer_headings_font',
			'ogf_footer_content_font',
		);

		foreach ( $options as $option ) {
			if ( get_theme_mod( $option ) && get_theme_mod( $option ) !== 'default' ) {
				$this->choices[] = get_theme_mod( $option );
			}
		}

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
		$subsets  = array();

		if ( ! empty( $this->choices ) ) {

			foreach ( array_unique( $this->choices ) as $font ) {

				// Check the users choice is a real font.
				if ( array_key_exists( $font, $this->google_fonts ) ) {

					$id = $this->get_font_id( $this->google_fonts[ $font ]['family'] );

					$families[] = $id . ':' . implode( ',', array_keys( $this->google_fonts[ $font ]['variants'] ) );

					$subsets_array = $this->google_fonts[ $font ]['subsets'];

					// Build an array of the subsets that need to be loaded.
					foreach ( $subsets_array as $subset ) {

						if ( ! in_array( $subset, $subsets, true ) ) {
							$subsets[] = $subset;
						}
					}
				}
			}

			$query_args = array(
				'family' => implode( '|', $families ),
				'subset' => implode( ',', $subsets ),
			);

			return add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );

		}

	}

}
