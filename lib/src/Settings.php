<?php

namespace Plinth;

class Settings {

	private $slug;

	private $definitions = array();

	public function __construct( $slug ) {
		$this->slug = $slug;
	}

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

	public function get_definitions() {
		return $this->definitions;
	}

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

	public function register_ajax() {
		$action = $this->slug . '_save_setting';

		add_action( 'wp_ajax_' . $action, array( $this, 'ajax_save' ) );
	}

	public function ajax_save() {
		check_ajax_referer( $this->slug . '_plinth_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
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
				echo '<div class="pln-section-label">' . esc_html( $label ) . '</div>';
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

	private function render_setting_row( $key, array $def ) {
		$is_disabled = $def['disabled'];
		if ( is_callable( $is_disabled ) ) {
			$is_disabled = call_user_func( $is_disabled );
		}

		$value = $this->get( $key );
		?>
		<div class="pln-setting-row">
			<div class="pln-setting-info">
				<div class="pln-setting-label"><?php echo esc_html( $def['label'] ); ?></div>
				<?php if ( $def['desc'] ) : ?>
					<div class="pln-setting-desc"><?php echo esc_html( $def['desc'] ); ?></div>
				<?php endif; ?>
			</div>
			<div class="pln-setting-control">
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

	private function render_toggle( $key, $value, $is_disabled ) {
		$classes = 'pln-toggle';
		if ( $value ) {
			$classes .= ' on';
		}
		if ( $is_disabled ) {
			$classes .= ' disabled';
		}

		printf(
			'<button type="button" class="%s" data-setting="%s" role="switch" aria-checked="%s"%s></button>',
			esc_attr( $classes ),
			esc_attr( $key ),
			$value ? 'true' : 'false',
			$is_disabled ? ' disabled' : ''
		);
	}

	private function render_select( $key, $value, array $choices, $is_disabled ) {
		printf(
			'<select class="pln-setting-select" data-setting="%s"%s>',
			esc_attr( $key ),
			$is_disabled ? ' disabled' : ''
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
	}
}
