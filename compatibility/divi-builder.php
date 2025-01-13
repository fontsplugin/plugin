<?php
/**
 * Helper functions for the Divi Builder plugin.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Add fonts from Typekit and uploaded fonts to the Divi Builder dropdown.
 */
add_filter( 'et_websafe_fonts', function($fonts) {
    $websafe_fonts = array();

    $typekit_fonts = ogf_typekit_fonts();

    foreach( $typekit_fonts as $key => $value ) {
        $websafe_fonts[$value['id']] = array(
            'styles'        => '100,200,300,400,500,600,700,800,900,100italic,200italic,300italic,400italic,500italic,600italic,700italic,800italic,900italic',
            'character_set' => 'cyrillic,latin',
            'type'          => 'sans-serif',
        );
    }

    $custom_fonts = ogf_custom_fonts();
    
    foreach( $custom_fonts as $key => $value ) {
        $websafe_fonts[$key] = array(
            'styles'        => '100,200,300,400,500,600,700,800,900,100italic,200italic,300italic,400italic,500italic,600italic,700italic,800italic,900italic',
            'character_set' => 'cyrillic,latin',
            'type'          => 'sans-serif',
        );
    }

    return array_merge($websafe_fonts, $fonts);
}, 10, 1 );