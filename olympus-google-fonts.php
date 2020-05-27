<?php
/**
 * Google Fonts Typography
 *
 * Plugin Name: Google Fonts Typography
 * Plugin URI:  https://wordpress.org/plugins/olympus-google-fonts/
 * Description: The easiest to use Google Fonts typography plugin. No coding required. 900+ font choices.
 * Version:     2.0.5
 * Author:      Fonts Plugin
 * Author URI:  https://fontsplugin.com/?utm_source=wporg&utm_campaign=heading
 * Text Domain: olympus-google-fonts
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

define( 'OGF_VERSION', '2.0.5' );
define( 'OGF_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'OGF_DIR_URL', plugin_dir_url( __FILE__ ) );

require OGF_DIR_PATH . 'class-olympus-google-fonts.php';
require OGF_DIR_PATH . 'blocks/init.php';

$gfwp = new Olympus_Google_Fonts();

$current_theme      = wp_get_theme();
$theme_author       = strtolower( esc_attr( $current_theme->get( 'Author' ) ) );
$theme_author       = str_replace( ' ', '', $theme_author );
$author_compat_path = OGF_DIR_PATH . '/compatibility/' . $theme_author . '.php';
if ( file_exists( $author_compat_path ) ) {
	require $author_compat_path;
}
