<?php
/**
 * Helper functions for the Elementor plugin.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Create a new group for fonts added by Fonts Plugin.
 */
add_filter( 'elementor/fonts/groups', function($groups) {
    return array_merge(['fontsplugin' => 'Fonts Plugin'], $groups);
}, 10, 1 );

/**
 * Add fonts from Typekit and uploaded fonts to the Elementor dropdown.
 */
add_filter( 'elementor/fonts/additional_fonts', function($fonts) {
    $custom_fonts = ogf_custom_fonts_unique();
    
    foreach( $custom_fonts as $key => $value ) {
        $fonts[$key] = 'fontsplugin';
    }

    $typekit_fonts = ogf_typekit_fonts();

    foreach( $typekit_fonts as $key => $value ) {
        $fonts[$value['id']] = 'fontsplugin';
    }

    return $fonts;
}, 10, 1 );