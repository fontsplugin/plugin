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
	}

	/**
	 * Render the admin page.
	 */
	public function render() {
		$tabs = array(
			'dashboard'    => __( 'Dashboard', 'olympus-google-fonts' ),
			'fonts'        => __( 'Fonts', 'olympus-google-fonts' ),
			'font-loading' => __( 'Font Loading', 'olympus-google-fonts' ),
			'upload'       => __( 'Upload Fonts', 'olympus-google-fonts' ),
			'adobe'        => __( 'Adobe Fonts', 'olympus-google-fonts' ),
			'settings'     => __( 'Settings', 'olympus-google-fonts' ),
			'diagnostics'  => __( 'Diagnostics', 'olympus-google-fonts' ),
		);

		?>
		<div class="ogf-wrap">
			<div class="ogf-page-header">
				<div class="ogf-page-header-icon">Fp</div>
				<h1>
					<?php esc_html_e( 'Fonts Plugin', 'olympus-google-fonts' ); ?>
					<span class="ogf-version-badge"><?php echo esc_html( OGF_VERSION ); ?></span>
				</h1>
			</div>

			<div class="ogf-tab-nav" role="tablist">
				<?php foreach ( $tabs as $slug => $label ) : ?>
					<button class="ogf-tab-btn<?php echo 'dashboard' === $slug ? ' active' : ''; ?>" role="tab" data-tab="<?php echo esc_attr( $slug ); ?>">
						<?php echo esc_html( $label ); ?>
					</button>
				<?php endforeach; ?>
			</div>

			<?php
			$this->render_tab_dashboard();
			$this->render_tab_fonts();
			$this->render_tab_font_loading();
			$this->render_tab_upload();
			$this->render_tab_adobe();
			$this->render_tab_settings();
			$this->render_tab_diagnostics();
			?>

			<?php $this->render_upgrade_modal(); ?>
			<div id="ogf-toast" class="ogf-toast"></div>
		</div>
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

			<div class="ogf-card" style="margin-top:16px">
				<div class="ogf-card-header"><?php esc_html_e( 'Quick Actions', 'olympus-google-fonts' ); ?></div>
				<div class="ogf-card-body">
					<div class="ogf-actions-row">
						<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=ogf_google_fonts' ) ); ?>" class="ogf-action-btn">
							<span class="dashicons dashicons-edit"></span>
							<?php esc_html_e( 'Customize Fonts', 'olympus-google-fonts' ); ?>
						</a>
						<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=ogf_custom_fonts' ) ); ?>" class="ogf-action-btn">
							<span class="dashicons dashicons-upload"></span>
							<?php esc_html_e( 'Upload Fonts', 'olympus-google-fonts' ); ?>
						</a>
						<a href="https://docs.fontsplugin.com/" target="_blank" class="ogf-action-btn">
							<span class="dashicons dashicons-book"></span>
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
						<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Host fonts locally (GDPR)', 'olympus-google-fonts' ); ?></li>
						<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Preloading & optimization', 'olympus-google-fonts' ); ?></li>
						<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Custom element selectors', 'olympus-google-fonts' ); ?></li>
						<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Selective weight loading', 'olympus-google-fonts' ); ?></li>
						<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Remove external font requests', 'olympus-google-fonts' ); ?></li>
						<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Font subset control', 'olympus-google-fonts' ); ?></li>
					</ul>
					<a href="https://fontsplugin.com/pro-upgrade/?utm_source=plugin&utm_medium=admin_page&utm_campaign=dashboard_cta" target="_blank" class="ogf-btn-pro">
						<?php esc_html_e( 'Upgrade to Pro', 'olympus-google-fonts' ); ?>
						<span class="dashicons dashicons-arrow-right-alt"></span>
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
				<?php if ( $total > 0 ) : ?>
					<div class="ogf-rec-row ogf-rec-low">
						<div class="ogf-rec-icon"><span class="dashicons dashicons-yes-alt"></span></div>
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

				<?php if ( 'swap' === $font_display ) : ?>
					<div class="ogf-rec-row ogf-rec-low">
						<div class="ogf-rec-icon"><span class="dashicons dashicons-yes-alt"></span></div>
						<div class="ogf-rec-info">
							<div class="ogf-rec-title"><?php esc_html_e( 'Font display is set to swap', 'olympus-google-fonts' ); ?></div>
							<div class="ogf-rec-desc"><?php esc_html_e( 'Text remains visible while fonts load. This is the recommended setting.', 'olympus-google-fonts' ); ?></div>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( $custom_count > 0 ) : ?>
					<div class="ogf-rec-row ogf-rec-low">
						<div class="ogf-rec-icon"><span class="dashicons dashicons-yes-alt"></span></div>
						<div class="ogf-rec-info">
							<div class="ogf-rec-title"><?php esc_html_e( 'Custom font uploaded', 'olympus-google-fonts' ); ?></div>
							<div class="ogf-rec-desc"><?php esc_html_e( 'Custom fonts are hosted on your domain automatically.', 'olympus-google-fonts' ); ?></div>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( ! $is_pro && $google_count > 0 ) : ?>
					<div class="ogf-rec-row ogf-rec-medium">
						<div class="ogf-rec-icon"><span class="dashicons dashicons-performance"></span></div>
						<div class="ogf-rec-info">
							<div class="ogf-rec-title"><?php esc_html_e( 'Reduce unused font weights', 'olympus-google-fonts' ); ?> <span class="ogf-pro-badge"><?php esc_html_e( 'PRO', 'olympus-google-fonts' ); ?></span></div>
							<div class="ogf-rec-desc"><?php esc_html_e( 'You may be loading font weights that aren\'t used. Disabling unused weights reduces page size.', 'olympus-google-fonts' ); ?></div>
						</div>
						<button class="ogf-rec-action" data-pro-trigger data-feature="<?php esc_attr_e( 'Selective Font Loading', 'olympus-google-fonts' ); ?>" data-desc="<?php esc_attr_e( 'Choose exactly which font weights and subsets to load. Disable unused variants to reduce page weight.', 'olympus-google-fonts' ); ?>"><?php esc_html_e( 'Fix', 'olympus-google-fonts' ); ?></button>
					</div>

					<div class="ogf-rec-row ogf-rec-high">
						<div class="ogf-rec-icon"><span class="dashicons dashicons-shield"></span></div>
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
						<div class="ogf-rec-icon"><span class="dashicons dashicons-clock"></span></div>
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

		$weight_count         = is_array( $font['weights'] ) ? count( $font['weights'] ) : 0;
		$host_toggle_disabled = ! $is_pro && 'custom' !== $font['type'];
		$is_custom_hosted     = 'custom' === $font['type'];
		?>
		<div class="ogf-font-row">
			<div class="ogf-font-row-header">
				<span class="ogf-font-chevron dashicons dashicons-arrow-right-alt2"></span>
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
					<?php if ( ! empty( $font['weights'] ) ) : ?>
						<span class="ogf-font-detail-label"><?php esc_html_e( 'Weights', 'olympus-google-fonts' ); ?></span>
						<div class="ogf-weight-pills">
							<?php foreach ( $font['weights'] as $key => $label ) : ?>
								<span class="ogf-weight-pill"><?php echo esc_html( $key ); ?></span>
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
					<?php foreach ( $font['weights'] as $key => $label ) : ?>
						<?php
						$is_selected = ! $selected_weights || ( is_array( $selected_weights ) && in_array( $key, $selected_weights, true ) );
						$on_class    = $is_selected ? ' on' : '';
						?>
						<div class="ogf-fl-grid-row">
							<button class="ogf-toggle-xs<?php echo esc_attr( $on_class ); ?><?php echo ! $is_pro ? ' disabled' : ''; ?>"
								data-font="<?php echo esc_attr( $font_id ); ?>"
								data-weight="<?php echo esc_attr( $key ); ?>"></button>
							<span class="ogf-fl-grid-label"><?php echo esc_html( $key . ' ' . $label ); ?></span>
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
		$upload_url = admin_url( 'edit-tags.php?taxonomy=ogf_custom_fonts' );
		?>
		<div class="ogf-tab-panel" id="ogf-tab-upload">
			<div class="ogf-card">
				<div class="ogf-card-header"><?php esc_html_e( 'Upload Custom Fonts', 'olympus-google-fonts' ); ?></div>
				<div class="ogf-card-body">
					<p><?php esc_html_e( 'Upload your own font files (.woff2, .woff, .ttf, .otf) to use on your site. Custom fonts are automatically hosted on your domain.', 'olympus-google-fonts' ); ?></p>
					<p style="margin-top:12px">
						<a href="<?php echo esc_url( $upload_url ); ?>" class="ogf-btn-secondary">
							<span class="dashicons dashicons-upload"></span>
							<?php esc_html_e( 'Go to Upload Fonts', 'olympus-google-fonts' ); ?>
						</a>
					</p>

					<?php
					$custom_fonts = ogf_custom_fonts();
					if ( ! empty( $custom_fonts ) ) :
						?>
						<div style="margin-top:20px">
							<h4 style="font-size:13px;font-weight:600;margin-bottom:10px"><?php esc_html_e( 'Uploaded Fonts', 'olympus-google-fonts' ); ?></h4>
							<?php foreach ( $custom_fonts as $id => $font ) : ?>
								<div class="ogf-uploaded-font-row">
									<span class="ogf-font-type-badge ogf-badge-custom"><?php esc_html_e( 'Custom', 'olympus-google-fonts' ); ?></span>
									<span><?php echo esc_html( isset( $font['label'] ) ? $font['label'] : $id ); ?></span>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
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
		$kits             = get_option( 'fp-typekit-data', array() );
		$typekit_page_url = admin_url( 'admin.php?page=fonts-plugin-typekit' );
		?>
		<div class="ogf-tab-panel" id="ogf-tab-adobe">
			<div class="ogf-card">
				<div class="ogf-card-header"><?php esc_html_e( 'Adobe Fonts (Typekit)', 'olympus-google-fonts' ); ?></div>
				<div class="ogf-card-body">
					<p>
						<?php
						printf(
							/* translators: %s: URL to Adobe Fonts tokens page */
							esc_html__( 'Connect your Adobe Fonts account by entering your API key. You can find it at %s', 'olympus-google-fonts' ),
							'<a target="_blank" href="https://fonts.adobe.com/account/tokens">fonts.adobe.com/account/tokens</a>'
						);
						?>
					</p>
					<p style="margin-top:12px">
						<a href="<?php echo esc_url( $typekit_page_url ); ?>" class="ogf-btn-secondary">
							<?php echo $api_key ? esc_html__( 'Manage Adobe Fonts Settings', 'olympus-google-fonts' ) : esc_html__( 'Configure Adobe Fonts', 'olympus-google-fonts' ); ?>
						</a>
					</p>

					<?php if ( is_array( $kits ) && ! empty( $kits ) ) : ?>
						<div style="margin-top:20px">
							<h4 style="font-size:13px;font-weight:600;margin-bottom:10px"><?php esc_html_e( 'Connected Kits', 'olympus-google-fonts' ); ?></h4>
							<?php foreach ( $kits as $kit_id => $kit ) : ?>
								<?php if ( ! isset( $kit['enabled'] ) || ! $kit['enabled'] ) : ?>
									<?php continue; ?>
								<?php endif; ?>
								<div class="ogf-adobe-kit-card">
									<div class="ogf-adobe-kit-header">
										<strong><?php esc_html_e( 'Kit:', 'olympus-google-fonts' ); ?></strong> <?php echo esc_html( $kit_id ); ?>
									</div>
									<?php if ( isset( $kit['families'] ) ) : ?>
										<?php foreach ( $kit['families'] as $family ) : ?>
											<div class="ogf-uploaded-font-row">
												<span class="ogf-font-type-badge ogf-badge-adobe"><?php esc_html_e( 'Adobe', 'olympus-google-fonts' ); ?></span>
												<span><?php echo esc_html( $family['label'] ); ?></span>
											</div>
										<?php endforeach; ?>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Settings tab.
	 */
	private function render_tab_settings() {
		$is_pro = defined( 'OGF_PRO' );
		?>
		<div class="ogf-tab-panel" id="ogf-tab-settings">
			<div class="ogf-card">
				<div class="ogf-card-header"><?php esc_html_e( 'General Settings', 'olympus-google-fonts' ); ?></div>
				<div>
					<div class="ogf-setting-row">
						<div class="ogf-setting-info">
							<div class="ogf-setting-label"><?php esc_html_e( 'Force Styles', 'olympus-google-fonts' ); ?></div>
							<div class="ogf-setting-desc"><?php esc_html_e( 'Add !important to all font styles to override theme defaults.', 'olympus-google-fonts' ); ?></div>
						</div>
						<div class="ogf-setting-control">
							<button class="ogf-toggle<?php echo get_theme_mod( 'ogf_force_styles', false ) ? ' on' : ''; ?>" data-setting="ogf_force_styles"></button>
						</div>
					</div>

					<div class="ogf-setting-row">
						<div class="ogf-setting-info">
							<div class="ogf-setting-label"><?php esc_html_e( 'Disable Editor Controls', 'olympus-google-fonts' ); ?></div>
							<div class="ogf-setting-desc"><?php esc_html_e( 'Hide the font controls from the block editor sidebar.', 'olympus-google-fonts' ); ?></div>
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
							<div class="ogf-setting-desc"><?php esc_html_e( 'Controls how fonts are displayed while loading.', 'olympus-google-fonts' ); ?></div>
						</div>
						<div class="ogf-setting-control">
							<select class="ogf-setting-select" data-setting="ogf_font_display">
								<option value="swap" <?php selected( get_theme_mod( 'ogf_font_display', 'swap' ), 'swap' ); ?>><?php esc_html_e( 'Swap', 'olympus-google-fonts' ); ?></option>
								<option value="block" <?php selected( get_theme_mod( 'ogf_font_display', 'swap' ), 'block' ); ?>><?php esc_html_e( 'Block', 'olympus-google-fonts' ); ?></option>
								<option value="fallback" <?php selected( get_theme_mod( 'ogf_font_display', 'swap' ), 'fallback' ); ?>><?php esc_html_e( 'Fallback', 'olympus-google-fonts' ); ?></option>
								<option value="optional" <?php selected( get_theme_mod( 'ogf_font_display', 'swap' ), 'optional' ); ?>><?php esc_html_e( 'Optional', 'olympus-google-fonts' ); ?></option>
							</select>
						</div>
					</div>
				</div>
			</div>

			<?php if ( ! $is_pro ) : ?>
				<div class="ogf-card" style="margin-top:16px">
					<div class="ogf-card-header"><?php esc_html_e( 'Performance', 'olympus-google-fonts' ); ?></div>
					<div>
						<div class="ogf-setting-row">
							<div class="ogf-setting-info">
								<div class="ogf-setting-label"><?php esc_html_e( 'Remove External Fonts', 'olympus-google-fonts' ); ?> <span class="ogf-pro-badge"><?php esc_html_e( 'PRO', 'olympus-google-fonts' ); ?></span></div>
								<div class="ogf-setting-desc"><?php esc_html_e( 'Remove Google Fonts loaded by other plugins and your theme.', 'olympus-google-fonts' ); ?></div>
							</div>
							<div class="ogf-setting-control">
								<button class="ogf-toggle disabled" data-setting="ogf_removal" data-pro-feature data-feature="<?php esc_attr_e( 'Remove External Fonts', 'olympus-google-fonts' ); ?>" data-desc="<?php esc_attr_e( 'Remove Google Fonts loaded by other plugins and your theme to reduce external requests and improve page speed.', 'olympus-google-fonts' ); ?>"></button>
							</div>
						</div>

						<div class="ogf-setting-row">
							<div class="ogf-setting-info">
								<div class="ogf-setting-label"><?php esc_html_e( 'Rewrite External Fonts', 'olympus-google-fonts' ); ?> <span class="ogf-pro-badge"><?php esc_html_e( 'PRO', 'olympus-google-fonts' ); ?></span></div>
								<div class="ogf-setting-desc"><?php esc_html_e( 'Convert fonts added by your theme and plugins to be locally hosted on your domain.', 'olympus-google-fonts' ); ?></div>
							</div>
							<div class="ogf-setting-control">
								<button class="ogf-toggle disabled" data-setting="ogf_rewrite" data-pro-feature data-feature="<?php esc_attr_e( 'Rewrite External Fonts', 'olympus-google-fonts' ); ?>" data-desc="<?php esc_attr_e( 'Automatically rewrite Google Fonts URLs from your theme and plugins to serve from your domain, improving privacy and performance.', 'olympus-google-fonts' ); ?>"></button>
							</div>
						</div>
					</div>
				</div>
			<?php else : ?>
				<div class="ogf-card" style="margin-top:16px">
					<div class="ogf-card-header"><?php esc_html_e( 'Performance', 'olympus-google-fonts' ); ?></div>
					<div>
						<div class="ogf-setting-row">
							<div class="ogf-setting-info">
								<div class="ogf-setting-label"><?php esc_html_e( 'Remove External Fonts', 'olympus-google-fonts' ); ?></div>
								<div class="ogf-setting-desc"><?php esc_html_e( 'Remove Google Fonts loaded by other plugins and your theme.', 'olympus-google-fonts' ); ?></div>
							</div>
							<div class="ogf-setting-control">
								<button class="ogf-toggle<?php echo get_theme_mod( 'ogf_removal', false ) ? ' on' : ''; ?>" data-setting="ogf_removal"></button>
							</div>
						</div>

						<div class="ogf-setting-row">
							<div class="ogf-setting-info">
								<div class="ogf-setting-label"><?php esc_html_e( 'Rewrite External Fonts', 'olympus-google-fonts' ); ?></div>
								<div class="ogf-setting-desc"><?php esc_html_e( 'Convert fonts added by your theme and plugins to be locally hosted on your domain.', 'olympus-google-fonts' ); ?></div>
							</div>
							<div class="ogf-setting-control">
								<button class="ogf-toggle<?php echo get_theme_mod( 'ogf_rewrite', false ) ? ' on' : ''; ?>" data-setting="ogf_rewrite"></button>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>
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
			<div class="ogf-card">
				<div class="ogf-card-header"><?php esc_html_e( 'System Information', 'olympus-google-fonts' ); ?></div>
				<div class="ogf-card-body">
					<div class="ogf-diag-grid">
						<div class="ogf-diag-item">
							<span class="ogf-diag-key"><?php esc_html_e( 'Plugin Version', 'olympus-google-fonts' ); ?></span>
							<span class="ogf-diag-val"><?php echo esc_html( OGF_VERSION ); ?></span>
						</div>
						<div class="ogf-diag-item">
							<span class="ogf-diag-key"><?php esc_html_e( 'Pro Status', 'olympus-google-fonts' ); ?></span>
							<span class="ogf-diag-val"><?php echo $is_pro ? esc_html__( 'Active', 'olympus-google-fonts' ) : esc_html__( 'Free', 'olympus-google-fonts' ); ?></span>
						</div>
						<div class="ogf-diag-item">
							<span class="ogf-diag-key"><?php esc_html_e( 'WordPress Version', 'olympus-google-fonts' ); ?></span>
							<span class="ogf-diag-val"><?php echo esc_html( get_bloginfo( 'version' ) ); ?></span>
						</div>
						<div class="ogf-diag-item">
							<span class="ogf-diag-key"><?php esc_html_e( 'PHP Version', 'olympus-google-fonts' ); ?></span>
							<span class="ogf-diag-val"><?php echo esc_html( PHP_VERSION ); ?></span>
						</div>
						<div class="ogf-diag-item">
							<span class="ogf-diag-key"><?php esc_html_e( 'Theme', 'olympus-google-fonts' ); ?></span>
							<span class="ogf-diag-val"><?php echo esc_html( $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' ) ); ?></span>
						</div>
						<div class="ogf-diag-item">
							<span class="ogf-diag-key"><?php esc_html_e( 'Font Display', 'olympus-google-fonts' ); ?></span>
							<span class="ogf-diag-val"><?php echo esc_html( get_theme_mod( 'ogf_font_display', 'swap' ) ); ?></span>
						</div>
						<div class="ogf-diag-item">
							<span class="ogf-diag-key"><?php esc_html_e( 'Google Fonts', 'olympus-google-fonts' ); ?></span>
							<span class="ogf-diag-val"><?php echo esc_html( $google_count ); ?></span>
						</div>
						<div class="ogf-diag-item">
							<span class="ogf-diag-key"><?php esc_html_e( 'Custom Fonts', 'olympus-google-fonts' ); ?></span>
							<span class="ogf-diag-val"><?php echo esc_html( $custom_count ); ?></span>
						</div>
						<div class="ogf-diag-item">
							<span class="ogf-diag-key"><?php esc_html_e( 'Adobe Fonts', 'olympus-google-fonts' ); ?></span>
							<span class="ogf-diag-val"><?php echo esc_html( $adobe_count ); ?></span>
						</div>
						<div class="ogf-diag-item">
							<span class="ogf-diag-key"><?php esc_html_e( 'Font Cache', 'olympus-google-fonts' ); ?></span>
							<span class="ogf-diag-val"><?php echo $has_cache ? esc_html__( 'Active', 'olympus-google-fonts' ) : esc_html__( 'Empty', 'olympus-google-fonts' ); ?></span>
						</div>
						<div class="ogf-diag-item">
							<span class="ogf-diag-key"><?php esc_html_e( 'Force Styles', 'olympus-google-fonts' ); ?></span>
							<span class="ogf-diag-val"><?php echo get_theme_mod( 'ogf_force_styles', false ) ? esc_html__( 'Enabled', 'olympus-google-fonts' ) : esc_html__( 'Disabled', 'olympus-google-fonts' ); ?></span>
						</div>
						<div class="ogf-diag-item">
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
						</div>
					</div>
				</div>
			</div>

			<div class="ogf-card" style="margin-top:16px">
				<div class="ogf-card-header"><?php esc_html_e( 'Actions', 'olympus-google-fonts' ); ?></div>
				<div class="ogf-card-body">
					<div class="ogf-actions-row">
						<button id="ogf-clear-cache" class="ogf-btn-secondary">
							<span class="dashicons dashicons-trash"></span>
							<?php esc_html_e( 'Clear Font Cache', 'olympus-google-fonts' ); ?>
						</button>
						<button id="ogf-reset-fonts" class="ogf-btn-danger">
							<span class="dashicons dashicons-warning"></span>
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
			<div class="ogf-modal-card">
				<button class="ogf-modal-close">&times;</button>
				<h2 id="ogf-modal-title"></h2>
				<p id="ogf-modal-desc"></p>
				<ul class="ogf-modal-benefits">
					<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'One-click activation', 'olympus-google-fonts' ); ?></li>
					<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Works with all themes', 'olympus-google-fonts' ); ?></li>
					<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Priority support included', 'olympus-google-fonts' ); ?></li>
				</ul>
				<a id="ogf-modal-link" href="https://fontsplugin.com/pro-upgrade/?utm_source=plugin&utm_medium=admin_page" target="_blank" class="ogf-btn-pro">
					<?php esc_html_e( 'Upgrade to Pro', 'olympus-google-fonts' ); ?>
					<span class="dashicons dashicons-arrow-right-alt"></span>
				</a>
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

		$settings = ogf_get_elements();
		foreach ( $settings as $key => $value ) {
			set_theme_mod( $key . '_font', null );
			set_theme_mod( $key . '_font_weight', null );
			set_theme_mod( $key . '_font_style', null );
			set_theme_mod( $key . '_font_size', null );
			set_theme_mod( $key . '_font_color', null );
			set_theme_mod( $key . '_line_height', null );
		}

		wp_send_json_success();
	}
}

global $ogf_admin_page;
$ogf_admin_page = new OGF_Admin_Page();
