<?php
/**
 * Disable the WordPress core Font Library UI.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! class_exists( 'OGF_Font_Library' ) ) :
	/**
	 * Hides WordPress core Font Library entry points when Fonts Plugin is active.
	 */
	class OGF_Font_Library {

		/**
		 * Class constructor.
		 */
		public function __construct() {
			add_filter( 'block_editor_settings_all', array( $this, 'disable_font_library_ui' ) );
			add_action( 'admin_menu', array( $this, 'remove_font_library_menu' ), 999 );
		}

		/**
		 * Disable Font Library controls in the block and site editors.
		 *
		 * @param array $editor_settings Block editor settings.
		 * @return array
		 */
		public function disable_font_library_ui( $editor_settings ) {
			$editor_settings['fontLibraryEnabled'] = false;

			return $editor_settings;
		}

		/**
		 * Remove the Appearance → Fonts admin menu item.
		 */
		public function remove_font_library_menu() {
			remove_submenu_page( 'themes.php', 'font-library.php' );
		}
	}
endif;

new OGF_Font_Library();
