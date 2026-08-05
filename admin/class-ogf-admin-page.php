<?php
/**
 * Admin Page for the Fonts Plugin.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * OGF_Admin_Page Class.
 */
class OGF_Admin_Page {

	/**
	 * Active fonts data cache.
	 *
	 * @var array
	 */
	private $active_fonts = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_ogf_save_settings', array( $this, 'ajax_save_settings' ) );
		add_action( 'wp_ajax_ogf_clear_font_cache', array( $this, 'ajax_clear_font_cache' ) );
		add_action( 'wp_ajax_ogf_reset_all_fonts', array( $this, 'ajax_reset_all_fonts' ) );
		add_action( 'wp_ajax_ogf_save_custom_font', array( $this, 'ajax_save_custom_font' ) );
		add_action( 'wp_ajax_ogf_delete_custom_font', array( $this, 'ajax_delete_custom_font' ) );
		add_action( 'wp_ajax_ogf_save_typekit_key', array( $this, 'ajax_save_typekit_key' ) );
		add_action( 'wp_ajax_ogf_refresh_typekit', array( $this, 'ajax_refresh_typekit' ) );
		add_action( 'wp_ajax_ogf_toggle_typekit_kit', array( $this, 'ajax_toggle_typekit_kit' ) );
	}

	/**
	 * Echo an inline SVG icon.
	 *
	 * @param string $name      Icon name.
	 * @param int    $size      Width/height in pixels.
	 * @param float  $stroke    Stroke width.
	 * @param string $classname Optional class attribute.
	 */
	private function icon( $name, $size = 16, $stroke = 2, $classname = '' ) {
		$paths = array(
			'check'         => '<polyline points="20 6 9 17 4 12"/>',
			'package'       => '<path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 002 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0022 16z"/>',
			'zap'           => '<path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>',
			'clock'         => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
			'edit'          => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 013 3L7 19l-4 1 1-4z"/>',
			'upload'        => '<path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>',
			'book'          => '<path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/>',
			'chevron-right' => '<path d="M9 18l6-6-6-6"/>',
			'arrow-right'   => '<path d="M5 12h14"/><path d="M12 5l7 7-7 7"/>',
			'globe'         => '<path d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.66 0 3-4.03 3-9s-1.34-9-3-9m0 18c-1.66 0-3-4.03-3-9s1.34-9 3-9"/>',
			'trash'         => '<path d="M3 6h18"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>',
			'close'         => '<path d="M18 6L6 18"/><path d="M6 6l12 12"/>',
			'activity'      => '<path d="M3 12h4l3 8 4-16 3 8h4"/>',
			'type'          => '<path d="M4 7V5h16v2M12 5v14M9 19h6"/>',
			'adobe'         => '<path d="M12 3l9 16H3l9-16z"/>',
			'settings'      => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 11-4 0v-.09A1.65 1.65 0 008 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H2a2 2 0 110-4h.09A1.65 1.65 0 004.6 8a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06A1.65 1.65 0 009 3.68 1.65 1.65 0 0010 2.17V2a2 2 0 114 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06A1.65 1.65 0 0020.32 8a1.65 1.65 0 001.51 1H22a2 2 0 110 4h-.09a1.65 1.65 0 00-1.51 1z"/>',
			'pulse'         => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
			'copy'          => '<rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>',
		);

		if ( ! isset( $paths[ $name ] ) ) {
			return;
		}

		$svg = sprintf(
			'<svg %1$swidth="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="%3$s" aria-hidden="true" focusable="false">%4$s</svg>',
			$classname ? 'class="' . esc_attr( $classname ) . '" ' : '',
			(int) $size,
			esc_attr( (string) $stroke ),
			$paths[ $name ]
		);

		echo wp_kses( $svg, $this->allowed_svg_tags() );
	}

	/**
	 * Tags and attributes allowed in the inline icons.
	 *
	 * @return array
	 */
	private function allowed_svg_tags() {
		return array(
			'svg'      => array(
				'class'        => true,
				'width'        => true,
				'height'       => true,
				'viewbox'      => true,
				'fill'         => true,
				'stroke'       => true,
				'stroke-width' => true,
				'aria-hidden'  => true,
				'focusable'    => true,
			),
			'path'     => array( 'd' => true ),
			'polyline' => array( 'points' => true ),
			'circle'   => array(
				'cx' => true,
				'cy' => true,
				'r'  => true,
			),
			'rect'     => array(
				'x'      => true,
				'y'      => true,
				'width'  => true,
				'height' => true,
				'rx'     => true,
			),
			'line'     => array(
				'x1' => true,
				'y1' => true,
				'x2' => true,
				'y2' => true,
			),
		);
	}

	/**
	 * Build a human-readable label for a font weight key.
	 *
	 * Weight arrays are keyed by weight ('400', '400i') with a numeric index
	 * as the value, so the label has to be derived from the key.
	 *
	 * @param string $key Weight key, e.g. '400' or '400i'.
	 * @return string
	 */
	private function weight_label( $key ) {
		$names = array(
			'100' => __( 'Thin', 'olympus-google-fonts' ),
			'200' => __( 'Extra Light', 'olympus-google-fonts' ),
			'300' => __( 'Light', 'olympus-google-fonts' ),
			'400' => __( 'Normal', 'olympus-google-fonts' ),
			'500' => __( 'Medium', 'olympus-google-fonts' ),
			'600' => __( 'Semi Bold', 'olympus-google-fonts' ),
			'700' => __( 'Bold', 'olympus-google-fonts' ),
			'800' => __( 'Extra Bold', 'olympus-google-fonts' ),
			'900' => __( 'Ultra Bold', 'olympus-google-fonts' ),
		);

		$key       = (string) $key;
		$is_italic = 'i' === substr( $key, -1 );
		$weight    = $is_italic ? substr( $key, 0, -1 ) : $key;
		$name      = isset( $names[ $weight ] ) ? $names[ $weight ] : $weight;

		if ( $is_italic ) {
			/* translators: %s: weight name, e.g. "Bold" */
			$name = sprintf( __( '%s Italic', 'olympus-google-fonts' ), $name );
		}

		return $name;
	}

	/**
	 * Build the upright weight labels shown in the Active Fonts detail row.
	 *
	 * Italics are left out of the labels: they roughly double the list without
	 * telling anyone something they'd act on here. The header badge still
	 * reports the true variant total, and the Font Loading tab still lists
	 * every variant, because there each one is a control.
	 *
	 * Consecutive weights collapse to a range ('100–900') so a font like Inter
	 * reads as one pill instead of nine. Runs only merge when they really are
	 * consecutive, so a 400/700 font still shows two separate weights.
	 *
	 * @param array $weight_keys Weight keys, e.g. array( '400', '700', '400i' ).
	 * @return array Weight labels with consecutive runs collapsed.
	 */
	private function upright_weight_labels( $weight_keys ) {
		$upright = array();
		$other   = array();

		foreach ( $weight_keys as $key ) {
			$key = (string) $key;

			if ( preg_match( '/^\d+$/', $key ) ) {
				$upright[] = (int) $key;
			} elseif ( ! preg_match( '/^\d+i$/', $key ) ) {
				// Not a weight we recognise — surface it rather than drop it.
				$other[] = $key;
			}
		}

		return array_merge( $this->collapse_weight_runs( $upright ), $other );
	}

	/**
	 * Collapse consecutive weights into range labels.
	 *
	 * @param array $weights Numeric weights.
	 * @return array Display labels, e.g. array( '100–400', '700' ).
	 */
	private function collapse_weight_runs( $weights ) {
		if ( empty( $weights ) ) {
			return array();
		}

		sort( $weights, SORT_NUMERIC );

		$labels = array();
		$start  = null;
		$prev   = null;

		foreach ( $weights as $weight ) {
			if ( null === $start ) {
				$start = $weight;
			} elseif ( $weight !== $prev + 100 ) {
				$labels[] = $start === $prev ? (string) $start : $start . '–' . $prev;
				$start    = $weight;
			}

			$prev = $weight;
		}

		$labels[] = $start === $prev ? (string) $start : $start . '–' . $prev;

		return $labels;
	}

	/**
	 * Render the admin page.
	 */
	public function render() {
		$fonts       = $this->get_active_fonts();
		$adobe_fonts = ogf_typekit_fonts();

		// Slug => label, icon, optional count shown at the end of the row.
		$sections = array(
			array(
				'group' => __( 'Overview', 'olympus-google-fonts' ),
				'items' => array(
					array( 'dashboard', __( 'Dashboard', 'olympus-google-fonts' ), 'activity', null ),
				),
			),
			array(
				'group' => __( 'Typography', 'olympus-google-fonts' ),
				'items' => array(
					array( 'fonts', __( 'Active Fonts', 'olympus-google-fonts' ), 'type', count( $fonts ) ),
					array( 'font-loading', __( 'Font Loading', 'olympus-google-fonts' ), 'zap', null ),
				),
			),
			array(
				'group' => __( 'Sources', 'olympus-google-fonts' ),
				'items' => array(
					array( 'upload', __( 'Upload Fonts', 'olympus-google-fonts' ), 'upload', null ),
					array( 'adobe', __( 'Adobe Fonts', 'olympus-google-fonts' ), 'adobe', count( $adobe_fonts ) ),
				),
			),
			array(
				'group' => null,
				'items' => array(
					array( 'settings', __( 'Settings', 'olympus-google-fonts' ), 'settings', null ),
					array( 'diagnostics', __( 'Diagnostics', 'olympus-google-fonts' ), 'pulse', null ),
				),
			),
		);
		?>
		<div class="ogf-wrap">
			<nav class="ogf-nav" aria-label="<?php esc_attr_e( 'Fonts Plugin sections', 'olympus-google-fonts' ); ?>">
				<div class="ogf-nav-head">
					<div class="ogf-nav-icon">Fp</div>
					<div class="ogf-nav-id">
						<span class="ogf-nav-name"><?php esc_html_e( 'Fonts Plugin', 'olympus-google-fonts' ); ?></span>
						<span class="ogf-nav-version">v<?php echo esc_html( OGF_VERSION ); ?></span>
					</div>
				</div>

				<div class="ogf-nav-list" role="tablist">
					<?php foreach ( $sections as $index => $section ) : ?>
						<?php if ( $section['group'] ) : ?>
							<div class="ogf-nav-group"><?php echo esc_html( $section['group'] ); ?></div>
						<?php elseif ( $index > 0 ) : ?>
							<div class="ogf-nav-rule"></div>
						<?php endif; ?>

						<?php foreach ( $section['items'] as $item ) : ?>
							<?php list( $slug, $label, $icon, $count ) = $item; ?>
							<button class="ogf-nav-item<?php echo 'dashboard' === $slug ? ' active' : ''; ?>" role="tab" data-tab="<?php echo esc_attr( $slug ); ?>">
								<?php $this->icon( $icon, 15 ); ?>
								<span class="ogf-nav-label"><?php echo esc_html( $label ); ?></span>
								<?php if ( $count ) : ?>
									<span class="ogf-nav-count"><?php echo esc_html( $count ); ?></span>
								<?php endif; ?>
							</button>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</div>

				<div class="ogf-nav-foot">
					<div class="ogf-help">
						<strong><?php esc_html_e( 'Need help?', 'olympus-google-fonts' ); ?></strong>
						<?php
						printf(
							/* translators: %s: link to the plugin documentation */
							esc_html__( 'Read the %s or open a support ticket on WordPress.org.', 'olympus-google-fonts' ),
							'<a href="https://docs.fontsplugin.com/" target="_blank">' . esc_html__( 'documentation', 'olympus-google-fonts' ) . '</a>'
						);
						?>
					</div>
				</div>
			</nav>

			<div class="ogf-main">
				<?php
				$this->render_tab_dashboard();
				$this->render_tab_fonts();
				$this->render_tab_font_loading();
				$this->render_tab_upload();
				$this->render_tab_adobe();
				$this->render_tab_settings();
				$this->render_tab_diagnostics();
				?>
			</div>

			<?php $this->render_upgrade_modal(); ?>
			<div id="ogf-toast" class="ogf-toast">
				<?php $this->icon( 'check', 16, 2.5 ); ?>
				<span id="ogf-toast-msg"></span>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the heading that opens each section.
	 *
	 * With no top bar, this is what tells you where you are, so every panel
	 * carries its own — which also keeps the heading correct without JS.
	 *
	 * @param string $title       Section title.
	 * @param string $description One line of orientation.
	 */
	private function render_panel_header( $title, $description ) {
		?>
		<h1 class="ogf-page-title"><?php echo esc_html( $title ); ?></h1>
		<p class="ogf-page-sub"><?php echo esc_html( $description ); ?></p>
		<?php
	}

	/**
	 * Get active fonts with metadata.
	 *
	 * @return array
	 */
	private function get_active_fonts() {
		if ( null !== $this->active_fonts ) {
			return $this->active_fonts;
		}

		$fonts         = array();
		$elements      = array_merge( ogf_get_elements(), ogf_get_custom_elements() );
		$ogf_fonts     = OGF_Fonts::get_instance();
		$google_fonts  = OGF_Fonts::$google_fonts;
		$custom_fonts  = ogf_custom_fonts();
		$typekit_fonts = ogf_typekit_fonts();
		$system_fonts  = ogf_system_fonts();

		$font_elements = array();

		foreach ( $elements as $element_id => $element ) {
			$font_id = get_theme_mod( $element_id . '_font', '' );
			if ( ! $font_id || 'default' === $font_id ) {
				continue;
			}
			if ( ! isset( $font_elements[ $font_id ] ) ) {
				$font_elements[ $font_id ] = array();
			}
			$label                       = isset( $element['label'] ) ? $element['label'] : $element_id;
			$font_elements[ $font_id ][] = $label;
		}

		$load_fonts = get_theme_mod( 'ogf_load_fonts', array() );
		if ( is_array( $load_fonts ) ) {
			foreach ( $load_fonts as $font_id ) {
				if ( $font_id && ! isset( $font_elements[ $font_id ] ) ) {
					$font_elements[ $font_id ] = array( __( 'Load Only', 'olympus-google-fonts' ) );
				}
			}
		}

		foreach ( $font_elements as $font_id => $assigned_elements ) {
			$font_data = array(
				'id'       => $font_id,
				'name'     => $font_id,
				'type'     => 'system',
				'weights'  => array(),
				'subsets'  => array(),
				'elements' => $assigned_elements,
			);

			if ( isset( $google_fonts[ $font_id ] ) ) {
				$font_data['type']    = 'google';
				$font_data['name']    = isset( $google_fonts[ $font_id ]['f'] ) ? $google_fonts[ $font_id ]['f'] : $font_id;
				$font_data['weights'] = $ogf_fonts->get_font_weights( $font_id );
				$font_data['subsets'] = $ogf_fonts->get_font_subsets( $font_id );
			} elseif ( isset( $custom_fonts[ $font_id ] ) ) {
				$font_data['type'] = 'custom';
				$font_data['name'] = isset( $custom_fonts[ $font_id ]['label'] ) ? $custom_fonts[ $font_id ]['label'] : $font_id;
			} elseif ( isset( $typekit_fonts[ $font_id ] ) ) {
				$font_data['type'] = 'adobe';
				$font_data['name'] = isset( $typekit_fonts[ $font_id ]['label'] ) ? $typekit_fonts[ $font_id ]['label'] : $font_id;
			} elseif ( isset( $system_fonts[ $font_id ] ) ) {
				$font_data['type'] = 'system';
				$font_data['name'] = isset( $system_fonts[ $font_id ]['label'] ) ? $system_fonts[ $font_id ]['label'] : $font_id;
			}

			$fonts[ $font_id ] = $font_data;
		}

		$this->active_fonts = $fonts;
		return $fonts;
	}

	/**
	 * Render Dashboard tab.
	 */
	private function render_tab_dashboard() {
		$fonts        = $this->get_active_fonts();
		$total        = count( $fonts );
		$google_count = 0;
		$custom_count = 0;
		$adobe_count  = 0;

		foreach ( $fonts as $font ) {
			if ( 'google' === $font['type'] ) {
				++$google_count;
			} elseif ( 'custom' === $font['type'] ) {
				++$custom_count;
			} elseif ( 'adobe' === $font['type'] ) {
				++$adobe_count;
			}
		}

		$is_pro       = defined( 'OGF_PRO' );
		$host_locally = $is_pro && get_theme_mod( 'ogf_host_locally', false );
		$font_display = get_theme_mod( 'ogf_font_display', 'swap' );
		?>
		<div class="ogf-tab-panel active" id="ogf-tab-dashboard">
			<?php $this->render_panel_header( __( 'Dashboard', 'olympus-google-fonts' ), __( 'Your font setup at a glance — what\'s loaded, how it\'s served, and what to improve next.', 'olympus-google-fonts' ) ); ?>
			<div class="ogf-stats-grid">
				<div class="ogf-stat-card">
					<div class="ogf-stat-label"><?php esc_html_e( 'Fonts Loaded', 'olympus-google-fonts' ); ?></div>
					<div class="ogf-stat-value"><?php echo esc_html( $total ); ?></div>
					<div class="ogf-stat-meta">
						<?php
						$parts = array();
						if ( $google_count ) {
							/* translators: %d: number of Google fonts */
							$parts[] = sprintf( _n( '%d Google', '%d Google', $google_count, 'olympus-google-fonts' ), $google_count );
						}
						if ( $custom_count ) {
							/* translators: %d: number of custom fonts */
							$parts[] = sprintf( _n( '%d Custom', '%d Custom', $custom_count, 'olympus-google-fonts' ), $custom_count );
						}
						if ( $adobe_count ) {
							/* translators: %d: number of Adobe fonts */
							$parts[] = sprintf( _n( '%d Adobe', '%d Adobe', $adobe_count, 'olympus-google-fonts' ), $adobe_count );
						}
						echo esc_html( implode( ', ', $parts ) );
						?>
					</div>
				</div>
				<div class="ogf-stat-card">
					<div class="ogf-stat-label"><?php esc_html_e( 'Loading Method', 'olympus-google-fonts' ); ?></div>
					<div class="ogf-stat-value ogf-stat-value-sm">
						<?php echo $host_locally ? esc_html__( 'Local', 'olympus-google-fonts' ) : esc_html__( 'Google CDN', 'olympus-google-fonts' ); ?>
					</div>
					<div class="ogf-stat-meta">
						<?php
						if ( $host_locally ) {
							esc_html_e( 'Fonts served from your domain', 'olympus-google-fonts' );
						} else {
							esc_html_e( 'Fonts served from fonts.googleapis.com', 'olympus-google-fonts' );
						}
						?>
					</div>
				</div>
				<div class="ogf-stat-card">
					<div class="ogf-stat-label"><?php esc_html_e( 'Pro Status', 'olympus-google-fonts' ); ?></div>
					<div class="ogf-stat-value ogf-stat-value-sm">
						<?php echo $is_pro ? esc_html__( 'Active', 'olympus-google-fonts' ) : esc_html__( 'Free', 'olympus-google-fonts' ); ?>
					</div>
					<div class="ogf-stat-meta">
						<?php
						if ( $is_pro ) {
							esc_html_e( 'All features unlocked', 'olympus-google-fonts' );
						} else {
							esc_html_e( 'Upgrade for local hosting & more', 'olympus-google-fonts' );
						}
						?>
					</div>
				</div>
			</div>

			<?php $this->render_recommendations( $fonts, $google_count, $custom_count ); ?>

			<div class="ogf-card">
				<div class="ogf-card-header"><?php esc_html_e( 'Quick Actions', 'olympus-google-fonts' ); ?></div>
				<div class="ogf-card-body">
					<div class="ogf-actions-row">
						<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=ogf_google_fonts' ) ); ?>" class="ogf-action-btn">
							<?php $this->icon( 'edit' ); ?>
							<?php esc_html_e( 'Customize Fonts', 'olympus-google-fonts' ); ?>
						</a>
						<button type="button" class="ogf-action-btn" data-goto-tab="upload">
							<?php $this->icon( 'upload' ); ?>
							<?php esc_html_e( 'Upload Fonts', 'olympus-google-fonts' ); ?>
						</button>
						<a href="https://docs.fontsplugin.com/" target="_blank" class="ogf-action-btn">
							<?php $this->icon( 'book' ); ?>
							<?php esc_html_e( 'Documentation', 'olympus-google-fonts' ); ?>
						</a>
					</div>
				</div>
			</div>

			<?php if ( ! $is_pro ) : ?>
				<div class="ogf-pro-cta">
					<h3><?php esc_html_e( 'Upgrade to Fonts Plugin Pro', 'olympus-google-fonts' ); ?></h3>
					<p><?php esc_html_e( 'Unlock performance optimizations and full typographic control for your site.', 'olympus-google-fonts' ); ?></p>
					<ul>
						<li><?php $this->icon( 'check', 14, 2.5 ); ?><?php esc_html_e( 'Host fonts locally (GDPR)', 'olympus-google-fonts' ); ?></li>
						<li><?php $this->icon( 'check', 14, 2.5 ); ?><?php esc_html_e( 'Preloading & optimization', 'olympus-google-fonts' ); ?></li>
						<li><?php $this->icon( 'check', 14, 2.5 ); ?><?php esc_html_e( 'Custom element selectors', 'olympus-google-fonts' ); ?></li>
						<li><?php $this->icon( 'check', 14, 2.5 ); ?><?php esc_html_e( 'Selective weight loading', 'olympus-google-fonts' ); ?></li>
						<li><?php $this->icon( 'check', 14, 2.5 ); ?><?php esc_html_e( 'Remove external font requests', 'olympus-google-fonts' ); ?></li>
						<li><?php $this->icon( 'check', 14, 2.5 ); ?><?php esc_html_e( 'Font subset control', 'olympus-google-fonts' ); ?></li>
					</ul>
					<a href="https://fontsplugin.com/pro-upgrade/?utm_source=plugin&utm_medium=admin_page&utm_campaign=dashboard_cta" target="_blank" class="ogf-btn-pro">
						<?php esc_html_e( 'Upgrade to Pro', 'olympus-google-fonts' ); ?>
						<?php $this->icon( 'arrow-right', 14, 2.5 ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render the recommendations section.
	 *
	 * @param array $fonts        Active fonts.
	 * @param int   $google_count Number of Google fonts.
	 * @param int   $custom_count Number of custom fonts.
	 */
	private function render_recommendations( $fonts, $google_count, $custom_count ) {
		$is_pro       = defined( 'OGF_PRO' );
		$font_display = get_theme_mod( 'ogf_font_display', 'swap' );
		$total        = count( $fonts );
		?>
		<div class="ogf-card">
			<div class="ogf-card-header"><?php esc_html_e( 'Recommendations', 'olympus-google-fonts' ); ?></div>
			<div>
				<?php if ( 0 === $total ) : ?>
					<div class="ogf-rec-row ogf-rec-high">
						<div class="ogf-rec-icon"><?php $this->icon( 'edit' ); ?></div>
						<div class="ogf-rec-info">
							<div class="ogf-rec-title"><?php esc_html_e( 'Choose your first font', 'olympus-google-fonts' ); ?></div>
							<div class="ogf-rec-desc"><?php esc_html_e( 'No fonts are assigned yet, so your theme\'s default typography is still in use. Pick a font for your headings and body text to get started.', 'olympus-google-fonts' ); ?></div>
						</div>
						<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=ogf_google_fonts' ) ); ?>" class="ogf-rec-action"><?php esc_html_e( 'Choose', 'olympus-google-fonts' ); ?></a>
					</div>
				<?php else : ?>
					<div class="ogf-rec-row ogf-rec-low">
						<div class="ogf-rec-icon"><?php $this->icon( 'check' ); ?></div>
						<div class="ogf-rec-info">
							<div class="ogf-rec-title">
								<?php
								/* translators: %d: number of fonts */
								printf( esc_html( _n( '%d font configured', '%d fonts configured', $total, 'olympus-google-fonts' ) ), esc_html( $total ) );
								?>
							</div>
							<div class="ogf-rec-desc"><?php esc_html_e( 'You\'ve customized fonts across your site elements.', 'olympus-google-fonts' ); ?></div>
						</div>
					</div>
				<?php endif; ?>

				<?php // Only meaningful once something actually loads. ?>
				<?php if ( $total > 0 && 'swap' === $font_display ) : ?>
					<div class="ogf-rec-row ogf-rec-low">
						<div class="ogf-rec-icon"><?php $this->icon( 'check' ); ?></div>
						<div class="ogf-rec-info">
							<div class="ogf-rec-title"><?php esc_html_e( 'Font display is set to swap', 'olympus-google-fonts' ); ?></div>
							<div class="ogf-rec-desc"><?php esc_html_e( 'Text remains visible while fonts load. This is the recommended setting.', 'olympus-google-fonts' ); ?></div>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( $custom_count > 0 ) : ?>
					<div class="ogf-rec-row ogf-rec-low">
						<div class="ogf-rec-icon"><?php $this->icon( 'check' ); ?></div>
						<div class="ogf-rec-info">
							<div class="ogf-rec-title"><?php esc_html_e( 'Custom font uploaded', 'olympus-google-fonts' ); ?></div>
							<div class="ogf-rec-desc"><?php esc_html_e( 'Custom fonts are hosted on your domain automatically.', 'olympus-google-fonts' ); ?></div>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( ! $is_pro && $google_count > 0 ) : ?>
					<div class="ogf-rec-row ogf-rec-medium">
						<div class="ogf-rec-icon"><?php $this->icon( 'package' ); ?></div>
						<div class="ogf-rec-info">
							<div class="ogf-rec-title"><?php esc_html_e( 'Reduce unused font weights', 'olympus-google-fonts' ); ?> <span class="ogf-pro-badge"><?php esc_html_e( 'PRO', 'olympus-google-fonts' ); ?></span></div>
							<div class="ogf-rec-desc"><?php esc_html_e( 'You may be loading font weights that aren\'t used. Disabling unused weights reduces page size.', 'olympus-google-fonts' ); ?></div>
						</div>
						<button class="ogf-rec-action" data-pro-trigger data-feature="<?php esc_attr_e( 'Selective Font Loading', 'olympus-google-fonts' ); ?>" data-desc="<?php esc_attr_e( 'Choose exactly which font weights and subsets to load. Disable unused variants to reduce page weight.', 'olympus-google-fonts' ); ?>"><?php esc_html_e( 'Fix', 'olympus-google-fonts' ); ?></button>
					</div>

					<div class="ogf-rec-row ogf-rec-high">
						<div class="ogf-rec-icon"><?php $this->icon( 'zap' ); ?></div>
						<div class="ogf-rec-info">
							<div class="ogf-rec-title"><?php esc_html_e( 'Host fonts locally', 'olympus-google-fonts' ); ?> <span class="ogf-pro-badge"><?php esc_html_e( 'PRO', 'olympus-google-fonts' ); ?></span></div>
							<div class="ogf-rec-desc">
								<?php
								/* translators: %d: number of Google fonts */
								printf( esc_html( _n( '%d Google Font is loaded from Google\'s CDN. Hosting locally improves privacy (GDPR) and can reduce latency.', '%d Google Fonts are loaded from Google\'s CDN. Hosting locally improves privacy (GDPR) and can reduce latency.', $google_count, 'olympus-google-fonts' ) ), esc_html( $google_count ) );
								?>
							</div>
						</div>
						<button class="ogf-rec-action" data-pro-trigger data-feature="<?php esc_attr_e( 'Host Fonts Locally', 'olympus-google-fonts' ); ?>" data-desc="<?php esc_attr_e( 'Download and serve Google Fonts from your own domain. Eliminates external requests, improving GDPR compliance and page load speed.', 'olympus-google-fonts' ); ?>"><?php esc_html_e( 'Fix', 'olympus-google-fonts' ); ?></button>
					</div>

					<div class="ogf-rec-row ogf-rec-high">
						<div class="ogf-rec-icon"><?php $this->icon( 'clock' ); ?></div>
						<div class="ogf-rec-info">
							<div class="ogf-rec-title"><?php esc_html_e( 'Enable preloading', 'olympus-google-fonts' ); ?> <span class="ogf-pro-badge"><?php esc_html_e( 'PRO', 'olympus-google-fonts' ); ?></span></div>
							<div class="ogf-rec-desc"><?php esc_html_e( 'Add preload hints so the browser fetches font files earlier, reducing layout shift and improving Core Web Vitals.', 'olympus-google-fonts' ); ?></div>
						</div>
						<button class="ogf-rec-action" data-pro-trigger data-feature="<?php esc_attr_e( 'Preload Fonts', 'olympus-google-fonts' ); ?>" data-desc="<?php esc_attr_e( 'Add preload resource hints so the browser begins downloading fonts before they\'re needed, reducing layout shift.', 'olympus-google-fonts' ); ?>"><?php esc_html_e( 'Fix', 'olympus-google-fonts' ); ?></button>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Fonts tab.
	 */
	private function render_tab_fonts() {
		$fonts  = $this->get_active_fonts();
		$is_pro = defined( 'OGF_PRO' );
		?>
		<div class="ogf-tab-panel" id="ogf-tab-fonts">
			<?php $this->render_panel_header( __( 'Active Fonts', 'olympus-google-fonts' ), __( 'Every font assigned to an element on your site, with the weights and subsets each one loads.', 'olympus-google-fonts' ) ); ?>
			<div class="ogf-card">
				<div class="ogf-card-header">
					<?php esc_html_e( 'Active Fonts', 'olympus-google-fonts' ); ?>
					<span class="ogf-card-header-meta">
						<?php
						/* translators: %d: number of fonts */
						printf( esc_html( _n( '%d font loaded', '%d fonts loaded', count( $fonts ), 'olympus-google-fonts' ) ), esc_html( count( $fonts ) ) );
						?>
					</span>
				</div>
				<div>
					<?php if ( empty( $fonts ) ) : ?>
						<div class="ogf-card-body">
							<p><?php esc_html_e( 'No fonts configured yet.', 'olympus-google-fonts' ); ?></p>
							<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=ogf_google_fonts' ) ); ?>" class="ogf-action-btn">
								<?php esc_html_e( 'Customize Fonts', 'olympus-google-fonts' ); ?>
							</a>
						</div>
					<?php else : ?>
						<?php foreach ( $fonts as $font_id => $font ) : ?>
							<?php $this->render_font_row( $font_id, $font, $is_pro ); ?>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a single font row.
	 *
	 * @param string $font_id Font ID.
	 * @param array  $font    Font data.
	 * @param bool   $is_pro  Whether Pro is active.
	 */
	private function render_font_row( $font_id, $font, $is_pro ) {
		$badge_class = 'ogf-badge-google';
		$badge_label = __( 'Google', 'olympus-google-fonts' );
		if ( 'custom' === $font['type'] ) {
			$badge_class = 'ogf-badge-custom';
			$badge_label = __( 'Custom', 'olympus-google-fonts' );
		} elseif ( 'adobe' === $font['type'] ) {
			$badge_class = 'ogf-badge-adobe';
			$badge_label = __( 'Adobe', 'olympus-google-fonts' );
		} elseif ( 'system' === $font['type'] ) {
			$badge_class = 'ogf-badge-system';
			$badge_label = __( 'System', 'olympus-google-fonts' );
		}

		// Badge reports every variant that loads; the detail row lists upright weights.
		$weight_count         = is_array( $font['weights'] ) ? count( $font['weights'] ) : 0;
		$weight_labels        = $this->upright_weight_labels( is_array( $font['weights'] ) ? array_keys( $font['weights'] ) : array() );
		$host_toggle_disabled = ! $is_pro && 'custom' !== $font['type'];
		$is_custom_hosted     = 'custom' === $font['type'];
		?>
		<div class="ogf-font-row">
			<div class="ogf-font-row-header">
				<?php $this->icon( 'chevron-right', 14, 2.5, 'ogf-font-chevron' ); ?>
				<span class="ogf-font-name"><?php echo esc_html( $font['name'] ); ?></span>
				<span class="ogf-font-type-badge <?php echo esc_attr( $badge_class ); ?>"><?php echo esc_html( $badge_label ); ?></span>
				<div class="ogf-font-meta">
					<?php if ( $weight_count > 0 ) : ?>
						<span class="ogf-font-size-badge">
							<?php
							/* translators: %d: number of weights */
							printf( esc_html( _n( '%d weight', '%d weights', $weight_count, 'olympus-google-fonts' ) ), esc_html( $weight_count ) );
							?>
						</span>
					<?php endif; ?>
					<div class="ogf-font-toggles">
						<?php if ( $is_custom_hosted ) : ?>
							<div class="ogf-font-toggle-group">
								<span class="ogf-font-toggle-label"><?php esc_html_e( 'Host', 'olympus-google-fonts' ); ?></span>
								<button class="ogf-toggle-sm on" style="opacity:1;cursor:default" title="<?php esc_attr_e( 'Custom fonts are always locally hosted', 'olympus-google-fonts' ); ?>"></button>
							</div>
						<?php else : ?>
							<div class="ogf-font-toggle-group" <?php echo $host_toggle_disabled ? 'data-pro-toggle data-feature="' . esc_attr__( 'Host Fonts Locally', 'olympus-google-fonts' ) . '" data-desc="' . esc_attr__( 'Download and serve this font from your own domain instead of the CDN. Improves privacy compliance (GDPR/DSGVO) and can reduce latency.', 'olympus-google-fonts' ) . '"' : ''; ?>>
								<span class="ogf-font-toggle-label"><?php esc_html_e( 'Host', 'olympus-google-fonts' ); ?></span>
								<button class="ogf-toggle-sm<?php echo $host_toggle_disabled ? ' disabled' : ''; ?><?php echo $is_pro && get_theme_mod( 'ogf_host_locally', false ) ? ' on' : ''; ?>" data-font="<?php echo esc_attr( $font_id ); ?>" data-setting="host"></button>
							</div>
						<?php endif; ?>
						<div class="ogf-font-toggle-group" <?php echo ! $is_pro ? 'data-pro-toggle data-feature="' . esc_attr__( 'Preload Font', 'olympus-google-fonts' ) . '" data-desc="' . esc_attr__( 'Add a preload resource hint for this font so the browser begins downloading it before it\'s needed, reducing layout shift.', 'olympus-google-fonts' ) . '"' : ''; ?>>
							<span class="ogf-font-toggle-label"><?php esc_html_e( 'Preload', 'olympus-google-fonts' ); ?></span>
							<button class="ogf-toggle-sm<?php echo ! $is_pro ? ' disabled' : ''; ?><?php echo $is_pro && get_theme_mod( 'ogf_preloading', false ) ? ' on' : ''; ?>" data-font="<?php echo esc_attr( $font_id ); ?>" data-setting="preload"></button>
						</div>
					</div>
				</div>
			</div>
			<div class="ogf-font-details">
				<div class="ogf-font-detail-grid">
					<?php if ( ! empty( $weight_labels ) ) : ?>
						<span class="ogf-font-detail-label"><?php esc_html_e( 'Weights', 'olympus-google-fonts' ); ?></span>
						<div class="ogf-weight-pills">
							<?php foreach ( $weight_labels as $label ) : ?>
								<span class="ogf-weight-pill"><?php echo esc_html( $label ); ?></span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $font['subsets'] ) ) : ?>
						<span class="ogf-font-detail-label"><?php esc_html_e( 'Subsets', 'olympus-google-fonts' ); ?></span>
						<span class="ogf-font-detail-value"><?php echo esc_html( implode( ', ', array_values( $font['subsets'] ) ) ); ?></span>
					<?php endif; ?>

					<span class="ogf-font-detail-label"><?php esc_html_e( 'Used by', 'olympus-google-fonts' ); ?></span>
					<div class="ogf-element-pills">
						<?php foreach ( $font['elements'] as $el ) : ?>
							<span class="ogf-element-pill"><?php echo esc_html( $el ); ?></span>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Font Loading tab.
	 */
	private function render_tab_font_loading() {
		$fonts  = $this->get_active_fonts();
		$is_pro = defined( 'OGF_PRO' );

		$google_fonts = array_filter(
			$fonts,
			function ( $f ) {
				return 'google' === $f['type'];
			}
		);
		?>
		<div class="ogf-tab-panel" id="ogf-tab-font-loading">
			<?php $this->render_panel_header( __( 'Font Loading', 'olympus-google-fonts' ), __( 'Control which weights and subsets each font downloads. Fewer variants means a lighter page.', 'olympus-google-fonts' ) ); ?>
			<div class="ogf-card" style="margin-bottom:16px">
				<div class="ogf-card-body" style="display:flex;align-items:center;justify-content:space-between">
					<div>
						<div class="ogf-setting-label">
							<?php esc_html_e( 'Selective Font Loading', 'olympus-google-fonts' ); ?>
							<?php if ( ! $is_pro ) : ?>
								<span class="ogf-pro-badge"><?php esc_html_e( 'PRO', 'olympus-google-fonts' ); ?></span>
							<?php endif; ?>
						</div>
						<div class="ogf-setting-desc" style="margin-top:4px">
							<?php esc_html_e( 'Choose exactly which weights and subsets to load per font. Reduce page weight by disabling unused variants.', 'olympus-google-fonts' ); ?>
						</div>
					</div>
				</div>
			</div>

			<?php if ( empty( $google_fonts ) ) : ?>
				<div class="ogf-card">
					<div class="ogf-card-body">
						<p><?php esc_html_e( 'No Google Fonts are currently loaded. Add fonts via the Customizer to configure weight loading.', 'olympus-google-fonts' ); ?></p>
					</div>
				</div>
			<?php else : ?>
				<?php foreach ( $google_fonts as $font_id => $font ) : ?>
					<?php $this->render_font_loading_card( $font_id, $font, $is_pro ); ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render a font loading card.
	 *
	 * @param string $font_id Font ID.
	 * @param array  $font    Font data.
	 * @param bool   $is_pro  Whether Pro is active.
	 */
	private function render_font_loading_card( $font_id, $font, $is_pro ) {
		$selected_weights = get_theme_mod( $font_id . '_weights', false );
		$toggle_class     = $is_pro ? 'ogf-toggle-xs on' : 'ogf-toggle-xs on disabled';
		?>
		<div class="ogf-fl-card">
			<div class="ogf-fl-header">
				<span class="ogf-fl-font-name"><?php echo esc_html( $font['name'] ); ?></span>
				<span class="ogf-font-type-badge ogf-badge-google"><?php esc_html_e( 'Google', 'olympus-google-fonts' ); ?></span>
			</div>

			<?php if ( ! empty( $font['weights'] ) ) : ?>
				<div class="ogf-fl-section-title"><?php esc_html_e( 'Weights', 'olympus-google-fonts' ); ?></div>
				<div class="ogf-fl-grid">
					<?php foreach ( array_keys( $font['weights'] ) as $key ) : ?>
						<?php
						$is_selected = ! $selected_weights || ( is_array( $selected_weights ) && in_array( $key, $selected_weights, true ) );
						$on_class    = $is_selected ? ' on' : '';
						?>
						<div class="ogf-fl-grid-row">
							<button class="ogf-toggle-xs<?php echo esc_attr( $on_class ); ?><?php echo ! $is_pro ? ' disabled' : ''; ?>"
								data-font="<?php echo esc_attr( $font_id ); ?>"
								data-weight="<?php echo esc_attr( $key ); ?>"></button>
							<span class="ogf-fl-grid-label"><?php echo esc_html( $key . ' ' . $this->weight_label( $key ) ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $font['subsets'] ) ) : ?>
				<div class="ogf-fl-section-title"><?php esc_html_e( 'Subsets', 'olympus-google-fonts' ); ?></div>
				<div class="ogf-fl-grid">
					<?php foreach ( $font['subsets'] as $subset ) : ?>
						<div class="ogf-fl-grid-row">
							<button class="ogf-toggle-xs on<?php echo ! $is_pro ? ' disabled' : ''; ?>"
								data-font="<?php echo esc_attr( $font_id ); ?>"
								data-subset="<?php echo esc_attr( $subset ); ?>"></button>
							<span class="ogf-fl-grid-label"><?php echo esc_html( ucfirst( str_replace( '-', ' ', $subset ) ) ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="ogf-fl-note">
				<?php
				$weight_count = count( $font['weights'] );
				$subset_count = count( $font['subsets'] );
				/* translators: 1: number of weight variants, 2: number of subsets */
				printf( esc_html__( '%1$d weight variants / %2$d subsets loaded', 'olympus-google-fonts' ), intval( $weight_count ), intval( $subset_count ) );
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Upload Fonts tab.
	 */
	private function render_tab_upload() {
		$terms = get_terms(
			array(
				'taxonomy'   => OGF_Fonts_Taxonomy::$taxonomy_slug,
				'hide_empty' => false,
			)
		);

		if ( is_wp_error( $terms ) ) {
			$terms = array();
		}
		?>
		<div class="ogf-tab-panel" id="ogf-tab-upload">
			<?php $this->render_panel_header( __( 'Upload Fonts', 'olympus-google-fonts' ), __( 'Add your own font files. Uploaded fonts are hosted on your domain and appear alongside Google Fonts.', 'olympus-google-fonts' ) ); ?>
			<div class="ogf-card">
				<div class="ogf-card-header"><?php esc_html_e( 'Upload a Custom Font', 'olympus-google-fonts' ); ?></div>
				<div class="ogf-card-body">
					<p style="margin-bottom:18px">
						<?php esc_html_e( 'Add a font file for each weight and style you need. Uploaded fonts are hosted on your domain and appear in the Customizer alongside Google Fonts.', 'olympus-google-fonts' ); ?>
					</p>
					<?php $this->render_custom_font_form( 0, null ); ?>
				</div>
			</div>

			<div class="ogf-card">
				<div class="ogf-card-header">
					<?php esc_html_e( 'Custom Fonts', 'olympus-google-fonts' ); ?>
					<span class="ogf-card-header-meta">
						<?php
						/* translators: %d: number of uploaded font variants */
						printf( esc_html( _n( '%d variant', '%d variants', count( $terms ), 'olympus-google-fonts' ) ), esc_html( count( $terms ) ) );
						?>
					</span>
				</div>
				<div>
					<?php if ( empty( $terms ) ) : ?>
						<div class="ogf-card-body">
							<p><?php esc_html_e( 'No custom fonts uploaded yet.', 'olympus-google-fonts' ); ?></p>
						</div>
					<?php else : ?>
						<?php foreach ( $terms as $term ) : ?>
							<?php $this->render_custom_font_row( $term ); ?>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a single uploaded font row with its inline edit form.
	 *
	 * @param WP_Term $term The custom font term.
	 */
	private function render_custom_font_row( $term ) {
		$data     = OGF_Fonts_Taxonomy::get_font_data( $term->term_id );
		$family   = ! empty( $data['family'] ) ? $data['family'] : $term->name;
		$formats  = array();
		$variants = array();

		foreach ( array( 'woff2', 'woff', 'ttf', 'otf' ) as $format ) {
			if ( ! empty( $data[ $format ] ) ) {
				$formats[] = '.' . $format;
			}
		}

		if ( ! empty( $data['weight'] ) ) {
			$variants[] = $data['weight'];
		}
		if ( ! empty( $data['style'] ) ) {
			$variants[] = $data['style'];
		}

		$meta = array_merge( $variants, $formats );
		?>
		<div class="ogf-uploaded-font-row" data-term-id="<?php echo esc_attr( $term->term_id ); ?>">
			<div class="ogf-cf-info">
				<div class="ogf-cf-name"><?php echo esc_html( $term->name ); ?></div>
				<div class="ogf-cf-meta">
					<?php
					echo esc_html( $family );
					if ( ! empty( $meta ) ) {
						echo ' &middot; ' . esc_html( implode( ' &middot; ', $meta ) );
					}
					if ( empty( $formats ) ) {
						echo ' &middot; ' . esc_html__( 'No font file', 'olympus-google-fonts' );
					}
					?>
				</div>
			</div>
			<div class="ogf-cf-actions">
				<button type="button" class="ogf-cf-btn ogf-cf-edit"><?php esc_html_e( 'Edit', 'olympus-google-fonts' ); ?></button>
				<button type="button" class="ogf-cf-btn ogf-cf-delete"><?php esc_html_e( 'Delete', 'olympus-google-fonts' ); ?></button>
			</div>
		</div>
		<div class="ogf-cf-edit-panel" data-term-id="<?php echo esc_attr( $term->term_id ); ?>">
			<?php $this->render_custom_font_form( $term->term_id, $term ); ?>
		</div>
		<?php
	}

	/**
	 * Render the add/edit form for a custom font.
	 *
	 * @param int          $term_id Term ID, or 0 for the "add new" form.
	 * @param WP_Term|null $term    The term being edited, if any.
	 */
	private function render_custom_font_form( $term_id, $term = null ) {
		$data   = $term_id ? OGF_Fonts_Taxonomy::get_font_data( $term_id ) : OGF_Fonts_Taxonomy::get_font_data( 0 );
		$name   = $term ? $term->name : '';
		$is_new = ! $term_id;
		$is_pro = defined( 'OGF_PRO' );

		$weights = array(
			''    => __( 'All', 'olympus-google-fonts' ),
			'100' => __( 'Thin (100)', 'olympus-google-fonts' ),
			'200' => __( 'Extra Light (200)', 'olympus-google-fonts' ),
			'300' => __( 'Light (300)', 'olympus-google-fonts' ),
			'400' => __( 'Normal (400)', 'olympus-google-fonts' ),
			'500' => __( 'Medium (500)', 'olympus-google-fonts' ),
			'600' => __( 'Semi Bold (600)', 'olympus-google-fonts' ),
			'700' => __( 'Bold (700)', 'olympus-google-fonts' ),
			'800' => __( 'Extra Bold (800)', 'olympus-google-fonts' ),
			'900' => __( 'Black (900)', 'olympus-google-fonts' ),
		);

		$styles = array(
			''        => __( 'All', 'olympus-google-fonts' ),
			'normal'  => __( 'Normal', 'olympus-google-fonts' ),
			'italic'  => __( 'Italic', 'olympus-google-fonts' ),
			'oblique' => __( 'Oblique', 'olympus-google-fonts' ),
		);

		$files = array(
			'woff2' => __( 'WOFF2 file', 'olympus-google-fonts' ),
			'woff'  => __( 'WOFF file', 'olympus-google-fonts' ),
			'ttf'   => __( 'TTF file', 'olympus-google-fonts' ),
			'otf'   => __( 'OTF file', 'olympus-google-fonts' ),
		);
		?>
		<div class="ogf-cf-form" data-term-id="<?php echo esc_attr( $term_id ); ?>">
			<div class="ogf-field-grid">
				<div class="ogf-field">
					<label class="ogf-field-label" for="ogf-cf-name-<?php echo esc_attr( $term_id ); ?>"><?php esc_html_e( 'Variant name', 'olympus-google-fonts' ); ?></label>
					<input type="text" class="ogf-input" id="ogf-cf-name-<?php echo esc_attr( $term_id ); ?>" data-field="name" value="<?php echo esc_attr( $name ); ?>" placeholder="<?php esc_attr_e( 'Brand Sans Bold', 'olympus-google-fonts' ); ?>">
					<p class="ogf-field-hint"><?php esc_html_e( 'A unique name to describe this variant.', 'olympus-google-fonts' ); ?></p>
				</div>
				<div class="ogf-field">
					<label class="ogf-field-label" for="ogf-cf-family-<?php echo esc_attr( $term_id ); ?>"><?php esc_html_e( 'Font family', 'olympus-google-fonts' ); ?></label>
					<input type="text" class="ogf-input" id="ogf-cf-family-<?php echo esc_attr( $term_id ); ?>" data-field="family" value="<?php echo esc_attr( $data['family'] ); ?>" placeholder="<?php esc_attr_e( 'Brand Sans', 'olympus-google-fonts' ); ?>">
					<p class="ogf-field-hint"><?php esc_html_e( 'Variants that share a family name are grouped together.', 'olympus-google-fonts' ); ?></p>
				</div>
			</div>

			<div class="ogf-field-grid">
				<div class="ogf-field">
					<label class="ogf-field-label" for="ogf-cf-weight-<?php echo esc_attr( $term_id ); ?>"><?php esc_html_e( 'Font weight', 'olympus-google-fonts' ); ?></label>
					<select class="ogf-setting-select ogf-field-select" id="ogf-cf-weight-<?php echo esc_attr( $term_id ); ?>" data-field="weight">
						<?php foreach ( $weights as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $data['weight'], $value ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="ogf-field">
					<label class="ogf-field-label" for="ogf-cf-style-<?php echo esc_attr( $term_id ); ?>"><?php esc_html_e( 'Font style', 'olympus-google-fonts' ); ?></label>
					<select class="ogf-setting-select ogf-field-select" id="ogf-cf-style-<?php echo esc_attr( $term_id ); ?>" data-field="style">
						<?php foreach ( $styles as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $data['style'], $value ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

			<div class="ogf-fl-section-title"><?php esc_html_e( 'Font files', 'olympus-google-fonts' ); ?></div>
			<?php foreach ( $files as $format => $label ) : ?>
				<div class="ogf-field">
					<label class="ogf-field-label" for="ogf-cf-<?php echo esc_attr( $format . '-' . $term_id ); ?>"><?php echo esc_html( $label ); ?></label>
					<div class="ogf-file-row">
						<input type="text" class="ogf-input" id="ogf-cf-<?php echo esc_attr( $format . '-' . $term_id ); ?>" data-field="<?php echo esc_attr( $format ); ?>" value="<?php echo esc_attr( $data[ $format ] ); ?>" placeholder="https://">
						<button type="button" class="ogf-btn-outline ogf-cf-upload"><?php esc_html_e( 'Upload', 'olympus-google-fonts' ); ?></button>
					</div>
				</div>
			<?php endforeach; ?>

			<?php if ( $is_pro ) : ?>
				<div class="ogf-field ogf-field-inline">
					<button type="button" class="ogf-toggle-sm<?php echo ! empty( $data['preload'] ) ? ' on' : ''; ?>" data-field="preload"></button>
					<label class="ogf-field-label"><?php esc_html_e( 'Preload this font', 'olympus-google-fonts' ); ?></label>
				</div>
			<?php endif; ?>

			<div class="ogf-cf-form-actions">
				<button type="button" class="ogf-btn-pro ogf-cf-save">
					<?php echo $is_new ? esc_html__( 'Add Font', 'olympus-google-fonts' ) : esc_html__( 'Save Changes', 'olympus-google-fonts' ); ?>
				</button>
				<?php if ( ! $is_new ) : ?>
					<button type="button" class="ogf-btn-modal-cancel ogf-cf-cancel"><?php esc_html_e( 'Cancel', 'olympus-google-fonts' ); ?></button>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Adobe Fonts tab.
	 */
	private function render_tab_adobe() {
		$typekit_settings = get_option( 'fp-typekit', array() );
		$api_key          = is_array( $typekit_settings ) && isset( $typekit_settings['api_key'] ) ? $typekit_settings['api_key'] : '';
		$kits             = get_option( 'fp-typekit-data', false );
		$has_kits         = is_array( $kits ) && ! empty( $kits );
		?>
		<div class="ogf-tab-panel" id="ogf-tab-adobe">
			<?php $this->render_panel_header( __( 'Adobe Fonts', 'olympus-google-fonts' ), __( 'Connect an Adobe Fonts (Typekit) account to use the families from your web projects.', 'olympus-google-fonts' ) ); ?>
			<div class="ogf-card">
				<div class="ogf-card-header"><?php esc_html_e( 'Adobe Fonts Configuration', 'olympus-google-fonts' ); ?></div>
				<div class="ogf-card-body">
					<p style="margin-bottom:14px">
						<?php esc_html_e( 'Connect your Adobe Fonts (Typekit) account to use your web project fonts.', 'olympus-google-fonts' ); ?>
						<a class="ogf-tk-link" target="_blank" href="https://fonts.adobe.com/account/tokens"><?php esc_html_e( 'Find your API token', 'olympus-google-fonts' ); ?></a>
					</p>
					<div class="ogf-adobe-input-group">
						<input type="text" id="ogf-typekit-key" value="<?php echo esc_attr( $api_key ); ?>" placeholder="<?php esc_attr_e( 'Paste your Adobe Fonts API token...', 'olympus-google-fonts' ); ?>" autocomplete="off" spellcheck="false">
						<button type="button" class="ogf-btn-pro" id="ogf-typekit-save"><?php esc_html_e( 'Save Token', 'olympus-google-fonts' ); ?></button>
						<?php if ( $api_key ) : ?>
							<button type="button" class="ogf-btn-outline" id="ogf-typekit-refresh"><?php esc_html_e( 'Refresh Fonts', 'olympus-google-fonts' ); ?></button>
						<?php endif; ?>
					</div>
					<?php if ( $api_key && ! $has_kits ) : ?>
						<p class="ogf-fl-note">
							<?php
							if ( is_array( $kits ) ) {
								esc_html_e( 'No kits were found for this token.', 'olympus-google-fonts' );
							} else {
								esc_html_e( 'There is a problem connecting to the API. Please check your API token.', 'olympus-google-fonts' );
							}
							?>
						</p>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( $has_kits ) : ?>
				<div class="ogf-card">
					<div class="ogf-card-header">
						<?php esc_html_e( 'Your Kits', 'olympus-google-fonts' ); ?>
						<span class="ogf-card-header-meta">
							<?php
							/* translators: %d: number of Adobe Fonts kits */
							printf( esc_html( _n( '%d kit found', '%d kits found', count( $kits ), 'olympus-google-fonts' ) ), esc_html( count( $kits ) ) );
							?>
						</span>
					</div>
					<div class="ogf-card-body">
						<?php foreach ( $kits as $kit_id => $kit ) : ?>
							<?php
							$is_enabled = ! empty( $kit['enabled'] );
							$families   = array();
							if ( isset( $kit['families'] ) && is_array( $kit['families'] ) ) {
								foreach ( $kit['families'] as $family ) {
									$families[] = $family['label'];
								}
							}
							?>
							<div class="ogf-adobe-kit-card">
								<div>
									<div class="ogf-adobe-kit-header"><?php echo esc_html( $kit_id ); ?></div>
									<div class="ogf-adobe-kit-fonts">
										<?php
										if ( ! empty( $families ) ) {
											echo esc_html( implode( ', ', $families ) );
										} else {
											esc_html_e( 'No font families in this kit', 'olympus-google-fonts' );
										}
										?>
									</div>
								</div>
								<div class="ogf-tk-actions">
									<span class="ogf-kit-status <?php echo $is_enabled ? 'active' : 'inactive'; ?>">
										<?php echo $is_enabled ? esc_html__( 'Active', 'olympus-google-fonts' ) : esc_html__( 'Inactive', 'olympus-google-fonts' ); ?>
									</span>
									<button type="button" class="ogf-btn-outline ogf-tk-toggle" data-kit="<?php echo esc_attr( $kit_id ); ?>" data-enabled="<?php echo $is_enabled ? '1' : '0'; ?>">
										<?php echo $is_enabled ? esc_html__( 'Disable', 'olympus-google-fonts' ) : esc_html__( 'Enable', 'olympus-google-fonts' ); ?>
									</button>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render Settings tab.
	 */
	private function render_tab_settings() {
		$is_pro       = defined( 'OGF_PRO' );
		$font_display = get_theme_mod( 'ogf_font_display', 'swap' );
		?>
		<div class="ogf-tab-panel" id="ogf-tab-settings">
			<?php $this->render_panel_header( __( 'Settings', 'olympus-google-fonts' ), __( 'How the plugin loads fonts and how strongly it overrides your theme.', 'olympus-google-fonts' ) ); ?>
			<div class="ogf-card">
				<div class="ogf-section-label"><?php esc_html_e( 'Performance & Privacy', 'olympus-google-fonts' ); ?></div>

				<div class="ogf-setting-row">
					<div class="ogf-setting-info">
						<div class="ogf-setting-label">
							<?php esc_html_e( 'Remove External Fonts', 'olympus-google-fonts' ); ?>
							<?php if ( ! $is_pro ) : ?>
								<span class="ogf-pro-badge"><?php esc_html_e( 'PRO', 'olympus-google-fonts' ); ?></span>
							<?php endif; ?>
						</div>
						<div class="ogf-setting-desc"><?php esc_html_e( 'Remove Google Fonts loaded by other plugins and your theme.', 'olympus-google-fonts' ); ?></div>
					</div>
					<div class="ogf-setting-control">
						<?php if ( $is_pro ) : ?>
							<button class="ogf-toggle<?php echo get_theme_mod( 'ogf_removal', false ) ? ' on' : ''; ?>" data-setting="ogf_removal"></button>
						<?php else : ?>
							<button class="ogf-toggle disabled" data-setting="ogf_removal" data-pro-feature data-feature="<?php esc_attr_e( 'Remove External Fonts', 'olympus-google-fonts' ); ?>" data-desc="<?php esc_attr_e( 'Remove Google Fonts loaded by other plugins and your theme to reduce external requests and improve page speed.', 'olympus-google-fonts' ); ?>"></button>
						<?php endif; ?>
					</div>
				</div>

				<div class="ogf-setting-row">
					<div class="ogf-setting-info">
						<div class="ogf-setting-label">
							<?php esc_html_e( 'Rewrite External Fonts', 'olympus-google-fonts' ); ?>
							<?php if ( ! $is_pro ) : ?>
								<span class="ogf-pro-badge"><?php esc_html_e( 'PRO', 'olympus-google-fonts' ); ?></span>
							<?php endif; ?>
						</div>
						<div class="ogf-setting-desc"><?php esc_html_e( 'Convert fonts from other plugins and themes to be locally hosted.', 'olympus-google-fonts' ); ?></div>
					</div>
					<div class="ogf-setting-control">
						<?php if ( $is_pro ) : ?>
							<button class="ogf-toggle<?php echo get_theme_mod( 'ogf_rewrite', false ) ? ' on' : ''; ?>" data-setting="ogf_rewrite"></button>
						<?php else : ?>
							<button class="ogf-toggle disabled" data-setting="ogf_rewrite" data-pro-feature data-feature="<?php esc_attr_e( 'Rewrite External Fonts', 'olympus-google-fonts' ); ?>" data-desc="<?php esc_attr_e( 'Automatically rewrite Google Fonts URLs from your theme and plugins to serve from your domain, improving privacy and performance.', 'olympus-google-fonts' ); ?>"></button>
						<?php endif; ?>
					</div>
				</div>

				<div class="ogf-section-label"><?php esc_html_e( 'General', 'olympus-google-fonts' ); ?></div>

				<div class="ogf-setting-row">
					<div class="ogf-setting-info">
						<div class="ogf-setting-label"><?php esc_html_e( 'Force Styles', 'olympus-google-fonts' ); ?></div>
						<div class="ogf-setting-desc"><?php esc_html_e( 'Add !important to all font styles. Useful if your theme overrides the plugin.', 'olympus-google-fonts' ); ?></div>
					</div>
					<div class="ogf-setting-control">
						<button class="ogf-toggle<?php echo get_theme_mod( 'ogf_force_styles', false ) ? ' on' : ''; ?>" data-setting="ogf_force_styles"></button>
					</div>
				</div>

				<div class="ogf-setting-row">
					<div class="ogf-setting-info">
						<div class="ogf-setting-label"><?php esc_html_e( 'Disable Editor Controls', 'olympus-google-fonts' ); ?></div>
						<div class="ogf-setting-desc"><?php esc_html_e( 'Remove font controls from the Gutenberg and Classic editors.', 'olympus-google-fonts' ); ?></div>
					</div>
					<div class="ogf-setting-control">
						<button class="ogf-toggle<?php echo get_theme_mod( 'ogf_disable_post_level_controls', false ) ? ' on' : ''; ?>" data-setting="ogf_disable_post_level_controls"></button>
					</div>
				</div>

				<div class="ogf-setting-row">
					<div class="ogf-setting-info">
						<div class="ogf-setting-label"><?php esc_html_e( 'Use px Font Sizes', 'olympus-google-fonts' ); ?></div>
						<div class="ogf-setting-desc"><?php esc_html_e( 'Use pixel values for font sizes instead of em units.', 'olympus-google-fonts' ); ?></div>
					</div>
					<div class="ogf-setting-control">
						<button class="ogf-toggle<?php echo 'true' === get_theme_mod( 'ogf_use_px', 'true' ) ? ' on' : ''; ?>" data-setting="ogf_use_px"></button>
					</div>
				</div>

				<div class="ogf-setting-row">
					<div class="ogf-setting-info">
						<div class="ogf-setting-label"><?php esc_html_e( 'Font Display', 'olympus-google-fonts' ); ?></div>
						<div class="ogf-setting-desc"><?php esc_html_e( 'Controls the font-display CSS property for all loaded fonts.', 'olympus-google-fonts' ); ?></div>
					</div>
					<div class="ogf-setting-control">
						<select class="ogf-setting-select" data-setting="ogf_font_display">
							<option value="swap" <?php selected( $font_display, 'swap' ); ?>><?php esc_html_e( 'Swap', 'olympus-google-fonts' ); ?></option>
							<option value="block" <?php selected( $font_display, 'block' ); ?>><?php esc_html_e( 'Block', 'olympus-google-fonts' ); ?></option>
							<option value="fallback" <?php selected( $font_display, 'fallback' ); ?>><?php esc_html_e( 'Fallback', 'olympus-google-fonts' ); ?></option>
							<option value="optional" <?php selected( $font_display, 'optional' ); ?>><?php esc_html_e( 'Optional', 'olympus-google-fonts' ); ?></option>
						</select>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Diagnostics tab.
	 */
	private function render_tab_diagnostics() {
		$fonts     = $this->get_active_fonts();
		$is_pro    = defined( 'OGF_PRO' );
		$theme     = wp_get_theme();
		$ogf_fonts = OGF_Fonts::get_instance();
		$has_cache = false;

		if ( $ogf_fonts->has_google_fonts() ) {
			$url       = $ogf_fonts->build_url();
			$url_to_id = md5( $url );
			$has_cache = false !== get_transient( 'ogf_external_font_css_' . $url_to_id );
		}

		$google_count = 0;
		$custom_count = 0;
		$adobe_count  = 0;
		foreach ( $fonts as $font ) {
			if ( 'google' === $font['type'] ) {
				++$google_count;
			} elseif ( 'custom' === $font['type'] ) {
				++$custom_count;
			} elseif ( 'adobe' === $font['type'] ) {
				++$adobe_count;
			}
		}
		?>
		<div class="ogf-tab-panel" id="ogf-tab-diagnostics">
			<?php $this->render_panel_header( __( 'Diagnostics', 'olympus-google-fonts' ), __( 'Current configuration, environment details, and tools for clearing cached font data.', 'olympus-google-fonts' ) ); ?>
			<div class="ogf-diag-grid">
				<div class="ogf-card">
					<div class="ogf-card-header"><?php esc_html_e( 'Configuration', 'olympus-google-fonts' ); ?></div>
					<div>
						<ul class="ogf-diag-list">
							<li class="ogf-diag-item">
								<span class="ogf-diag-key"><?php esc_html_e( 'Loading Method', 'olympus-google-fonts' ); ?></span>
								<span class="ogf-diag-val">
									<?php
									if ( $is_pro && get_theme_mod( 'ogf_host_locally', false ) ) {
										esc_html_e( 'Local', 'olympus-google-fonts' );
									} else {
										esc_html_e( 'Google CDN', 'olympus-google-fonts' );
									}
									?>
								</span>
							</li>
							<li class="ogf-diag-item">
								<span class="ogf-diag-key"><?php esc_html_e( 'Font Display', 'olympus-google-fonts' ); ?></span>
								<span class="ogf-diag-val"><?php echo esc_html( get_theme_mod( 'ogf_font_display', 'swap' ) ); ?></span>
							</li>
							<li class="ogf-diag-item">
								<span class="ogf-diag-key"><?php esc_html_e( 'Force Styles', 'olympus-google-fonts' ); ?></span>
								<?php if ( get_theme_mod( 'ogf_force_styles', false ) ) : ?>
									<span class="ogf-diag-status ok"><?php esc_html_e( 'On', 'olympus-google-fonts' ); ?></span>
								<?php else : ?>
									<span class="ogf-diag-status warn"><?php esc_html_e( 'Off', 'olympus-google-fonts' ); ?></span>
								<?php endif; ?>
							</li>
							<li class="ogf-diag-item">
								<span class="ogf-diag-key"><?php esc_html_e( 'Google Fonts', 'olympus-google-fonts' ); ?></span>
								<span class="ogf-diag-val"><?php echo esc_html( $google_count ); ?></span>
							</li>
							<li class="ogf-diag-item">
								<span class="ogf-diag-key"><?php esc_html_e( 'Custom Fonts', 'olympus-google-fonts' ); ?></span>
								<span class="ogf-diag-val"><?php echo esc_html( $custom_count ); ?></span>
							</li>
							<li class="ogf-diag-item">
								<span class="ogf-diag-key"><?php esc_html_e( 'Adobe Fonts', 'olympus-google-fonts' ); ?></span>
								<span class="ogf-diag-val"><?php echo esc_html( $adobe_count ); ?></span>
							</li>
							<li class="ogf-diag-item">
								<span class="ogf-diag-key"><?php esc_html_e( 'Cache Status', 'olympus-google-fonts' ); ?></span>
								<?php if ( $has_cache ) : ?>
									<span class="ogf-diag-status ok"><?php esc_html_e( 'Active', 'olympus-google-fonts' ); ?></span>
								<?php else : ?>
									<span class="ogf-diag-status warn"><?php esc_html_e( 'Empty', 'olympus-google-fonts' ); ?></span>
								<?php endif; ?>
							</li>
						</ul>
					</div>
				</div>

				<div class="ogf-card">
					<div class="ogf-card-header"><?php esc_html_e( 'System', 'olympus-google-fonts' ); ?></div>
					<div>
						<ul class="ogf-diag-list">
							<li class="ogf-diag-item">
								<span class="ogf-diag-key"><?php esc_html_e( 'Plugin Version', 'olympus-google-fonts' ); ?></span>
								<span class="ogf-diag-val"><?php echo esc_html( OGF_VERSION ); ?></span>
							</li>
							<li class="ogf-diag-item">
								<span class="ogf-diag-key"><?php esc_html_e( 'Pro Status', 'olympus-google-fonts' ); ?></span>
								<?php if ( $is_pro ) : ?>
									<span class="ogf-diag-status ok"><?php esc_html_e( 'Active', 'olympus-google-fonts' ); ?></span>
								<?php else : ?>
									<span class="ogf-diag-status warn"><?php esc_html_e( 'Not Installed', 'olympus-google-fonts' ); ?></span>
								<?php endif; ?>
							</li>
							<li class="ogf-diag-item">
								<span class="ogf-diag-key"><?php esc_html_e( 'WordPress', 'olympus-google-fonts' ); ?></span>
								<span class="ogf-diag-val"><?php echo esc_html( get_bloginfo( 'version' ) ); ?></span>
							</li>
							<li class="ogf-diag-item">
								<span class="ogf-diag-key"><?php esc_html_e( 'PHP', 'olympus-google-fonts' ); ?></span>
								<span class="ogf-diag-val"><?php echo esc_html( PHP_VERSION ); ?></span>
							</li>
							<li class="ogf-diag-item">
								<span class="ogf-diag-key"><?php esc_html_e( 'Active Theme', 'olympus-google-fonts' ); ?></span>
								<span class="ogf-diag-val"><?php echo esc_html( $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' ) ); ?></span>
							</li>
						</ul>
					</div>
				</div>
			</div>

			<div class="ogf-card" style="margin-top:16px">
				<div class="ogf-card-header"><?php esc_html_e( 'Actions', 'olympus-google-fonts' ); ?></div>
				<div class="ogf-card-body">
					<p style="margin-bottom:14px"><?php esc_html_e( 'Copy the report above to paste into a support ticket. Cache stores a copy of the Google Fonts CSS locally, and reset removes all font assignments made by the plugin.', 'olympus-google-fonts' ); ?></p>
					<div class="ogf-diag-actions">
						<button id="ogf-copy-diagnostics" class="ogf-btn-outline">
							<?php $this->icon( 'copy', 14 ); ?>
							<?php esc_html_e( 'Copy Diagnostics', 'olympus-google-fonts' ); ?>
						</button>
						<button id="ogf-clear-cache" class="ogf-btn-outline">
							<?php $this->icon( 'globe', 14 ); ?>
							<?php esc_html_e( 'Clear Font Cache', 'olympus-google-fonts' ); ?>
						</button>
						<button id="ogf-reset-fonts" class="ogf-btn-outline ogf-btn-danger">
							<?php $this->icon( 'trash', 14 ); ?>
							<?php esc_html_e( 'Reset All Fonts', 'olympus-google-fonts' ); ?>
						</button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the upgrade modal.
	 */
	private function render_upgrade_modal() {
		?>
		<div id="ogf-modal" class="ogf-modal">
			<div class="ogf-modal-card" role="dialog" aria-modal="true" aria-labelledby="ogf-modal-title">
				<div class="ogf-modal-header">
					<h2 id="ogf-modal-title"></h2>
					<button class="ogf-modal-close" aria-label="<?php esc_attr_e( 'Close', 'olympus-google-fonts' ); ?>">
						<?php $this->icon( 'close', 20 ); ?>
					</button>
				</div>
				<div class="ogf-modal-body">
					<p id="ogf-modal-desc"></p>
					<ul class="ogf-modal-benefits">
						<li><?php $this->icon( 'check', 14, 2.5 ); ?><?php esc_html_e( 'One-click activation', 'olympus-google-fonts' ); ?></li>
						<li><?php $this->icon( 'check', 14, 2.5 ); ?><?php esc_html_e( 'Works with all themes', 'olympus-google-fonts' ); ?></li>
						<li><?php $this->icon( 'check', 14, 2.5 ); ?><?php esc_html_e( 'Priority support included', 'olympus-google-fonts' ); ?></li>
					</ul>
				</div>
				<div class="ogf-modal-footer">
					<button class="ogf-btn-modal-cancel ogf-modal-close"><?php esc_html_e( 'Maybe later', 'olympus-google-fonts' ); ?></button>
					<a id="ogf-modal-link" href="https://fontsplugin.com/pro-upgrade/?utm_source=plugin&utm_medium=admin_page" target="_blank" class="ogf-btn-pro">
						<?php esc_html_e( 'Upgrade to Pro', 'olympus-google-fonts' ); ?>
						<?php $this->icon( 'arrow-right', 14, 2.5 ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Allowed settings for AJAX save.
	 *
	 * @return array
	 */
	private function get_allowed_settings() {
		$free_settings = array(
			'ogf_force_styles',
			'ogf_disable_post_level_controls',
			'ogf_use_px',
			'ogf_font_display',
		);

		$pro_settings = array(
			'ogf_host_locally',
			'ogf_preloading',
			'ogf_removal',
			'ogf_rewrite',
		);

		if ( defined( 'OGF_PRO' ) ) {
			return array_merge( $free_settings, $pro_settings );
		}

		return $free_settings;
	}

	/**
	 * AJAX handler: save a setting.
	 */
	public function ajax_save_settings() {
		check_ajax_referer( 'ogf_admin_nonce' );

		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_send_json_error( 'insufficient_permissions' );
		}

		$setting = isset( $_POST['setting'] ) ? sanitize_text_field( wp_unslash( $_POST['setting'] ) ) : '';
		$value   = isset( $_POST['value'] ) ? sanitize_text_field( wp_unslash( $_POST['value'] ) ) : '';

		$allowed = $this->get_allowed_settings();

		if ( ! in_array( $setting, $allowed, true ) ) {
			wp_send_json_error( 'invalid_setting' );
		}

		if ( 'ogf_font_display' === $setting ) {
			$valid_displays = array( 'swap', 'block', 'fallback', 'optional' );
			if ( ! in_array( $value, $valid_displays, true ) ) {
				wp_send_json_error( 'invalid_value' );
			}
			set_theme_mod( $setting, $value );
		} elseif ( 'ogf_use_px' === $setting ) {
			set_theme_mod( $setting, '1' === $value ? 'true' : 'false' );
		} else {
			set_theme_mod( $setting, '1' === $value );
		}

		wp_send_json_success();
	}

	/**
	 * AJAX handler: clear font cache.
	 */
	public function ajax_clear_font_cache() {
		check_ajax_referer( 'ogf_admin_nonce' );

		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_send_json_error( 'insufficient_permissions' );
		}

		$fonts = OGF_Fonts::get_instance();

		if ( $fonts->has_google_fonts() ) {
			$url       = $fonts->build_url();
			$url_to_id = md5( $url );
			delete_transient( 'ogf_external_font_css_' . $url_to_id );
		}

		if ( class_exists( 'FPP_Host_Google_Fonts_Locally' ) ) {
			$loader = new FPP_Host_Google_Fonts_Locally();
			$loader->delete_fonts_folder();
			delete_site_option( 'downloaded_font_files' );
		}

		wp_send_json_success();
	}

	/**
	 * AJAX handler: reset all fonts.
	 */
	public function ajax_reset_all_fonts() {
		check_ajax_referer( 'ogf_admin_nonce' );

		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_send_json_error( 'insufficient_permissions' );
		}

		// remove_theme_mod() deletes the key; set_theme_mod( $key, null ) would
		// just store a null and leave the entry behind in theme_mods.
		$properties = array( '_font', '_font_weight', '_font_style', '_font_size', '_font_color', '_line_height' );

		foreach ( array_keys( ogf_get_elements() ) as $element_id ) {
			foreach ( $properties as $property ) {
				remove_theme_mod( $element_id . $property );
			}
		}

		wp_send_json_success();
	}

	/**
	 * AJAX handler: create or update a custom font variant.
	 */
	public function ajax_save_custom_font() {
		check_ajax_referer( 'ogf_admin_nonce' );

		if ( ! current_user_can( OGF_Fonts_Taxonomy::$capability ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to do that.', 'olympus-google-fonts' ) ) );
		}

		$term_id = isset( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : 0;
		$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';

		if ( '' === $name ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a name for this variant.', 'olympus-google-fonts' ) ) );
		}

		$data = array(
			'family'  => isset( $_POST['family'] ) ? sanitize_text_field( wp_unslash( $_POST['family'] ) ) : '',
			'weight'  => isset( $_POST['weight'] ) ? sanitize_text_field( wp_unslash( $_POST['weight'] ) ) : '',
			'style'   => isset( $_POST['style'] ) ? sanitize_text_field( wp_unslash( $_POST['style'] ) ) : '',
			'preload' => ! empty( $_POST['preload'] ) ? '1' : '',
		);

		$allowed_weights = array( '', '100', '200', '300', '400', '500', '600', '700', '800', '900' );
		$allowed_styles  = array( '', 'normal', 'italic', 'oblique' );

		if ( ! in_array( $data['weight'], $allowed_weights, true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid font weight.', 'olympus-google-fonts' ) ) );
		}

		if ( ! in_array( $data['style'], $allowed_styles, true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid font style.', 'olympus-google-fonts' ) ) );
		}

		$has_file = false;

		foreach ( array( 'woff2', 'woff', 'ttf', 'otf' ) as $format ) {
			$url = isset( $_POST[ $format ] ) ? trim( sanitize_text_field( wp_unslash( $_POST[ $format ] ) ) ) : '';

			if ( '' !== $url ) {
				$url = esc_url_raw( $url );

				if ( '' === $url ) {
					/* translators: %s: font file format, e.g. "woff2" */
					wp_send_json_error( array( 'message' => sprintf( __( 'The %s URL is not valid.', 'olympus-google-fonts' ), $format ) ) );
				}

				$has_file = true;
			}

			$data[ $format ] = $url;
		}

		if ( ! $has_file ) {
			wp_send_json_error( array( 'message' => __( 'Add at least one font file.', 'olympus-google-fonts' ) ) );
		}

		if ( $term_id ) {
			$term = get_term( $term_id, OGF_Fonts_Taxonomy::$taxonomy_slug );

			if ( ! $term || is_wp_error( $term ) ) {
				wp_send_json_error( array( 'message' => __( 'That font no longer exists.', 'olympus-google-fonts' ) ) );
			}

			$updated = wp_update_term( $term_id, OGF_Fonts_Taxonomy::$taxonomy_slug, array( 'name' => $name ) );
		} else {
			$updated = wp_insert_term( $name, OGF_Fonts_Taxonomy::$taxonomy_slug );
		}

		if ( is_wp_error( $updated ) ) {
			wp_send_json_error( array( 'message' => $updated->get_error_message() ) );
		}

		OGF_Fonts_Taxonomy::update_font_data( $data, $updated['term_id'] );

		wp_send_json_success( array( 'term_id' => $updated['term_id'] ) );
	}

	/**
	 * AJAX handler: delete a custom font variant.
	 */
	public function ajax_delete_custom_font() {
		check_ajax_referer( 'ogf_admin_nonce' );

		if ( ! current_user_can( OGF_Fonts_Taxonomy::$capability ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to do that.', 'olympus-google-fonts' ) ) );
		}

		$term_id = isset( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : 0;

		if ( ! $term_id ) {
			wp_send_json_error( array( 'message' => __( 'That font no longer exists.', 'olympus-google-fonts' ) ) );
		}

		$deleted = wp_delete_term( $term_id, OGF_Fonts_Taxonomy::$taxonomy_slug );

		if ( is_wp_error( $deleted ) || ! $deleted ) {
			wp_send_json_error( array( 'message' => __( 'That font could not be deleted.', 'olympus-google-fonts' ) ) );
		}

		delete_option( 'taxonomy_' . OGF_Fonts_Taxonomy::$taxonomy_slug . "_{$term_id}" );

		wp_send_json_success();
	}

	/**
	 * AJAX handler: save the Adobe Fonts API token and pull the kits.
	 */
	public function ajax_save_typekit_key() {
		check_ajax_referer( 'ogf_admin_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to do that.', 'olympus-google-fonts' ) ) );
		}

		$key      = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : '';
		$settings = get_option( 'fp-typekit', array() );

		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		$settings['api_key'] = $key;
		update_option( 'fp-typekit', $settings );

		// The stored kits belong to the previous token.
		delete_option( 'fp-typekit-data' );

		if ( '' === $key ) {
			wp_send_json_success( array( 'message' => __( 'API token cleared.', 'olympus-google-fonts' ) ) );
		}

		$this->fetch_typekit_kits();
	}

	/**
	 * AJAX handler: re-query the Adobe Fonts API.
	 */
	public function ajax_refresh_typekit() {
		check_ajax_referer( 'ogf_admin_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to do that.', 'olympus-google-fonts' ) ) );
		}

		$this->fetch_typekit_kits();
	}

	/**
	 * Pull kits from the Adobe Fonts API and send the JSON response.
	 *
	 * Enabled/disabled state is re-applied afterwards so a refresh doesn't
	 * silently switch every kit back on.
	 */
	private function fetch_typekit_kits() {
		$previous = get_option( 'fp-typekit-data', array() );
		$typekit  = new OGF_Typekit();
		$kits     = $typekit->fetch_and_store_kits();

		if ( is_wp_error( $kits ) ) {
			wp_send_json_error( array( 'message' => $kits->get_error_message() ) );
		}

		if ( is_array( $previous ) && ! empty( $previous ) ) {
			foreach ( $kits as $kit_id => $kit ) {
				if ( isset( $previous[ $kit_id ]['enabled'] ) ) {
					$kits[ $kit_id ]['enabled'] = (bool) $previous[ $kit_id ]['enabled'];
				}
			}
			update_option( 'fp-typekit-data', $kits );
		}

		wp_send_json_success(
			array(
				/* translators: %d: number of Adobe Fonts kits */
				'message' => sprintf( _n( '%d kit found.', '%d kits found.', count( $kits ), 'olympus-google-fonts' ), count( $kits ) ),
			)
		);
	}

	/**
	 * AJAX handler: enable or disable an Adobe Fonts kit.
	 */
	public function ajax_toggle_typekit_kit() {
		check_ajax_referer( 'ogf_admin_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to do that.', 'olympus-google-fonts' ) ) );
		}

		$kit_id  = isset( $_POST['kit_id'] ) ? sanitize_text_field( wp_unslash( $_POST['kit_id'] ) ) : '';
		$enabled = ! empty( $_POST['enabled'] );

		if ( '' === $kit_id || ! OGF_Typekit::set_kit_enabled( $kit_id, $enabled ) ) {
			wp_send_json_error( array( 'message' => __( 'That kit could not be updated.', 'olympus-google-fonts' ) ) );
		}

		wp_send_json_success();
	}
}

global $ogf_admin_page;
$ogf_admin_page = new OGF_Admin_Page();
