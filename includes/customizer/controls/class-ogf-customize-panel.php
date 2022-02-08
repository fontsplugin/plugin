<?php
/**
 * Customize Repeater Custom Control
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extend the core panel Class.
 */
class OGF_Customize_Panel extends WP_Customize_Panel {

	/**
	 * The parent panel ID.
	 *
	 * @var string
	 */
	public $panel;

	/**
	 * Type of this panel.
	 *
	 * @var string
	 */
	public $type = 'ogf_panel';

	/**
	 * Gather the parameters passed to client JavaScript via JSON.
	 *
	 * @return array
	 */
	public function json() {
		$array                   = wp_array_slice_assoc( (array) $this, array( 'id', 'description', 'priority', 'type', 'panel' ) );
		$array['title']          = html_entity_decode( $this->title, ENT_QUOTES, get_bloginfo( 'charset' ) );
		$array['content']        = $this->get_content();
		$array['active']         = $this->active();
		$array['instanceNumber'] = $this->instance_number;
		return $array;
	}
}
