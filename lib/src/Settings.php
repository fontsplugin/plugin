<?php
/**
 * Plinth Settings — declarative settings with auto-rendered UI and AJAX save.
 *
 * Register settings as key => definition pairs. Plinth renders the toggle or
 * select control, saves values through AJAX, and reads from the correct
 * WordPress storage layer (option or theme_mod).
 *
 * Two filters let the consumer change the rendered HTML without Plinth
 * knowledge of consumer-specific concepts (e.g. PRO badges, upsell modals):
 *
 *   plinth_setting_label   — filters the full label HTML for a setting row.
 *   plinth_setting_control — filters the full control HTML (toggle or select).
 *
 * The consumer can create this instance early and pass it to Shell later.
 * This lets the AJAX handler register on every admin request, not only when
 * the admin page renders. See Shell::__construct() for the hand-off.
 *
 * @package Plinth
 */

namespace Plinth;

/**
 * Declarative settings with auto-rendered UI and AJAX save.
 */
class Settings {

	/**
	 * Plugin slug used to namespace the AJAX action and nonce.
	 *
	 * @var string
	 */
	private $slug;

	/**
	 * WordPress capability required to save settings.
	 *
	 * @var string
	 */
	private $capability = 'manage_options';

	/**
	 * Registered setting definitions keyed by setting name.
	 *
	 * @var array
	 */
	private $definitions = array();

	/**
	 * Create a Settings instance.
	 *
	 * @param string $slug       Plugin slug. Used to namespace the AJAX action and nonce.
	 * @param string $capability WordPress capability to save settings. Match to your
	 *                           data type: 'edit_theme_options' for theme_mods,
	 *                           'manage_options' for wp_options.
	 */
	public function __construct( $slug, $capability = 'manage_options' ) {
		$this->slug       = $slug;
		$this->capability = $capability;
	}

	/**
	 * Register setting definitions.
	 *
	 * @param array $definitions Setting definitions keyed by setting name.
	 * @return void
	 */
	public function register( array $definitions ) {
		$defaults = array(
			'type'     => 'boolean',
			'storage'  => 'option',
			'default'  => null,
			'label'    => '',
			'desc'     => '',
			'choices'  => array(),
			'disabled' => false,
		);

		foreach ( $definitions as $key => $definition ) {
			$this->definitions[ $key ] = array_merge( $defaults, $definition );
		}
	}

	/**
	 * Get all registered setting definitions.
	 *
	 * @return array
	 */
	public function get_definitions() {
		return $this->definitions;
	}

	/**
	 * Read a setting value from the database.
	 *
	 * @param string $key Setting name.
	 * @return mixed|null Value, or null if the key is not registered.
	 */
	public function get( $key ) {
		if ( ! isset( $this->definitions[ $key ] ) ) {
			return null;
		}

		$def     = $this->definitions[ $key ];
		$default = $def['default'];

		if ( 'theme_mod' === $def['storage'] ) {
			return get_theme_mod( $key, $default );
		}

		return get_option( $key, $default );
	}

	/**
	 * Write a setting value to the database.
	 *
	 * @param string $key   Setting name.
	 * @param mixed  $value Value to store.
	 * @return bool True on success, false if the key is not registered.
	 */
	public function set( $key, $value ) {
		if ( ! isset( $this->definitions[ $key ] ) ) {
			return false;
		}

		$def = $this->definitions[ $key ];

		if ( 'theme_mod' === $def['storage'] ) {
			set_theme_mod( $key, $value );
		} else {
			update_option( $key, $value );
		}

		return true;
	}

	/**
	 * Register the AJAX save handler. Call this on every admin load, not only
	 * on the plugin page. WordPress must know about the action before an AJAX
	 * request arrives, otherwise the request returns HTTP 400.
	 */
	public function register_ajax() {
		$action = $this->slug . '_save_setting';

		add_action( 'wp_ajax_' . $action, array( $this, 'ajax_save' ) );
	}

	/**
	 * Handle the AJAX save request.
	 *
	 * @return void
	 */
	public function ajax_save() {
		check_ajax_referer( $this->slug . '_plinth_nonce' );

		if ( ! current_user_can( $this->capability ) ) {
			wp_send_json_error( 'insufficient_permissions' );
		}

		$key   = isset( $_POST['setting'] ) ? sanitize_text_field( wp_unslash( $_POST['setting'] ) ) : '';
		$value = isset( $_POST['value'] ) ? sanitize_text_field( wp_unslash( $_POST['value'] ) ) : '';

		if ( ! isset( $this->definitions[ $key ] ) ) {
			wp_send_json_error( 'invalid_setting' );
		}

		$def = $this->definitions[ $key ];

		$is_disabled = $def['disabled'];
		if ( is_callable( $is_disabled ) ) {
			$is_disabled = call_user_func( $is_disabled );
		}
		if ( $is_disabled ) {
			wp_send_json_error( 'setting_disabled' );
		}

		$value = $this->sanitize( $value, $def );

		if ( null === $value ) {
			wp_send_json_error( 'invalid_value' );
		}

		$this->set( $key, $value );

		wp_send_json_success();
	}

	/**
	 * Sanitize a value according to the setting definition type.
	 *
	 * @param mixed $value Raw value from the request.
	 * @param array $def   Setting definition.
	 * @return mixed|null Sanitized value, or null if invalid.
	 */
	private function sanitize( $value, array $def ) {
		switch ( $def['type'] ) {
			case 'boolean':
				return '1' === $value || true === $value;

			case 'select':
				if ( ! empty( $def['choices'] ) && ! array_key_exists( $value, $def['choices'] ) ) {
					return null;
				}
				return $value;

			case 'text':
				return sanitize_text_field( $value );

			default:
				return sanitize_text_field( $value );
		}
	}

	/**
	 * Render a full settings tab with sections and rows.
	 *
	 * @param array $tab Tab configuration with title, desc, and sections keys.
	 * @return void
	 */
	public function render_tab( array $tab ) {
		$title    = isset( $tab['title'] ) ? $tab['title'] : '';
		$desc     = isset( $tab['desc'] ) ? $tab['desc'] : '';
		$sections = isset( $tab['sections'] ) ? $tab['sections'] : array();

		if ( $title || $desc ) {
			Components::panel_header( $title, $desc );
		}

		echo '<div class="pln-card">';

		foreach ( $sections as $section_id => $section ) {
			$label    = isset( $section['label'] ) ? $section['label'] : '';
			$settings = isset( $section['settings'] ) ? $section['settings'] : array();

			if ( $label ) {
				echo '<div class="pln-section__label">' . esc_html( $label ) . '</div>';
			}

			foreach ( $settings as $key ) {
				if ( ! isset( $this->definitions[ $key ] ) ) {
					continue;
				}

				$this->render_setting_row( $key, $this->definitions[ $key ] );
			}
		}

		echo '</div>';
	}

	/**
	 * Render a single setting row with label and control.
	 *
	 * @param string $key Setting name.
	 * @param array  $def Setting definition.
	 * @return void
	 */
	private function render_setting_row( $key, array $def ) {
		$is_disabled = $def['disabled'];
		if ( is_callable( $is_disabled ) ) {
			$is_disabled = call_user_func( $is_disabled );
		}

		$value = $this->get( $key );
		?>
		<div class="pln-setting">
			<div class="pln-setting__info">
				<?php
					$label_html = '<div class="pln-setting__label">' . esc_html( $def['label'] ) . '</div>';
					echo apply_filters( 'plinth_setting_label', $label_html, $key, $def );
				?>
				<?php if ( $def['desc'] ) : ?>
					<div class="pln-setting__desc"><?php echo esc_html( $def['desc'] ); ?></div>
				<?php endif; ?>
			</div>
			<div class="pln-setting__control">
				<?php
				if ( 'boolean' === $def['type'] ) {
					$this->render_toggle( $key, $value, $is_disabled );
				} elseif ( 'select' === $def['type'] ) {
					$this->render_select( $key, $value, $def['choices'], $is_disabled );
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a toggle switch control.
	 *
	 * @param string $key         Setting name.
	 * @param mixed  $value       Current stored value.
	 * @param bool   $is_disabled Whether the control is disabled.
	 * @return void
	 */
	private function render_toggle( $key, $value, $is_disabled ) {
		$classes = 'pln-toggle';
		if ( $value ) {
			$classes .= ' on';
		}
		if ( $is_disabled ) {
			$classes .= ' disabled';
		}

		// Use aria-disabled, not the HTML disabled attribute. A disabled button
		// blocks all click events. Consumers need click events to show upsell
		// modals or other prompts on disabled controls.
		$html = sprintf(
			'<button type="button" class="%s" data-setting="%s" role="switch" aria-checked="%s"%s></button>',
			esc_attr( $classes ),
			esc_attr( $key ),
			$value ? 'true' : 'false',
			$is_disabled ? ' aria-disabled="true"' : ''
		);

		echo apply_filters( 'plinth_setting_control', $html, $key, $this->definitions[ $key ] );
	}

	/**
	 * Render a select dropdown control.
	 *
	 * @param string $key         Setting name.
	 * @param mixed  $value       Current stored value.
	 * @param array  $choices     Choices as value => label pairs.
	 * @param bool   $is_disabled Whether the control is disabled.
	 * @return void
	 */
	private function render_select( $key, $value, array $choices, $is_disabled ) {
		ob_start();

		printf(
			'<select class="pln-setting__select" data-setting="%s"%s>',
			esc_attr( $key ),
			$is_disabled ? ' aria-disabled="true"' : ''
		);

		foreach ( $choices as $option_value => $option_label ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $option_value ),
				selected( $value, $option_value, false ),
				esc_html( $option_label )
			);
		}

		echo '</select>';

		$html = ob_get_clean();
		echo apply_filters( 'plinth_setting_control', $html, $key, $this->definitions[ $key ] );
	}
}
