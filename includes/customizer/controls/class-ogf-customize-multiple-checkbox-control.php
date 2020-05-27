<?php
/**
 * Multiple Checkbox Custom Control
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
 * Multi check control
 */
class OGF_Customize_Multiple_Checkbox_Control extends WP_Customize_Control {
	/**
	 * The control type.
	 *
	 * @var string
	 */
	public $type = 'multiple-checkbox';
	/**
	 * Enqueue control related scripts/styles.
	 *
	 * @access public
	 */
	public function enqueue() {
		wp_enqueue_script( 'ogf-multicheck', OGF_DIR_URL . 'assets/js/multiple-checkbox.js', array( 'jquery' ), OGF_VERSION, true );
	}
	/**
	 * Refresh the parameters passed to the JavaScript via JSON.
	 *
	 * @see WP_Customize_Control::to_json()
	 */
	public function to_json() {
		parent::to_json();
		if ( isset( $this->default ) ) {
			$this->json['default'] = $this->default;
		} else {
			$this->json['default'] = $this->setting->default;
		}
		$this->json['value']      = $this->value();
		$this->json['choices']    = $this->choices;
		$this->json['link']       = $this->get_link();
		$this->json['id']         = $this->id;
		$this->json['inputAttrs'] = '';
		foreach ( $this->input_attrs as $attr => $value ) {
			$this->json['inputAttrs'] .= $attr . '="' . esc_attr( $value ) . '" ';
		}
	}

	/**
	 * Overwrite this method as we are rendering the template with JS.
	 *
	 * @access protected
	 * @since 1.0
	 * @return void
	 */
	protected function render_content() {}

	/**
	 * An Underscore (JS) template for this control's content (but not its container).
	 *
	 * Class variables for this control class are available in the `data` JS object;
	 * export custom variables by overriding {@see WP_Customize_Control::to_json()}.
	 *
	 * @see WP_Customize_Control::print_template()
	 */
	protected function content_template() {
		?>
		<# if ( ! data.choices ) { return; } #>

		<# if ( data.label ) { #>
			<span class="customize-control-title">{{ data.label }}</span>
		<# } #>

		<# if ( data.description ) { #>
			<span class="description customize-control-description">{{{ data.description }}}</span>
		<# } #>

		<ul>
			<# for ( key in data.choices ) { #>
				<li>
					<label>
						<input {{{ data.inputAttrs }}} type="checkbox" value="{{ key }}"
							<# if ( _.contains( data.value, key ) ) { #> checked<# } #>
						/>
						{{ data.choices[ key ] }}
					</label>
				</li>
			<# } #>
		</ul>
		<?php
	}

}
