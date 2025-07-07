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
		 * OGF_Fonts object.
		 *
		 * @var object
		 */
		private $ogf_fonts;

		/**
		 * Array of system fonts.
		 *
		 * @var array
		 */
		private $system_fonts;

		/**
		 * Array of custom fonts.
		 *
		 * @var array
		 */
		private $custom_fonts;

		/**
		 * Array of typekit fonts.
		 *
		 * @var array
		 */
		private $typekit_fonts;

		/**
		 * Class constructor.
		 */
		public function __construct() {
			if ( true === get_theme_mod( 'ogf_disable_post_level_controls', false ) ) {
				return;
			}

			$this->ogf_fonts     = OGF_Fonts::get_instance();
			$this->system_fonts  = ogf_system_fonts();
			$this->custom_fonts  = ogf_custom_fonts();
			$this->typekit_fonts = ogf_typekit_fonts();

			add_filter( 'tiny_mce_before_init', array( $this, 'add_font_sizes' ) );
			add_filter( 'mce_buttons', array( $this, 'tinymce_add_buttons' ), 1 );
			add_filter( 'tiny_mce_before_init', array( $this, 'tinymce_custom_options' ), 100 );
			add_filter( 'ogf_classic_font_formats', array( $this, 'tinymce_add_fonts' ), 100 );
			add_action( 'admin_init', array( $this, 'google_fonts_enqueue' ) );
			add_action( 'admin_init', array( $this, 'typekit_fonts_enqueue' ) );
		}

		/**
		 * Add Formats to TinyMCE
		 * - https://developer.wordpress.org/reference/hooks/tiny_mce_before_init/
		 * - https://codex.wordpress.org/Plugin_API/Filter_Reference/tiny_mce_before_init
		 *
		 * @param array $args   - Arguments used to initialize the tinyMCE
		 *
		 * @return array $args  - Modified arguments
		 */
		function add_font_sizes( $args ) {
			if ( true === get_theme_mod( 'ogf_use_px', true ) ) {
				$args['fontsize_formats'] = '6px 7px 8px 9px 10px 11px 12px 13px 14px 15px 16px 17px 18px 19px 20px 21px 22px 23px 24px 25px 26px 27px 28px 29px 30px 31px 32px 33px 34px 35px 36px 37px 38px 39px 40px 41px 42px 43px 44px 45px 46px 47px 48px 49px 50px 51px 52px 53px 55px 55px 56px 57px 58px 59px 60px 61px 62px 63px 66px 65px 66px 67px 68px 69px 70px 71px 72px 73px 77px 75px 76px 77px 78px 79px 80px';
			}

			return apply_filters( 'ogf_classic_font_sizes', $args );
		}

		/**
		 * Add buttons to the editor.
		 *
		 * @param array $buttons Tiny MCE buttons array.
		 * @return array Modified Tiny MCE buttons array.
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
		 * @return array Modified Tiny MCE options.
		 */
		public function tinymce_custom_options( $opt ) {
			$base_type             = get_theme_mod( 'ogf_body_font' );
			$base_font_size        = get_theme_mod( 'ogf_body_font_size', false );
			$base_font_weight      = get_theme_mod( 'ogf_body_font_weight', false );
			$base_font_line_height = get_theme_mod( 'ogf_body_line_height', false );

			$content_type             = get_theme_mod( 'ogf_post_page_content_font' );
			$content_font_size        = get_theme_mod( 'ogf_post_page_content_font_size', false );
			$content_font_weight      = get_theme_mod( 'ogf_post_page_content_font_weight', false );
			$content_font_line_height = get_theme_mod( 'ogf_post_page_content_line_height', false );

			$headings_type        = get_theme_mod( 'ogf_headings_font' );
			$headings_font_weight = get_theme_mod( 'ogf_headings_font_weight', false );

			if ( ogf_is_custom_font( $base_type ) ) {
				$name      = str_replace( 'cf-', '', $base_type );
				$font      = OGF_Fonts_Taxonomy::get_by_name( $name );
				$base_type = ! empty( $font['family'] ) ? $font['family'] : $name;
			} elseif ( ogf_is_system_font( $base_type ) ) {
				$base_type = str_replace( 'sf-', '', $base_type );
				$base_type = $this->typekit_fonts[ $base_type ]['stack'] ?? $base_type;
			} elseif ( ogf_is_google_font( $base_type ) ) {
				$base_type = $this->ogf_fonts->get_font_name( $base_type );
			} elseif ( ogf_is_typekit_font( $base_type ) ) {
				$base_type = $this->typekit_fonts[ $base_type ]['stack'] ?? $base_type;
			}

			if ( ogf_is_custom_font( $content_type ) ) {
				$name         = str_replace( 'cf-', '', $content_type );
				$font         = OGF_Fonts_Taxonomy::get_by_name( $name );
				$content_type = ! empty( $font['family'] ) ? $font['family'] : $name;
			} elseif ( ogf_is_system_font( $content_type ) ) {
				$content_type = str_replace( 'sf-', '', $content_type );
				$content_type = $this->typekit_fonts[ $content_type ]['stack'] ?? $content_type;
			} elseif ( ogf_is_google_font( $content_type ) ) {
				$content_type = $this->ogf_fonts->get_font_name( $content_type );
			} elseif ( ogf_is_typekit_font( $content_type ) ) {
				$content_type = $this->typekit_fonts[ $content_type ]['stack'] ?? $content_type;
			}

			if ( ogf_is_custom_font( $headings_type ) ) {
				$name          = str_replace( 'cf-', '', $headings_type );
				$font          = OGF_Fonts_Taxonomy::get_by_name( $name );
				$headings_type = ! empty( $font['family'] ) ? $font['family'] : $name;
			} elseif ( ogf_is_system_font( $headings_type ) ) {
				$headings_type = str_replace( 'sf-', '', $headings_type );
				$headings_type = $this->system_fonts[ $headings_type ]['stack'] ?? $headings_type;
			} elseif ( ogf_is_google_font( $headings_type ) ) {
				$headings_type = $this->ogf_fonts->get_font_name( $headings_type );
			} elseif ( ogf_is_typekit_font( $headings_type ) ) {
				$headings_type = $this->typekit_fonts[ $headings_type ]['label'] ?? $headings_type;
			}

			$opt['font_formats'] = apply_filters( 'ogf_classic_font_formats', 'Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Book Antiqua=book antiqua,palatino;Comic Sans MS=comic sans ms,sans-serif;Courier New=courier new,courier;Georgia=georgia,palatino;Helvetica=helvetica;Impact=impact,chicago;Symbol=symbol;Tahoma=tahoma,arial,helvetica,sans-serif;Terminal=terminal,monaco;Times New Roman=times new roman,times;Trebuchet MS=trebuchet ms,geneva;Verdana=verdana,geneva;' );

			if ( ! isset( $opt['content_style'] ) ) {
				$opt['content_style'] = '';
			}

			$opt['content_style'] .= 'body#tinymce, body#tinymce p { ';

			if ( $base_type !== 'default' ) {
				$opt['content_style'] .= ' font-family: ' . str_replace( '"', '\'', $base_type ) . ' !important;';
			}
			if ( $base_font_size ) {
				$opt['content_style'] .= ' font-size: ' . $base_font_size . 'px !important;';
			}
			if ( $base_font_weight ) {
				$opt['content_style'] .= ' font-weight: ' . $base_font_weight . ' !important;';
			}
			if ( $base_font_line_height ) {
				$opt['content_style'] .= ' line-height: ' . $base_font_line_height . ' !important;';
			}

			if ( $content_type !== 'default' ) {
				$opt['content_style'] .= ' font-family: ' . str_replace( '"', '\'', $content_type ) . ' !important;';
			}
			if ( $content_font_size ) {
				$opt['content_style'] .= ' font-size: ' . $content_font_size . 'px !important;';
			}
			if ( $content_font_weight ) {
				$opt['content_style'] .= ' font-weight: ' . $content_font_weight . ' !important;';
			}
			if ( $content_font_line_height ) {
				$opt['content_style'] .= ' line-height: ' . $content_font_line_height . ' !important;';
			}

			$opt['content_style'] .= '}';

			$opt['content_style'] .= '#tinymce h1, #tinymce h2, #tinymce h3, #tinymce h4, #tinymce h5, #tinymce h6 {';

			if ( $headings_type !== 'default' ) {
				$opt['content_style'] .= ' font-family: ' . str_replace( '"', '\'', $headings_type ) . ' !important;';
			}
			if ( $headings_font_weight ) {
				$opt['content_style'] .= ' font-weight: ' . $headings_font_weight . ' !important;';
			}

			$opt['content_style'] .= '}';

			$opt['content_style'] .= ogf_return_custom_font_css();
			return $opt;
		}

		/**
		 * Add fonts to the classic editor list.
		 *
		 * @param string $old_default The default fonts.
		 * @return string Modified fonts string.
		 */
		public function tinymce_add_fonts( $old_default ) {
			$new_default = '';
			$choices     = $this->ogf_fonts->choices;
			foreach ( array_unique( $choices ) as $font ) {
				if ( ogf_is_google_font( $font ) ) {
					$new_default .= $this->ogf_fonts->get_font_name( $font ) . '=' . $this->ogf_fonts->get_font_name( $font ) . ';';
				}
			}

			foreach ( $this->custom_fonts as $font ) {
				$stack = ! empty( $font['family'] ) ? $font['family'] : $font['stack'];

				$new_default .= $font['label'] . '=' . $stack . ';';
			}

			foreach ( $this->typekit_fonts as $font ) {
				$new_default .= $font['label'] . '=' . str_replace( '"', '', $font['stack'] ) . ';';
			}

			$new_default .= $old_default;
			return $new_default;
		}

		/**
		 * Enqueue the Google Fonts in TinyMCE.
		 */
		public function google_fonts_enqueue() {
			global $editor_styles;
			if ( $this->ogf_fonts->has_google_fonts() ) {
				$editor_styles[] = $this->ogf_fonts->build_url();
			}
		}

		/**
		 * Enqueue the Typekit Fonts in TinyMCE.
		 */
		public function typekit_fonts_enqueue() {
			global $editor_styles;

			$typekit_data = get_option( 'fp-typekit-data', array() );

			if ( is_array( $typekit_data ) ) {
				foreach ( $typekit_data as $id => $values ) {
					// skip if the kit is disabled.
					if ( $values['enabled'] === false ) {
						continue;
					}

					$editor_styles[] = esc_url( 'https://use.typekit.com/' . $id . '.css' );
				}
			}
		}
	}
endif;

/*
 * Instantiate the OGF_Classic_Editor class.
 */
new OGF_Classic_Editor();
