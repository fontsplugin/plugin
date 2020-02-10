<?php
/**
 * Add Google Fonts dropdown to the classic editor.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! class_exists( 'OGF_Classic_Editor' ) ) :
	/**
	 * The 'Classic Editor' class.
	 */
	class OGF_Classic_Editor {

		/**
		 * WP_Customize object.
		 *
		 * @var WP_Customize_Manager
		 */
		private $wp_customize;


		/**
		 * OGF_Fonts object.
		 *
		 * @var object
		 */
		private $ogf_fonts;

		/**
		 * Class constructor.
		 */
		public function __construct() {
			$this->ogf_fonts = new OGF_Fonts();
			if ( ! $this->ogf_fonts->has_custom_fonts() ) {
				return;
			}
			add_filter( 'mce_buttons', array( $this, 'tinymce_add_buttons' ), 1 );
			add_filter( 'tiny_mce_before_init', array( $this, 'tinymce_custom_options' ) );
			add_filter( 'ogf_classic_font_formats', array( $this, 'tinymce_add_fonts' ) );
			add_action( 'admin_init', array( $this, 'google_fonts_enqueue' ) );
		}

		/**
		 * Add buttons to the editor.
		 *
		 * @param array $buttons Tiny MCE buttons.
		 */
		public function tinymce_add_buttons( $buttons ) {
			return array_merge(
				array( 'fontselect', 'fontsizeselect' ),
				$buttons
			);
		}

		/**
		 * Customize the Tiny MCE settings.
		 *
		 * @param array $opt Tiny MCE options.
		 */
		public function tinymce_custom_options( $opt ) {

				$opt['font_formats'] = apply_filters( 'ogf_classic_font_formats', 'Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Book Antiqua=book antiqua,palatino;Comic Sans MS=comic sans ms,sans-serif;Courier New=courier new,courier;Georgia=georgia,palatino;Helvetica=helvetica;Impact=impact,chicago;Symbol=symbol;Tahoma=tahoma,arial,helvetica,sans-serif;Terminal=terminal,monaco;Times New Roman=times new roman,times;Trebuchet MS=trebuchet ms,geneva;Verdana=verdana,geneva;' );

				return $opt;
		}

		/**
		 * Add fonts to the classic editor list.
		 *
		 * @param array $old_default The default fonts.
		 */
		public function tinymce_add_fonts( $old_default ) {
			$new_default = '';
			$choices     = $this->ogf_fonts->choices;
			foreach ( array_unique( $choices ) as $font ) {
				if ( ! ogf_is_system_font( $font ) ) {
					$new_default .= $this->ogf_fonts->get_font_name( $font ) . '=' . $this->ogf_fonts->get_font_name( $font ) . ';';
				}
			}
			$new_default .= $old_default;
			return $new_default;
		}

		/**
		 * Enqueue the Google Fonts in TinyMCE.
		 */
		public function google_fonts_enqueue() {
			global $editor_styles;
			if ( $this->ogf_fonts->has_custom_fonts() ) {
				$editor_styles[] = $this->ogf_fonts->build_url();
			}
		}

	}
endif;

/*
 * Instantiate the OGF_Classic_Editor class.
 */
new OGF_Classic_Editor();
