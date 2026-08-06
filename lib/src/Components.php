<?php

namespace Plinth;

class Components {

	public static function panel_header( $title, $description = '' ) {
		?>
		<h1 class="pln-page-title"><?php echo esc_html( $title ); ?></h1>
		<?php if ( $description ) : ?>
			<p class="pln-page-sub"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
		<?php
	}

	public static function stat_card( $label, $value, $meta = '' ) {
		?>
		<div class="pln-stat-card">
			<div class="pln-stat-label"><?php echo esc_html( $label ); ?></div>
			<div class="pln-stat-value"><?php echo esc_html( $value ); ?></div>
			<?php if ( $meta ) : ?>
				<div class="pln-stat-meta"><?php echo esc_html( $meta ); ?></div>
			<?php endif; ?>
		</div>
		<?php
	}

	public static function card( $title, $content = null, $header_meta = '' ) {
		echo '<div class="pln-card">';

		if ( $title ) {
			echo '<div class="pln-card-header">';
			echo esc_html( $title );
			if ( $header_meta ) {
				echo '<span class="pln-card-header-meta">' . esc_html( $header_meta ) . '</span>';
			}
			echo '</div>';
		}

		if ( is_callable( $content ) ) {
			echo '<div class="pln-card-body">';
			call_user_func( $content );
			echo '</div>';
		} elseif ( is_string( $content ) ) {
			echo '<div class="pln-card-body">' . wp_kses_post( $content ) . '</div>';
		}
	}

	public static function card_close() {
		echo '</div>';
	}

	/**
	 * Render a recommendation row.
	 *
	 * @param string $severity   'low', 'medium', or 'high'.
	 * @param string $icon       Icon name from the Shell icon set.
	 * @param string $title      Row title.
	 * @param string $desc       Row description.
	 * @param string $action     Action button HTML (pre-escaped), or empty for no action.
	 * @param string $badge_html Optional badge HTML to append after the title (e.g. PRO badge).
	 */
	public static function recommendation_row( $severity, $icon, $title, $desc, $action = '', $badge_html = '' ) {
		$valid = array( 'low', 'medium', 'high' );
		if ( ! in_array( $severity, $valid, true ) ) {
			$severity = 'low';
		}

		$icon_map = array(
			'low'    => 'check',
			'medium' => 'package',
			'high'   => 'zap',
		);

		if ( ! $icon ) {
			$icon = $icon_map[ $severity ];
		}
		?>
		<div class="pln-rec-row pln-rec-<?php echo esc_attr( $severity ); ?>">
			<div class="pln-rec-icon">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false">
					<?php
					$paths = array(
						'check'   => '<polyline points="20 6 9 17 4 12"/>',
						'package' => '<path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 002 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0022 16z"/>',
						'zap'     => '<path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>',
						'edit'    => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 013 3L7 19l-4 1 1-4z"/>',
						'type'    => '<path d="M4 7V5h16v2M12 5v14M9 19h6"/>',
						'clock'   => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
					);
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG path data is hardcoded above.
					echo isset( $paths[ $icon ] ) ? $paths[ $icon ] : $paths['check'];
					?>
				</svg>
			</div>
			<div class="pln-rec-info">
				<div class="pln-rec-title">
					<?php echo esc_html( $title ); ?>
					<?php
					if ( $badge_html ) {
						echo wp_kses_post( $badge_html );
					}
					?>
				</div>
				<div class="pln-rec-desc"><?php echo esc_html( $desc ); ?></div>
			</div>
			<?php if ( $action ) : ?>
				<?php echo wp_kses_post( $action ); ?>
			<?php endif; ?>
		</div>
		<?php
	}

	public static function diagnostics_row( $key, $value ) {
		?>
		<li class="pln-diag-item">
			<span class="pln-diag-key"><?php echo esc_html( $key ); ?></span>
			<span class="pln-diag-val"><?php echo esc_html( $value ); ?></span>
		</li>
		<?php
	}

	public static function diagnostics_status( $key, $label, $ok = true ) {
		?>
		<li class="pln-diag-item">
			<span class="pln-diag-key"><?php echo esc_html( $key ); ?></span>
			<span class="pln-diag-status <?php echo $ok ? 'ok' : 'warn'; ?>"><?php echo esc_html( $label ); ?></span>
		</li>
		<?php
	}

	public static function stats_grid_open() {
		echo '<div class="pln-stats-grid">';
	}

	public static function stats_grid_close() {
		echo '</div>';
	}

	public static function diag_grid_open() {
		echo '<div class="pln-diag-grid">';
	}

	public static function diag_grid_close() {
		echo '</div>';
	}

	public static function section_label( $label ) {
		echo '<div class="pln-section-label">' . esc_html( $label ) . '</div>';
	}

	public static function button( $label, $attrs = array() ) {
		$defaults = array(
			'type'  => 'button',
			'class' => 'pln-btn-outline',
			'id'    => '',
		);
		$attrs = array_merge( $defaults, $attrs );

		$attr_str = '';
		foreach ( $attrs as $name => $val ) {
			if ( '' !== $val ) {
				$attr_str .= ' ' . esc_attr( $name ) . '="' . esc_attr( $val ) . '"';
			}
		}

		echo '<button' . $attr_str . '>' . esc_html( $label ) . '</button>';
	}
}
