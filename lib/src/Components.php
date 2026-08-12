<?php
/**
 * Plinth Components — static helper methods for common UI elements.
 *
 * Each method outputs HTML directly. Use these inside tab callbacks to
 * build cards, stat grids, diagnostics tables, and recommendation rows
 * with consistent markup and BEM class names.
 *
 * @package Plinth
 */

namespace Plinth;

/**
 * Static helper methods for common UI elements.
 */
class Components {

	/**
	 * Render a page header with title and optional description.
	 *
	 * @param string $title       Page title.
	 * @param string $description Optional subtitle text.
	 * @return void
	 */
	public static function panel_header( $title, $description = '' ) {
		?>
		<h1 class="pln-page__title"><?php echo esc_html( $title ); ?></h1>
		<?php if ( $description ) : ?>
			<p class="pln-page__sub"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render a stat card with label, value, and optional meta text.
	 *
	 * @param string $label Stat label.
	 * @param string $value Stat value.
	 * @param string $meta  Optional meta text below the value.
	 * @return void
	 */
	public static function stat_card( $label, $value, $meta = '' ) {
		?>
		<div class="pln-stat">
			<div class="pln-stat__label"><?php echo esc_html( $label ); ?></div>
			<div class="pln-stat__value"><?php echo esc_html( $value ); ?></div>
			<?php if ( $meta ) : ?>
				<div class="pln-stat__meta"><?php echo esc_html( $meta ); ?></div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render a card with optional title and body content.
	 *
	 * @param string               $title   Card title, or empty to omit the header.
	 * @param callable|string|null $content Callback or HTML string for the body, or null.
	 * @param array|string         $options Header meta string, or options array:
	 *                                    - header_meta (string)
	 *                                    - wrap_body (bool) Wrap content in pln-card__body. Default true.
	 *                                    - class (string) Extra classes on the card element.
	 * @return void
	 */
	public static function card( $title, $content = null, $options = '' ) {
		if ( is_string( $options ) ) {
			$options = array( 'header_meta' => $options );
		} elseif ( ! is_array( $options ) ) {
			$options = array();
		}

		$options = array_merge(
			array(
				'header_meta' => '',
				'wrap_body'   => true,
				'class'       => '',
			),
			$options
		);

		$class = 'pln-card';
		if ( $options['class'] ) {
			$class .= ' ' . $options['class'];
		}

		echo '<div class="' . esc_attr( trim( $class ) ) . '">';

		if ( $title ) {
			echo '<div class="pln-card__header">';
			echo esc_html( $title );
			if ( $options['header_meta'] ) {
				echo '<span class="pln-card__header-meta">' . esc_html( $options['header_meta'] ) . '</span>';
			}
			echo '</div>';
		}

		if ( is_callable( $content ) ) {
			if ( $options['wrap_body'] ) {
				echo '<div class="pln-card__body">';
				call_user_func( $content );
				echo '</div>';
			} else {
				call_user_func( $content );
			}
		} elseif ( is_string( $content ) ) {
			if ( $options['wrap_body'] ) {
				echo '<div class="pln-card__body">' . wp_kses_post( $content ) . '</div>';
			} else {
				echo wp_kses_post( $content );
			}
		}

		echo '</div>';
	}

	/**
	 * @deprecated Cards auto-close since Plinth 0.1. No longer needed.
	 * @return void
	 */
	public static function card_close() {
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
	 * @return void
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
		<div class="pln-rec pln-rec--<?php echo esc_attr( $severity ); ?>">
			<div class="pln-rec__icon">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false">
					<?php
					$paths = array(
						'check'    => '<polyline points="20 6 9 17 4 12"/>',
						'package'  => '<path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 002 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0022 16z"/>',
						'zap'      => '<path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>',
						'edit'     => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 013 3L7 19l-4 1 1-4z"/>',
						'type'     => '<path d="M4 7V5h16v2M12 5v14M9 19h6"/>',
						'clock'    => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
						'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 11-4 0v-.09A1.65 1.65 0 008 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H2a2 2 0 110-4h.09A1.65 1.65 0 004.6 8a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06A1.65 1.65 0 009 3.68 1.65 1.65 0 0010 2.17V2a2 2 0 114 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06A1.65 1.65 0 0020.32 8a1.65 1.65 0 001.51 1H22a2 2 0 110 4h-.09a1.65 1.65 0 00-1.51 1z"/>',
					);
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG path data is hardcoded above.
					echo isset( $paths[ $icon ] ) ? $paths[ $icon ] : $paths['check'];
					?>
				</svg>
			</div>
			<div class="pln-rec__info">
				<div class="pln-rec__title">
					<?php echo esc_html( $title ); ?>
					<?php
					if ( $badge_html ) {
						echo wp_kses_post( $badge_html );
					}
					?>
				</div>
				<div class="pln-rec__desc"><?php echo esc_html( $desc ); ?></div>
			</div>
			<?php if ( $action ) : ?>
				<?php
				echo wp_kses(
					$action,
					array(
						'a'      => array(
							'href'  => true,
							'class' => true,
						),
						'button' => array(
							'type'             => true,
							'class'            => true,
							'data-pro-trigger' => true,
							'data-feature'     => true,
							'data-desc'        => true,
							'data-goto-tab'    => true,
						),
					)
				);
				?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render a diagnostics key-value row.
	 *
	 * @param string $key   Row label.
	 * @param string $value Row value.
	 * @return void
	 */
	public static function diagnostics_row( $key, $value ) {
		?>
		<li class="pln-diag__item">
			<span class="pln-diag__key"><?php echo esc_html( $key ); ?></span>
			<span class="pln-diag__val"><?php echo esc_html( $value ); ?></span>
		</li>
		<?php
	}

	/**
	 * Render a diagnostics status row with ok/warn indicator.
	 *
	 * @param string $key   Row label.
	 * @param string $label Status text.
	 * @param bool   $ok    True for ok, false for warn.
	 * @return void
	 */
	public static function diagnostics_status( $key, $label, $ok = true ) {
		?>
		<li class="pln-diag__item">
			<span class="pln-diag__key"><?php echo esc_html( $key ); ?></span>
			<span class="pln-diag__status <?php echo $ok ? 'ok' : 'warn'; ?>"><?php echo esc_html( $label ); ?></span>
		</li>
		<?php
	}

	/**
	 * Open a stats grid container.
	 *
	 * @return void
	 */
	public static function stats_grid_open() {
		echo '<div class="pln-stats">';
	}

	/**
	 * Close a stats grid container.
	 *
	 * @return void
	 */
	public static function stats_grid_close() {
		echo '</div>';
	}

	/**
	 * Open a diagnostics grid container.
	 *
	 * @return void
	 */
	public static function diag_grid_open() {
		echo '<div class="pln-diag-grid">';
	}

	/**
	 * Close a diagnostics grid container.
	 *
	 * @return void
	 */
	public static function diag_grid_close() {
		echo '</div>';
	}

	/**
	 * Render a section label divider.
	 *
	 * @param string $label Section label text.
	 * @return void
	 */
	public static function section_label( $label ) {
		echo '<div class="pln-section__label">' . esc_html( $label ) . '</div>';
	}

	/**
	 * Render a button element.
	 *
	 * @param string $label Button text.
	 * @param array  $attrs HTML attributes as name => value pairs.
	 * @return void
	 */
	public static function button( $label, $attrs = array() ) {
		$defaults = array(
			'type'  => 'button',
			'class' => 'pln-btn--outline',
			'id'    => '',
		);
		$attrs    = array_merge( $defaults, $attrs );

		$attr_str = '';
		foreach ( $attrs as $name => $val ) {
			if ( '' !== $val ) {
				$attr_str .= ' ' . esc_attr( $name ) . '="' . esc_attr( $val ) . '"';
			}
		}

		echo '<button' . $attr_str . '>' . esc_html( $label ) . '</button>';
	}
}
