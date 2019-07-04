<?php
/**
 * Add Google Fonts dropdown to the classic editor.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2019, Fonts Plugin
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
		 * Class constructor.
		 */
		public function __construct() {
			add_filter( 'mce_buttons', array( $this, 'tinymce_add_buttons' ), 1 );
			add_filter( 'tiny_mce_before_init', array( $this, 'tinymce_custom_options' ) );
		}

		/**
		 * Add buttons to the editor.
		 */
		public function tinymce_add_buttons( $buttons ) {
			return array_merge(
				array( 'fontselect', 'fontsizeselect' ),
				$buttons
			);
		}

		/**
		 * Store a reference to `WP_Customize_Manager` instance
		 *
		 * @param Object $opt Global $wp_customize object.
		 */
		public function tinymce_custom_options( $opt ) {


				$opt['font_formats'] = 'Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Book Antiqua=book antiqua,palatino;Comic Sans MS=comic sans ms,sans-serif;Courier New=courier new,courier;Georgia=georgia,palatino;Helvetica=helvetica;Impact=impact,chicago;Symbol=symbol;Tahoma=tahoma,arial,helvetica,sans-serif;Terminal=terminal,monaco;Times New Roman=times new roman,times;Trebuchet MS=trebuchet ms,geneva;Verdana=verdana,geneva;Webdings=webdings;Wingdings=wingdings,zapf dingbats';

				$opt['fontsize_formats'] = '8px 10px 12px 14px 16px 18px 24px 30px 36px 48px 60px 72px';

				return $opt;
		}

	}
endif;

/*
 * Instantiate the OGF_Classic_Editor class.
 */
new OGF_Classic_Editor();
