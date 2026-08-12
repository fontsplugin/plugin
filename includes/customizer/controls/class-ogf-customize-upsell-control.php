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
	public $type = 'ogf-upsell';

	/**
	 * Render the control's content.
	 */
	public function render_content() {
		?>
		<div class="ogf-upsell">
			<h2 class="upsell__title"><?php esc_html_e( 'Unlock Fonts Plugin Pro', 'olympus-google-fonts' ); ?></h2>
			<ul class="upsell__list">
				<li><?php esc_html_e( 'Faster pages with local hosting', 'olympus-google-fonts' ); ?></li>
				<li><?php esc_html_e( 'Cut font sizes by up to 90%', 'olympus-google-fonts' ); ?></li>
				<li><?php esc_html_e( 'Control size, colour and spacing', 'olympus-google-fonts' ); ?></li>
				<li><?php esc_html_e( 'Target any element', 'olympus-google-fonts' ); ?></li>
			</ul>
			<a class="upsell__button button button-primary" href="https://fontsplugin.com/pro-upgrade/?utm_source=plugin&utm_medium=customizer&utm_campaign=<?php echo esc_attr( $this->section ); ?>" target="_blank"><?php esc_html_e( 'Unlock Fonts Plugin Pro', 'olympus-google-fonts' ); ?></a>
		</div>
		<?php
	}
}
