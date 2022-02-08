<?php
/**
 * Upsell Custom Control
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
 * Upsell control
 */
class OGF_Customize_Upsell_Control extends WP_Customize_Control {

	/**
	 * The control type.
	 *
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
				<h2 class="upsell__title">Upgrade to Fonts Plugin Pro</h2>
				<ul>
					<li>✅ Unlock Font Size & Color</li>
					<li>📦 Host Fonts Locally</li>
					<li>⚡️ Optimized Font Loading</li>
					<li>🧙‍ Upload Custom Fonts</li>
				</ul>
				<a class="upsell__button button button-primary" href="https://fontsplugin.com/pro-upgrade/?utm_source=plugin&utm_medium=customizer&utm_campaign=<?php echo esc_attr( $this->section ); ?>" target="_blank">Learn More</a>
			</div>
		<?php
	}
}
