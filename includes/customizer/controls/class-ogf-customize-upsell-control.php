<?php
/**
 * Upsell Custom Control
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2019, Danny Cooper
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Upsell control
 */
class OGF_Customize_Upsell_Control extends WP_Customize_Control {

	/**
	 * The control type.
	 *
	 * @access public
	 * @var string
	 */
	public $type = 'upsell';

	/**
	 * Render the control's content.
	 *
	 * Allows the content to be overriden without having to rewrite the wrapper.
	 */
	public function render_content() { ?>
			<div class="ogf-upsell">
				<h2 class="upsell__title">Upgrade to Google Fonts Pro</h2>
				<ul>
					<li>✅ Unlock Font Size</li>
					<li>✅ Unlock Font Color</li>
					<li>✅ Unlock Line Height</li>
					<li>⚡️ Optimized Font Loading</li>
					<li>🧙‍ Custom Elements</li>
				</ul>
				<a class="upsell__button button button-primary" href="https://fontsplugin.com/upgrade/?utm_source=customizer&utm_campaign=<?php echo esc_attr( $this->section ); ?>" target="_blank">Learn More</a>
			</div>
		<?php
	}

}
