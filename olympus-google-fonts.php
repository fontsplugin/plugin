<?php
/**
 * Google Fonts for WordPress
 *
 * Plugin Name: Google Fonts for WordPress
 * Plugin URI:  https://wordpress.org/plugins/olympus-google-fonts/
 * Description: The easiest to use Google Fonts plugin. No coding required. 870+ font choices. Now includes Gutenberg Block!
 * Version:     1.4.5
 * Author:      Danny Cooper
 * Author URI:  https://fontsplugin.com
 * Text Domain: olympus-google-fonts
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2018, Danny Cooper
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

define( 'OGF_VERSION', '1.4.5' );
define( 'OGF_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'OGF_DIR_URL', plugin_dir_url( __FILE__ ) );

require OGF_DIR_PATH . 'class-olympus-google-fonts.php';
require OGF_DIR_PATH . 'blocks/init.php';

$gfwp = new Olympus_Google_Fonts();
