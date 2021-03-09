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
 * Repeater control
 */
class OGF_Customize_Repeater_Control extends WP_Customize_Control {

	/**
	 * The type of customize control being rendered.
	 *
	 * @var string
	 */
	public $type = 'ogf-repeater';

	/**
	 * Enqueue scripts/styles for the control.
	 */
	public function enqueue() {
		wp_enqueue_script( 'customizer-repeater-script', OGF_DIR_URL . 'assets/js/customizer-repeater.js', array( 'jquery', 'jquery-ui-draggable', 'wp-color-picker' ), OGF_VERSION, true );
		$custom_selectors_url = esc_url( admin_url( '/customize.php?autofocus[section]=ogf_custom' ) );
		wp_localize_script( 'customizer-repeater-script', 'ogf_custom_selectors_url', $custom_selectors_url );

	}

	/**
	 * Render the control.
	 */
	public function render_content() {
		// Get default options.
		$default = json_decode( $this->setting->default );
		// Get values (json format).
		$values = $this->value();
		// Decode values.
		$json = json_decode( $values );
		if ( ! is_array( $json ) ) {
			$json = array( $values );
		} ?>

		<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<div class="customizer-repeater-general-control-repeater">
			<?php
			if ( ( 1 === count( $json ) && '' === $json[0] ) || empty( $json ) ) {
				if ( ! empty( $default ) ) {
					$this->iterate_array( $default );
					?>
					<input type="hidden"
					id="customizer-repeater-<?php echo esc_attr( $this->id ); ?>-colector" <?php esc_attr( $this->link() ); ?>
					class="customizer-repeater-colector"
					value="<?php echo esc_textarea( wp_json_encode( $default ) ); ?>"/>
					<?php
				} else {
					$this->iterate_array();
					?>
					<input type="hidden"
					id="customizer-repeater-<?php echo esc_attr( $this->id ); ?>-colector" <?php esc_attr( $this->link() ); ?>
					class="customizer-repeater-colector"/>
					<?php
				}
			} else {
				$this->iterate_array( $json );
				?>
				<input type="hidden" id="customizer-repeater-<?php echo esc_attr( $this->id ); ?>-colector" <?php esc_attr( $this->link() ); ?>
				class="customizer-repeater-colector" value="<?php echo esc_textarea( $this->value() ); ?>"/>
				<?php
			}
			?>
		</div>
		<div class="ogf_custom_selectors_actions clear">
			<button type="button" class="button add_field customizer-repeater-new-field">
				<?php echo esc_html__( 'Add New', 'olympus-google-fonts' ); ?>
			</button>
			<button type="button" class="button button-primary ogf_save_elements_button">
				<?php echo esc_html__( 'Save Elements', 'olympus-google-fonts' ); ?>
			</button>
		</div>
		<?php
	}

	/**
	 * Iterate through the array of values.
	 *
	 * @param array $array The array.
	 */
	private function iterate_array( $array = array() ) {
		// Counter that helps checking if the box is first and should have the delete button disabled.
		$count = 0;
		if ( ! empty( $array ) ) {
			foreach ( $array as $icon ) {
				?>
				<div class="customizer-repeater-general-control-repeater-container">
					<?php $this->input_control( $icon, $count ); ?>
				</div>

				<?php
				$count++;
			}
		} else {
			?>
			<div class="customizer-repeater-general-control-repeater-container">
				<?php $this->input_control(); ?>
			</div>
			<?php
		}
	}
	/**
	 * Input Control
	 *
	 * @param object $values Values for the controls.
	 * @param string $count   Count how many controls have been displayed so far.
	 */
	private function input_control( $values = '', $count = 0 ) {
		$label       = ( isset( $values->label ) ? $values->label : '' );
		$description = ( isset( $values->description ) ? $values->description : '' );
		$selectors   = ( isset( $values->selectors ) ? $values->selectors : '' );
		$display     = ( 0 === $count ? 'none' : 'block' );
		?>
		<ul class="repeater-item clear">
			<li class="customize-control customize-control-text">
				<label for="customizer-repeater-label-control" class="customize-control-title">
					<?php esc_html_e( 'Label', 'olympus-google-fonts' ); ?>
				</label>
				<input
					type="text"
					value="<?php echo esc_attr( $label ); ?>"
					class="customizer-repeater-control customizer-repeater-label-control"
					placeholder="<?php esc_html_e( 'Label', 'olympus-google-fonts' ); ?>"
				/>
			</li>
			<li class="customize-control customize-control-text">
				<label for="customizer-repeater-description-control" class="customize-control-title">
					<?php esc_html_e( 'Description', 'olympus-google-fonts' ); ?>
				</label>
				<input
					type="text"
					value="<?php echo esc_attr( $description ); ?>"
					class="customizer-repeater-control customizer-repeater-description-control"
					placeholder="<?php esc_html_e( 'Description', 'olympus-google-fonts' ); ?>"
				/>
			</li>
			<li class="customize-control customize-control-text">
				<label for="customizer-repeater-selector-control" class="customize-control-title">
					<?php esc_html_e( 'Selectors', 'olympus-google-fonts' ); ?>
				</label>
				<input
					type="text"
					value="<?php echo esc_attr( $selectors ); ?>"
					class="customizer-repeater-control customizer-repeater-selectors-control"
					placeholder="<?php esc_html_e( 'Add your selectors...', 'olympus-google-fonts' ); ?>"
				/>
			</li>
			<button type="button" id="ogf-repeater-control-remove-field" class="button" style="display: <?php echo esc_attr( $display ); ?>">
				<?php esc_html_e( 'Delete field', 'olympus-google-fonts' ); ?>
			</button>
		</ul>
		<?php
	}

}
