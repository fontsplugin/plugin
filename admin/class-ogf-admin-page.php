<?php
/**
 * Admin Page for the Fonts Plugin.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

require_once dirname( __DIR__ ) . '/lib/src/Components.php';
require_once dirname( __DIR__ ) . '/lib/src/Settings.php';
require_once dirname( __DIR__ ) . '/lib/src/Shell.php';

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
	 * Plinth Shell instance (lazy-loaded).
	 *
	 * @var \Plinth\Shell|null
	 */
	private $shell = null;

	/**
	 * Plinth Settings instance (created early for AJAX registration).
	 *
	 * @var \Plinth\Settings
	 */
	private $settings;

	/**
	 * Constructor.
	 *
	 * Create the Plinth Settings instance here, not in get_shell(). The
	 * constructor runs on every admin request (including AJAX). The Shell
	 * only instantiates when the plugin page renders. Settings must register
	 * its AJAX handler before AJAX requests arrive.
	 */
	public function __construct() {
		$this->settings = $this->register_settings();

		// Font-row HOST/PRELOAD toggles use this separate handler with the
		// ogf_admin_nonce. Settings tab toggles use Plinth's own handler.
		add_action( 'wp_ajax_ogf_save_settings', array( $this, 'ajax_save_settings' ) );
		add_action( 'wp_ajax_ogf_save_font_loading', array( $this, 'ajax_save_font_loading' ) );
		add_action( 'wp_ajax_ogf_clear_font_cache', array( $this, 'ajax_clear_font_cache' ) );
		add_action( 'wp_ajax_ogf_reset_all_fonts', array( $this, 'ajax_reset_all_fonts' ) );
		add_action( 'wp_ajax_ogf_save_custom_font', array( $this, 'ajax_save_custom_font' ) );
		add_action( 'wp_ajax_ogf_delete_custom_font', array( $this, 'ajax_delete_custom_font' ) );
		add_action( 'wp_ajax_ogf_save_typekit_key', array( $this, 'ajax_save_typekit_key' ) );
		add_action( 'wp_ajax_ogf_refresh_typekit', array( $this, 'ajax_refresh_typekit' ) );
		add_action( 'wp_ajax_ogf_toggle_typekit_kit', array( $this, 'ajax_toggle_typekit_kit' ) );
	}

	/**
	 * Register Plinth settings definitions and AJAX handler.
	 *
	 * Pro uses different theme_mod keys (fpp_ prefix) than free (ogf_ prefix).
	 * Register under the correct key so Plinth reads and writes the right
	 * value directly. No remapping is needed at save time.
	 *
	 * @return \Plinth\Settings
	 */
	private function register_settings() {
		$is_pro   = defined( 'OGF_PRO' );
		$settings = new \Plinth\Settings( 'fonts-plugin', 'edit_theme_options' );

		$woff2_key   = 'fpp_use_woff2';
		$removal_key = 'fpp_removal';
		$rewrite_key = 'fpp_rewrite';

		$settings->register( array(
			$woff2_key                      => array(
				'type'     => 'boolean',
				'storage'  => 'theme_mod',
				'label'    => __( 'Use WOFF2 File Format', 'olympus-google-fonts' ),
				'desc'     => __( 'Use the WOFF2 file format instead of WOFF.', 'olympus-google-fonts' ),
				'disabled' => ! $is_pro,
			),
			$removal_key                    => array(
				'type'     => 'boolean',
				'storage'  => 'theme_mod',
				'label'    => __( 'Remove External Fonts', 'olympus-google-fonts' ),
				'desc'     => __( 'Remove Google Fonts loaded by other plugins and your theme.', 'olympus-google-fonts' ),
				'disabled' => ! $is_pro,
			),
			$rewrite_key                    => array(
				'type'     => 'boolean',
				'storage'  => 'theme_mod',
				'label'    => __( 'Rewrite External Fonts', 'olympus-google-fonts' ),
				'desc'     => __( 'Convert fonts from other plugins and themes to be locally hosted.', 'olympus-google-fonts' ),
				'disabled' => ! $is_pro,
			),
			'ogf_force_styles'              => array(
				'type'    => 'boolean',
				'storage' => 'theme_mod',
				'label'   => __( 'Force Styles', 'olympus-google-fonts' ),
				'desc'    => __( 'Add !important to all font styles. Useful if your theme overrides the plugin.', 'olympus-google-fonts' ),
			),
			'ogf_disable_post_level_controls' => array(
				'type'    => 'boolean',
				'storage' => 'theme_mod',
				'label'   => __( 'Disable Editor Controls', 'olympus-google-fonts' ),
				'desc'    => __( 'Remove font controls from the Gutenberg and Classic editors.', 'olympus-google-fonts' ),
			),
			'ogf_use_px'                    => array(
				'type'    => 'boolean',
				'storage' => 'theme_mod',
				'default' => true,
				'label'   => __( 'Use px Font Sizes', 'olympus-google-fonts' ),
				'desc'    => __( 'Use pixel values for font sizes instead of em units.', 'olympus-google-fonts' ),
			),
			'ogf_font_display'             => array(
				'type'    => 'select',
				'storage' => 'theme_mod',
				'default' => 'swap',
				'label'   => __( 'Font Display', 'olympus-google-fonts' ),
				'desc'    => __( 'Controls the font-display CSS property for all loaded fonts.', 'olympus-google-fonts' ),
				'choices' => array(
					'swap'     => __( 'Swap', 'olympus-google-fonts' ),
					'block'    => __( 'Block', 'olympus-google-fonts' ),
					'fallback' => __( 'Fallback', 'olympus-google-fonts' ),
					'optional' => __( 'Optional', 'olympus-google-fonts' ),
				),
			),
		) );

		$settings->register_ajax();

		add_filter( 'plinth_setting_label', array( $this, 'filter_setting_label' ), 10, 3 );
		add_filter( 'plinth_setting_control', array( $this, 'filter_setting_control' ), 10, 3 );

		return $settings;
	}

	/**
	 * Add PRO badge to labels for Pro-only settings.
	 *
	 * Uses the plinth_setting_label filter so Plinth does not need to know
	 * about Pro badges. Only matches ogf_ keys (free mode). In Pro mode the
	 * keys are fpp_ and the settings are not disabled, so this filter has
	 * no effect.
	 *
	 * @param string $html Label HTML.
	 * @param string $key  Setting key.
	 * @param array  $def  Setting definition.
	 * @return string
	 */
	public function filter_setting_label( $html, $key, $def ) {
		$pro_settings = array( 'fpp_use_woff2', 'fpp_removal', 'fpp_rewrite' );

		if ( in_array( $key, $pro_settings, true ) && ! defined( 'OGF_PRO' ) ) {
			$badge = ' <span class="ogf-pro-badge">' . esc_html__( 'PRO', 'olympus-google-fonts' ) . '</span>';
			$html  = str_replace( '</div>', $badge . '</div>', $html );
		}

		return $html;
	}

	/**
	 * Add data attributes to disabled Pro-only toggles.
	 *
	 * Injects data-pro-feature, data-feature, and data-desc so the
	 * initProModal JS handler can show an upsell modal on click. Plinth
	 * uses aria-disabled (not HTML disabled) on the button, so click
	 * events still fire.
	 *
	 * @param string $html Control HTML.
	 * @param string $key  Setting key.
	 * @param array  $def  Setting definition.
	 * @return string
	 */
	public function filter_setting_control( $html, $key, $def ) {
		$pro_descs = array(
			'fpp_use_woff2' => __( 'Serve locally hosted fonts in the WOFF2 format for smaller file sizes and faster loading.', 'olympus-google-fonts' ),
			'fpp_removal'   => __( 'Remove Google Fonts loaded by other plugins and your theme to reduce external requests and improve page speed.', 'olympus-google-fonts' ),
			'fpp_rewrite'   => __( 'Automatically rewrite Google Fonts URLs from your theme and plugins to serve from your domain, improving privacy and performance.', 'olympus-google-fonts' ),
		);

		if ( isset( $pro_descs[ $key ] ) && ! defined( 'OGF_PRO' ) ) {
			$attrs = sprintf(
				' data-pro-feature data-feature="%s" data-desc="%s"',
				esc_attr( $def['label'] ),
				esc_attr( $pro_descs[ $key ] )
			);
			$html = str_replace( ' data-setting=', $attrs . ' data-setting=', $html );
		}

		return $html;
	}

	/**
	 * Get or create the Plinth Shell instance.
	 *
	 * @return \Plinth\Shell
	 */
	public function get_shell() {
		if ( $this->shell ) {
			return $this->shell;
		}

		$fonts       = $this->get_active_fonts();
		$adobe_fonts = ogf_typekit_fonts();
		$guide_seen  = (bool) get_option( 'ogf_dismiss_guide', false );

		$overview_items = array(
			array( 'dashboard', __( 'Dashboard', 'olympus-google-fonts' ), 'activity', null ),
		);
		if ( ! $guide_seen ) {
			array_unshift(
				$overview_items,
				array( 'guide', __( 'Quickstart', 'olympus-google-fonts' ), 'book', null )
			);
		}

		$nav_groups = array(
			array(
				'group' => __( 'Overview', 'olympus-google-fonts' ),
				'items' => $overview_items,
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

		$tabs = array();

		if ( ! $guide_seen ) {
			$tabs['guide'] = array(
				'label'    => __( 'Quickstart', 'olympus-google-fonts' ),
				'icon'     => 'book',
				'callback' => array( $this, 'render_tab_guide' ),
			);
		}

		$tabs['dashboard']    = array(
			'label'    => __( 'Dashboard', 'olympus-google-fonts' ),
			'icon'     => 'activity',
			'callback' => array( $this, 'render_tab_dashboard' ),
		);
		$tabs['fonts']        = array(
			'label'    => __( 'Active Fonts', 'olympus-google-fonts' ),
			'icon'     => 'type',
			'callback' => array( $this, 'render_tab_fonts' ),
		);
		$tabs['font-loading'] = array(
			'label'    => __( 'Font Loading', 'olympus-google-fonts' ),
			'icon'     => 'zap',
			'callback' => array( $this, 'render_tab_font_loading' ),
		);
		$tabs['upload']       = array(
			'label'    => __( 'Upload Fonts', 'olympus-google-fonts' ),
			'icon'     => 'upload',
			'callback' => array( $this, 'render_tab_upload' ),
		);
		$tabs['adobe']        = array(
			'label'    => __( 'Adobe Fonts', 'olympus-google-fonts' ),
			'icon'     => 'adobe',
			'callback' => array( $this, 'render_tab_adobe' ),
		);
		$tabs['settings']     = array(
			'label'    => __( 'Settings', 'olympus-google-fonts' ),
			'icon'     => 'settings',
			'type'     => 'settings',
			'title'    => __( 'Settings', 'olympus-google-fonts' ),
			'desc'     => __( 'How the plugin loads fonts and how strongly it overrides your theme.', 'olympus-google-fonts' ),
			'sections' => array(
				'performance' => array(
					'label'    => __( 'Performance & Privacy', 'olympus-google-fonts' ),
					'settings' => array(
						'fpp_use_woff2',
						'fpp_removal',
						'fpp_rewrite',
					),
				),
				'general'     => array(
					'label'    => __( 'General', 'olympus-google-fonts' ),
					'settings' => array( 'ogf_force_styles', 'ogf_disable_post_level_controls', 'ogf_use_px', 'ogf_font_display' ),
				),
			),
		);
		$tabs['diagnostics']  = array(
			'label'    => __( 'Diagnostics', 'olympus-google-fonts' ),
			'icon'     => 'pulse',
			'callback' => array( $this, 'render_tab_diagnostics' ),
		);

		$help_html = sprintf(
			'<strong>%s</strong>%s',
			esc_html__( 'Need help?', 'olympus-google-fonts' ),
			sprintf(
				/* translators: %1$s: documentation link, %2$s: support link */
				esc_html__( 'Read the %1$s or open a %2$s on WordPress.org.', 'olympus-google-fonts' ),
				'<a href="https://docs.fontsplugin.com/" target="_blank">' . esc_html__( 'documentation', 'olympus-google-fonts' ) . '</a>',
				'<a href="https://wordpress.org/support/plugin/olympus-google-fonts/" target="_blank">' . esc_html__( 'support ticket', 'olympus-google-fonts' ) . '</a>'
			)
		);

		$this->shell = new \Plinth\Shell( array(
			'slug'        => 'fonts-plugin',
			'title'       => __( 'Fonts Plugin', 'olympus-google-fonts' ),
			'version'     => OGF_VERSION,
			'icon'        => OGF_DIR_URL . 'admin/icon-128x128.jpg',
			'tabs'        => $tabs,
			'default_tab' => $guide_seen ? 'dashboard' : 'guide',
			'nav_groups'  => $nav_groups,
			'help'        => $help_html,
			'after_tabs'  => array( $this, 'render_upgrade_modal' ),
			'capability'  => 'manage_options',
			'settings'    => $this->settings,
		) );

		if ( ! $guide_seen ) {
			update_option( 'ogf_dismiss_guide', true );
		}

		return $this->shell;
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
	 * Render the admin page via Plinth Shell.
	 */
	public function render() {
		$this->get_shell()->render();
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

			if ( ogf_is_custom_font( $font_id ) ) {
				$lookup_id = substr( $font_id, 3 );
				if ( isset( $custom_fonts[ $lookup_id ] ) ) {
					$font_data['type'] = 'custom';
					$font_data['name'] = isset( $custom_fonts[ $lookup_id ]['label'] ) ? $custom_fonts[ $lookup_id ]['label'] : $lookup_id;
				}
			} elseif ( ogf_is_typekit_font( $font_id ) && isset( $typekit_fonts[ $font_id ] ) ) {
				$font_data['type'] = 'adobe';
				$font_data['name'] = isset( $typekit_fonts[ $font_id ]['label'] ) ? $typekit_fonts[ $font_id ]['label'] : $font_id;
			} elseif ( ogf_is_system_font( $font_id ) ) {
				$lookup_id = substr( $font_id, 3 );
				if ( isset( $system_fonts[ $lookup_id ] ) ) {
					$font_data['type'] = 'system';
					$font_data['name'] = isset( $system_fonts[ $lookup_id ]['label'] ) ? $system_fonts[ $lookup_id ]['label'] : $lookup_id;
				}
			} elseif ( isset( $google_fonts[ $font_id ] ) ) {
				$font_data['type']    = 'google';
				$font_data['name']    = isset( $google_fonts[ $font_id ]['f'] ) ? $google_fonts[ $font_id ]['f'] : $font_id;
				$font_data['weights'] = $ogf_fonts->get_font_weights( $font_id );
				$font_data['subsets'] = $ogf_fonts->get_font_subsets( $font_id );
			}

			$fonts[ $font_id ] = $font_data;
		}

		$this->active_fonts = $fonts;
		return $fonts;
	}

	/**
	 * Count active fonts by source type.
	 *
	 * @param array $fonts Active fonts from get_active_fonts().
	 * @return array
	 */
	private function get_font_type_counts( $fonts ) {
		$counts = array(
			'google' => 0,
			'custom' => 0,
			'adobe'  => 0,
			'system' => 0,
		);

		foreach ( $fonts as $font ) {
			if ( isset( $counts[ $font['type'] ] ) ) {
				++$counts[ $font['type'] ];
			}
		}

		return $counts;
	}

	/**
	 * Count loaded font variants for Google and custom fonts.
	 *
	 * @param array $fonts Active fonts from get_active_fonts().
	 * @return int
	 */
	private function get_loaded_variant_count( $fonts ) {
		$ogf_fonts = OGF_Fonts::get_instance();
		$count     = 0;

		foreach ( $fonts as $font ) {
			if ( 'google' === $font['type'] ) {
				$weights = $ogf_fonts->filter_selected_weights( $font['id'], $font['weights'] );
				$count  += is_array( $weights ) ? count( $weights ) : 0;
			} elseif ( 'custom' === $font['type'] ) {
				++$count;
			}
		}

		return $count;
	}

	/**
	 * Build loading-method labels and descriptions for active font sources.
	 *
	 * @param int  $google_count Number of active Google fonts.
	 * @param int  $custom_count Number of active custom fonts.
	 * @param int  $adobe_count  Number of active Adobe fonts.
	 * @param bool $host_locally Whether Google fonts are hosted locally.
	 * @return array
	 */
	private function get_font_delivery_info( $google_count, $custom_count, $adobe_count, $host_locally ) {
		$labels  = array();
		$sources = array();

		if ( $google_count > 0 ) {
			if ( $host_locally ) {
				$labels[]  = __( 'Local', 'olympus-google-fonts' );
				$sources[] = wp_parse_url( home_url(), PHP_URL_HOST );
			} else {
				$labels[]  = __( 'Google CDN', 'olympus-google-fonts' );
				$sources[] = 'fonts.googleapis.com';
			}
		}

		if ( $custom_count > 0 ) {
			$local_label = __( 'Local', 'olympus-google-fonts' );
			if ( ! in_array( $local_label, $labels, true ) ) {
				$labels[] = $local_label;
			}
			$domain = wp_parse_url( home_url(), PHP_URL_HOST );
			if ( ! in_array( $domain, $sources, true ) ) {
				$sources[] = $domain;
			}
		}

		if ( $adobe_count > 0 ) {
			$labels[]  = __( 'Adobe', 'olympus-google-fonts' );
			$sources[] = 'use.typekit.com';
		}

		if ( empty( $labels ) ) {
			return array(
				'labels'      => array( __( 'System', 'olympus-google-fonts' ) ),
				'description' => __( 'Built-in fonts with no external files to download', 'olympus-google-fonts' ),
			);
		}

		return array(
			'labels'      => $labels,
			'description' => sprintf(
				/* translators: %s: comma-separated list of font source domains */
				__( 'Fonts served from %s', 'olympus-google-fonts' ),
				implode( ', ', $sources )
			),
		);
	}

	/**
	 * Get details for active plugins.
	 *
	 * @return array[] Each item has 'name' and 'version' keys.
	 */
	private function get_active_plugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins    = get_plugins();
		$active_files   = (array) get_option( 'active_plugins', array() );
		$active_plugins = array();

		foreach ( $active_files as $plugin_file ) {
			if ( ! isset( $all_plugins[ $plugin_file ] ) ) {
				continue;
			}

			$plugin = $all_plugins[ $plugin_file ];
			$active_plugins[] = array(
				'name'    => $plugin['Name'],
				'version' => $plugin['Version'],
			);
		}

		usort(
			$active_plugins,
			function( $a, $b ) {
				return strcasecmp( $a['name'], $b['name'] );
			}
		);

		return $active_plugins;
	}

	/**
	 * Render Dashboard tab content (callback for Plinth Shell).
	 *
	 * @param \Plinth\Shell $shell Shell instance.
	 */
	public function render_tab_dashboard( $shell = null ) {
		$shell = $shell ? $shell : $this->get_shell();
		$fonts        = $this->get_active_fonts();
		$total        = count( $fonts );
		$type_counts  = $this->get_font_type_counts( $fonts );
		$google_count = $type_counts['google'];
		$custom_count = $type_counts['custom'];
		$adobe_count  = $type_counts['adobe'];

		$is_pro       = ogf_is_pro_active();
		$host_locally = $is_pro && get_theme_mod( $this->get_setting_key( 'ogf_host_locally' ), false );
		$font_display = get_theme_mod( 'ogf_font_display', 'swap' );
		$delivery     = $this->get_font_delivery_info( $google_count, $custom_count, $adobe_count, $host_locally );
		?>
			<?php \Plinth\Components::panel_header( __( 'Dashboard', 'olympus-google-fonts' ), __( 'See your fonts at a glance and what you might want to change next.', 'olympus-google-fonts' ) ); ?>
			<div class="pln-stats">
				<div class="pln-stat">
					<div class="pln-stat__label"><?php esc_html_e( 'Fonts Loaded', 'olympus-google-fonts' ); ?></div>
					<div class="pln-stat__value"><?php echo esc_html( $total ); ?></div>
					<div class="pln-stat__meta">
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
				<div class="pln-stat">
					<div class="pln-stat__label"><?php esc_html_e( 'Loading Method', 'olympus-google-fonts' ); ?></div>
					<div class="pln-stat__value pln-stat__value--sm"><?php echo esc_html( implode( ', ', $delivery['labels'] ) ); ?></div>
					<div class="pln-stat__meta"><?php echo esc_html( $delivery['description'] ); ?></div>
				</div>
			</div>

			<?php $this->render_recommendations( $fonts, $google_count, $custom_count ); ?>

			<?php
			\Plinth\Components::card(
				__( 'Quick Actions', 'olympus-google-fonts' ),
				function() use ( $shell ) {
					?>
					<div class="pln-actions">
						<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=ogf_google_fonts' ) ); ?>" class="pln-action">
							<?php $shell->icon( 'edit' ); ?>
							<?php esc_html_e( 'Customize Fonts', 'olympus-google-fonts' ); ?>
						</a>
						<button type="button" class="pln-action" data-goto-tab="upload">
							<?php $shell->icon( 'upload' ); ?>
							<?php esc_html_e( 'Upload Fonts', 'olympus-google-fonts' ); ?>
						</button>
						<a href="https://docs.fontsplugin.com/" target="_blank" class="pln-action">
							<?php $shell->icon( 'book' ); ?>
							<?php esc_html_e( 'Documentation', 'olympus-google-fonts' ); ?>
						</a>
						<a href="https://wordpress.org/support/plugin/olympus-google-fonts/" target="_blank" class="pln-action">
							<?php $shell->icon( 'life-buoy' ); ?>
							<?php esc_html_e( 'Support', 'olympus-google-fonts' ); ?>
						</a>
					</div>
					<?php
				}
			);
			?>

			<?php if ( ! $is_pro ) : ?>
				<div class="ogf-pro-cta">
					<h3><?php esc_html_e( 'Upgrade to Fonts Plugin Pro', 'olympus-google-fonts' ); ?></h3>
					<p><?php esc_html_e( 'Unlock performance optimizations and full typographic control for your site.', 'olympus-google-fonts' ); ?></p>
					<ul>
						<li><?php $shell->icon( 'check', 14, 2.5 ); ?><?php esc_html_e( 'Host fonts locally (GDPR)', 'olympus-google-fonts' ); ?></li>
						<li><?php $shell->icon( 'check', 14, 2.5 ); ?><?php esc_html_e( 'Preloading & optimization', 'olympus-google-fonts' ); ?></li>
						<li><?php $shell->icon( 'check', 14, 2.5 ); ?><?php esc_html_e( 'Custom element selectors', 'olympus-google-fonts' ); ?></li>
						<li><?php $shell->icon( 'check', 14, 2.5 ); ?><?php esc_html_e( 'Selective weight loading', 'olympus-google-fonts' ); ?></li>
						<li><?php $shell->icon( 'check', 14, 2.5 ); ?><?php esc_html_e( 'Remove external font requests', 'olympus-google-fonts' ); ?></li>
						<li><?php $shell->icon( 'check', 14, 2.5 ); ?><?php esc_html_e( 'Font subset control', 'olympus-google-fonts' ); ?></li>
					</ul>
					<a href="https://fontsplugin.com/pro-upgrade/?utm_source=plugin&utm_medium=admin_page&utm_campaign=dashboard_cta" target="_blank" class="ogf-btn-pro">
						<?php esc_html_e( 'Upgrade to Pro', 'olympus-google-fonts' ); ?>
						<?php $shell->icon( 'arrow-right', 14, 2.5 ); ?>
					</a>
				</div>
			<?php endif; ?>

		<?php
	}

	/**
	 * Render Quickstart Guide tab content (callback for Plinth Shell).
	 *
	 * @param \Plinth\Shell $shell Shell instance.
	 */
	public function render_tab_guide( $shell = null ) {
		\Plinth\Components::card(
			'',
			function() {
				?>
					<div class="ogf-guide-layout">
						<div class="ogf-guide-content">
							<h1 class="ogf-page-title"><?php esc_html_e( 'Your Free Quickstart Guide', 'olympus-google-fonts' ); ?></h1>
							<p><?php esc_html_e( 'To help you get the most out of the Google Fonts plugin we\'ve put together a free quickstart guide.', 'olympus-google-fonts' ); ?></p>
							<p><?php esc_html_e( 'In this beautifully-formatted, easy-to-read PDF you will learn:', 'olympus-google-fonts' ); ?></p>
							<ul class="ogf-guide-list">
								<?php /* translators: %1$s and %2$s are opening and closing strong tags */ ?>
								<li><?php printf( esc_html__( 'How to %1$seasily%2$s customize your typography.', 'olympus-google-fonts' ), '<strong>', '</strong>' ); ?></li>
								<?php /* translators: %1$s and %2$s are opening and closing strong tags */ ?>
								<li><?php printf( esc_html__( 'How to host fonts %1$slocally%2$s for speed, GDPR & DSGVO.', 'olympus-google-fonts' ), '<strong>', '</strong>' ); ?></li>
								<?php /* translators: %1$s and %2$s are opening and closing strong tags */ ?>
								<li><?php printf( esc_html__( 'How to use Google Fonts without %1$sslowing down%2$s your website.', 'olympus-google-fonts' ), '<strong>', '</strong>' ); ?></li>
							</ul>
							<p><?php esc_html_e( 'Download your free copy today.', 'olympus-google-fonts' ); ?></p>

							<form action="https://fontsplugin.email/subscribe" method="post" class="ogf-guide-form validate" target="_blank" novalidate>
								<input type="email" value="" placeholder="<?php esc_attr_e( 'Your email address...', 'olympus-google-fonts' ); ?>" name="email" class="required email" id="mce-EMAIL">
								<input type="hidden" name="list" value="2guyf8U56tOENOh6892lBQ6w"/>
								<input type="hidden" name="subform" value="yes"/>
								<input type="submit" value="<?php esc_attr_e( 'Send My Guide!', 'olympus-google-fonts' ); ?>" name="submit" class="ogf-send-guide-button ogf-btn-pro">
							</form>
						</div>
						<img
							class="ogf-guide-cover"
							src="<?php echo esc_url( OGF_DIR_URL . 'admin/fonts-plugin-quickstart-guide.png' ); ?>"
							alt="<?php esc_attr_e( 'Fonts Plugin Quickstart Guide', 'olympus-google-fonts' ); ?>"
							width="210"
							height="280"
						>
					</div>
				<?php
			},
			array( 'class' => 'ogf-guide-card' )
		);
	}

	/**
	 * Render the recommendations section.
	 *
	 * @param array $fonts        Active fonts.
	 * @param int   $google_count Number of Google fonts.
	 * @param int   $custom_count Number of custom fonts.
	 */
	private function render_recommendations( $fonts, $google_count, $custom_count ) {
		$is_pro = ogf_is_pro_active();
		$loaded_count = 0;

		foreach ( $fonts as $font ) {
			if ( in_array( $font['type'], array( 'google', 'custom', 'adobe' ), true ) ) {
				++$loaded_count;
			}
		}

		$context = array(
			'is_pro'        => $is_pro,
			'needs_license' => ogf_is_pro_plugin_installed() && ! $is_pro,
			'host_locally'  => $is_pro && get_theme_mod( $this->get_setting_key( 'ogf_host_locally' ), false ),
			'font_display'  => get_theme_mod( 'ogf_font_display', 'swap' ),
			'total'         => count( $fonts ),
			'google_count'  => $google_count,
			'custom_count'  => $custom_count,
			'loaded_count'  => $loaded_count,
			'variant_count' => $this->get_loaded_variant_count( $fonts ),
		);

		\Plinth\Components::card(
			__( 'Recommendations', 'olympus-google-fonts' ),
			function() use ( $context ) {
				$this->render_recommendation_rows( $context );
			},
			array( 'wrap_body' => false )
		);
	}

	/**
	 * Render recommendation rows inside the recommendations card.
	 *
	 * @param array $context Recommendation context data.
	 */
	private function render_recommendation_rows( array $context ) {
		$pro_badge = $this->get_pro_badge_html();

		if ( $context['needs_license'] ) {
			\Plinth\Components::recommendation_row(
				'high',
				'settings',
				__( 'Activate license', 'olympus-google-fonts' ),
				__( 'Please add your Fonts Plugin Pro license key to use Pro features.', 'olympus-google-fonts' ),
				$this->get_rec_link_action(
					admin_url( 'admin.php?page=fpp_license_page' ),
					__( 'Add License', 'olympus-google-fonts' )
				)
			);
		}

		if ( 0 === $context['total'] ) {
			\Plinth\Components::recommendation_row(
				'high',
				'edit',
				__( 'Choose your first font', 'olympus-google-fonts' ),
				__( 'No fonts are assigned yet, so your theme\'s default typography is still in use. Pick a font for your headings and body text to get started.', 'olympus-google-fonts' ),
				$this->get_rec_link_action(
					admin_url( 'customize.php?autofocus[panel]=ogf_google_fonts' ),
					__( 'Choose', 'olympus-google-fonts' )
				)
			);
			return;
		}

		\Plinth\Components::recommendation_row(
			'low',
			'check',
			__( 'Fonts configured', 'olympus-google-fonts' ),
			__( 'You\'ve customized fonts across your site elements.', 'olympus-google-fonts' )
		);

		if ( 'swap' === $context['font_display'] ) {
			\Plinth\Components::recommendation_row(
				'low',
				'check',
				__( 'Font display is set to swap', 'olympus-google-fonts' ),
				__( 'Text remains visible while fonts load. This is the recommended setting.', 'olympus-google-fonts' )
			);
		}

		if ( $context['custom_count'] > 0 ) {
			\Plinth\Components::recommendation_row(
				'low',
				'check',
				__( 'Custom font uploaded', 'olympus-google-fonts' ),
				__( 'Custom fonts are hosted on your domain automatically.', 'olympus-google-fonts' )
			);
		}

		if ( $context['is_pro'] && $context['host_locally'] && $context['google_count'] > 0 ) {
			\Plinth\Components::recommendation_row(
				'low',
				'check',
				__( 'Fonts hosted locally', 'olympus-google-fonts' ),
				__( 'Google Fonts are served from your domain for better privacy and performance.', 'olympus-google-fonts' )
			);
		}

		if ( $context['loaded_count'] > 3 ) {
			\Plinth\Components::recommendation_row(
				'medium',
				'type',
				__( 'Too many font families', 'olympus-google-fonts' ),
				sprintf(
					/* translators: %d: number of font families */
					_n(
						'You are loading %d font family. Most sites perform best with three or fewer.',
						'You are loading %d font families. Most sites perform best with three or fewer.',
						$context['loaded_count'],
						'olympus-google-fonts'
					),
					$context['loaded_count']
				),
				$this->get_rec_button_action(
					__( 'Review', 'olympus-google-fonts' ),
					array( 'data-goto-tab' => 'fonts' )
				)
			);
		}

		if ( $context['variant_count'] > 10 && $context['google_count'] > 0 ) {
			$variant_action = $context['is_pro']
				? $this->get_rec_button_action(
					__( 'Review', 'olympus-google-fonts' ),
					array( 'data-goto-tab' => 'font-loading' )
				)
				: $this->get_rec_button_action(
					__( 'Fix', 'olympus-google-fonts' ),
					array(
						'data-pro-trigger' => '',
						'data-feature'     => __( 'Selective Font Loading', 'olympus-google-fonts' ),
						'data-desc'        => __( 'Choose exactly which font weights and subsets to load. Disable unused variants to reduce page weight.', 'olympus-google-fonts' ),
					)
				);

			\Plinth\Components::recommendation_row(
				'medium',
				'package',
				__( 'Reduce unused font weights', 'olympus-google-fonts' ),
				sprintf(
					/* translators: %d: number of font variants */
					_n(
						'You are loading %d font variant. Disabling unused weights reduces page size.',
						'You are loading %d font variants. Disabling unused weights reduces page size.',
						$context['variant_count'],
						'olympus-google-fonts'
					),
					$context['variant_count']
				),
				$variant_action,
				$context['is_pro'] ? '' : $pro_badge
			);
		}

		if ( ! $context['is_pro'] && $context['google_count'] > 0 ) {
			\Plinth\Components::recommendation_row(
				'high',
				'zap',
				__( 'Host fonts locally', 'olympus-google-fonts' ),
				sprintf(
					/* translators: %d: number of Google fonts */
					_n(
						'%d Google Font is loaded from Google\'s CDN. Hosting locally improves privacy (GDPR) and can help pages load faster.',
						'%d Google Fonts are loaded from Google\'s CDN. Hosting locally improves privacy (GDPR) and can help pages load faster.',
						$context['google_count'],
						'olympus-google-fonts'
					),
					$context['google_count']
				),
				$this->get_rec_button_action(
					__( 'Fix', 'olympus-google-fonts' ),
					array(
						'data-pro-trigger' => '',
						'data-feature'     => __( 'Host Fonts Locally', 'olympus-google-fonts' ),
						'data-desc'        => __( 'Download and serve Google Fonts from your own domain. Eliminates external requests, improving privacy (GDPR) and page load speed.', 'olympus-google-fonts' ),
					)
				),
				$pro_badge
			);

			\Plinth\Components::recommendation_row(
				'high',
				'clock',
				__( 'Enable preloading', 'olympus-google-fonts' ),
				__( 'Add preload hints so the browser fetches font files earlier, reducing layout shift and improving Core Web Vitals.', 'olympus-google-fonts' ),
				$this->get_rec_button_action(
					__( 'Fix', 'olympus-google-fonts' ),
					array(
						'data-pro-trigger' => '',
						'data-feature'     => __( 'Preload Fonts', 'olympus-google-fonts' ),
						'data-desc'        => __( 'Add preload resource hints so the browser begins downloading fonts before they\'re needed, reducing layout shift.', 'olympus-google-fonts' ),
					)
				),
				$pro_badge
			);
		}
	}

	/**
	 * HTML for the Pro badge shown on recommendation rows.
	 *
	 * @return string
	 */
	private function get_pro_badge_html() {
		return '<span class="ogf-pro-badge">' . esc_html__( 'PRO', 'olympus-google-fonts' ) . '</span>';
	}

	/**
	 * HTML for a recommendation row link action.
	 *
	 * @param string $url   Link URL.
	 * @param string $label Link label.
	 * @return string
	 */
	private function get_rec_link_action( $url, $label ) {
		return sprintf(
			'<a href="%s" class="pln-rec__action">%s</a>',
			esc_url( $url ),
			esc_html( $label )
		);
	}

	/**
	 * HTML for a recommendation row button action.
	 *
	 * @param string $label      Button label.
	 * @param array  $attributes Optional button attributes.
	 * @return string
	 */
	private function get_rec_button_action( $label, $attributes = array() ) {
		$attr_string = ' type="button" class="pln-rec__action"';

		foreach ( $attributes as $key => $value ) {
			if ( '' === $value ) {
				$attr_string .= ' ' . esc_attr( $key );
				continue;
			}

			$attr_string .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
		}

		return sprintf( '<button%s>%s</button>', $attr_string, esc_html( $label ) );
	}

	/**
	 * Render Fonts tab content (callback for Plinth Shell).
	 *
	 * @param \Plinth\Shell $shell Shell instance.
	 */
	public function render_tab_fonts( $shell = null ) {
		$shell = $shell ? $shell : $this->get_shell();
		$fonts  = $this->get_active_fonts();
		$is_pro = ogf_is_pro_active();
		?>
			<?php \Plinth\Components::panel_header( __( 'Active Fonts', 'olympus-google-fonts' ), __( 'Every font assigned to an element on your site, with the weights and subsets each one loads.', 'olympus-google-fonts' ) ); ?>
			<?php
			$font_count_label = sprintf(
				/* translators: %d: number of fonts */
				_n( '%d font loaded', '%d fonts loaded', count( $fonts ), 'olympus-google-fonts' ),
				count( $fonts )
			);
			\Plinth\Components::card(
				__( 'Active Fonts', 'olympus-google-fonts' ),
				function() use ( $shell, $fonts, $is_pro ) {
					if ( empty( $fonts ) ) {
						?>
						<div class="pln-card__body">
							<p><?php esc_html_e( 'No fonts configured yet.', 'olympus-google-fonts' ); ?></p>
							<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=ogf_google_fonts' ) ); ?>" class="pln-action">
								<?php esc_html_e( 'Customize Fonts', 'olympus-google-fonts' ); ?>
							</a>
						</div>
						<?php
						return;
					}

					foreach ( $fonts as $font_id => $font ) {
						$this->render_font_row( $shell, $font, $is_pro );
					}
				},
				array(
					'header_meta' => $font_count_label,
					'wrap_body'   => false,
				)
			);
			?>
		<?php
	}

	/**
	 * Render a single font row.
	 *
	 * @param \Plinth\Shell $shell  Plinth shell instance.
	 * @param array         $font   Font data.
	 * @param bool          $is_pro Whether Pro is active.
	 */
	private function render_font_row( $shell, $font, $is_pro ) {
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
		$weight_count    = is_array( $font['weights'] ) ? count( $font['weights'] ) : 0;
		$weight_labels   = $this->upright_weight_labels( is_array( $font['weights'] ) ? array_keys( $font['weights'] ) : array() );
		$show_toggles    = in_array( $font['type'], array( 'google', 'custom' ), true );
		$host_enabled    = $is_pro && get_theme_mod( $this->get_setting_key( 'ogf_host_locally' ), false );
		$preload_enabled = $is_pro && get_theme_mod( $this->get_setting_key( 'ogf_preloading' ), false );
		$toggle_disabled = ! $is_pro;
		$host_title      = $is_pro ? __( 'Controls hosting globally for all Google Fonts. Custom fonts remain locally hosted.', 'olympus-google-fonts' ) : __( 'Local hosting is available in Fonts Plugin Pro.', 'olympus-google-fonts' );
		$preload_title   = $is_pro ? __( 'Controls preloading globally for all active fonts.', 'olympus-google-fonts' ) : __( 'Font preloading is available in Fonts Plugin Pro.', 'olympus-google-fonts' );
		?>
		<div class="ogf-font-row">
			<div class="ogf-font-row-header">
				<?php $shell->icon( 'chevron-right', 14, 2.5, 'ogf-font-chevron' ); ?>
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
					<?php if ( $show_toggles ) : ?>
						<div class="ogf-font-toggles">
							<div class="ogf-font-toggle-group" <?php echo $toggle_disabled ? 'data-pro-toggle data-feature="' . esc_attr__( 'Host Fonts Locally', 'olympus-google-fonts' ) . '" data-desc="' . esc_attr__( 'Download and serve Google Fonts from your own domain instead of the CDN.', 'olympus-google-fonts' ) . '"' : ''; ?>>
								<span class="ogf-font-toggle-label"><?php esc_html_e( 'Host', 'olympus-google-fonts' ); ?></span>
								<button type="button" class="ogf-toggle-sm<?php echo $toggle_disabled ? ' disabled' : ''; ?><?php echo $host_enabled ? ' on' : ''; ?>" data-setting="ogf_host_locally" title="<?php echo esc_attr( $host_title ); ?>" aria-label="<?php esc_attr_e( 'Toggle global font hosting', 'olympus-google-fonts' ); ?>"></button>
							</div>
							<div class="ogf-font-toggle-group" <?php echo $toggle_disabled ? 'data-pro-toggle data-feature="' . esc_attr__( 'Preload Fonts', 'olympus-google-fonts' ) . '" data-desc="' . esc_attr__( 'Add preload resource hints for active fonts.', 'olympus-google-fonts' ) . '"' : ''; ?>>
								<span class="ogf-font-toggle-label"><?php esc_html_e( 'Preload', 'olympus-google-fonts' ); ?></span>
								<button type="button" class="ogf-toggle-sm<?php echo $toggle_disabled ? ' disabled' : ''; ?><?php echo $preload_enabled ? ' on' : ''; ?>" data-setting="ogf_preloading" title="<?php echo esc_attr( $preload_title ); ?>" aria-label="<?php esc_attr_e( 'Toggle global font preloading', 'olympus-google-fonts' ); ?>"></button>
							</div>
						</div>
					<?php endif; ?>
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
	 * Render Font Loading tab content (callback for Plinth Shell).
	 *
	 * @param \Plinth\Shell $shell Shell instance.
	 */
	public function render_tab_font_loading( $shell = null ) {
		$fonts  = $this->get_active_fonts();
		$is_pro = ogf_is_pro_active();

		$google_fonts = array_filter(
			$fonts,
			function ( $f ) {
				return 'google' === $f['type'];
			}
		);
		?>
			<?php \Plinth\Components::panel_header( __( 'Font Loading', 'olympus-google-fonts' ), __( 'Control which weights each font downloads and which subsets are kept globally. Fewer variants means a lighter page.', 'olympus-google-fonts' ) ); ?>
			<div class="pln-card" style="margin-bottom:16px">
				<div class="pln-card__body" style="display:flex;align-items:center;justify-content:space-between">
					<div>
						<div class="pln-setting__label">
							<?php esc_html_e( 'Selective Font Loading', 'olympus-google-fonts' ); ?>
							<?php if ( ! $is_pro ) : ?>
								<span class="ogf-pro-badge"><?php esc_html_e( 'PRO', 'olympus-google-fonts' ); ?></span>
							<?php endif; ?>
						</div>
						<div class="pln-setting__desc" style="margin-top:4px">
						<?php esc_html_e( 'Choose which weights to load per font and which language subsets to keep globally. Reduce page weight by disabling unused variants.', 'olympus-google-fonts' ); ?>
						</div>
					</div>
				</div>
			</div>

			<?php if ( empty( $google_fonts ) ) : ?>
				<?php
				\Plinth\Components::card(
					'',
					function() {
						?>
						<p><?php esc_html_e( 'No Google Fonts are currently loaded. Add fonts in the Customizer first, then come back here to choose which styles to load.', 'olympus-google-fonts' ); ?></p>
						<?php
					}
				);
				?>
			<?php else : ?>
				<?php foreach ( $google_fonts as $font_id => $font ) : ?>
					<?php $this->render_font_loading_card( $font_id, $font, $is_pro ); ?>
				<?php endforeach; ?>
			<?php endif; ?>
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
		$disabled_subsets = $is_pro ? get_theme_mod( 'fpp_disable_subsets', array() ) : array();
		if ( ! is_array( $disabled_subsets ) ) {
			$disabled_subsets = array();
		}
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
								data-kind="weight"
								data-weight="<?php echo esc_attr( $key ); ?>"
								<?php if ( ! $is_pro ) : ?>
									data-feature="<?php esc_attr_e( 'Selective Font Loading', 'olympus-google-fonts' ); ?>"
									data-desc="<?php esc_attr_e( 'Choose exactly which font weights and subsets to load. Disable unused variants to reduce page weight.', 'olympus-google-fonts' ); ?>"
								<?php endif; ?>></button>
							<span class="ogf-fl-grid-label"><?php echo esc_html( $this->weight_label( $key ) ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $font['subsets'] ) ) : ?>
				<div class="ogf-fl-section-title"><?php esc_html_e( 'Subsets (global)', 'olympus-google-fonts' ); ?></div>
				<div class="ogf-fl-grid">
					<?php foreach ( $font['subsets'] as $subset ) : ?>
						<?php $is_subset_enabled = ! in_array( $subset, $disabled_subsets, true ); ?>
						<div class="ogf-fl-grid-row">
							<button class="ogf-toggle-xs<?php echo $is_subset_enabled ? ' on' : ''; ?><?php echo ! $is_pro ? ' disabled' : ''; ?>"
								data-font="<?php echo esc_attr( $font_id ); ?>"
								data-kind="subset"
								data-subset="<?php echo esc_attr( $subset ); ?>"
								<?php if ( ! $is_pro ) : ?>
									data-feature="<?php esc_attr_e( 'Selective Font Loading', 'olympus-google-fonts' ); ?>"
									data-desc="<?php esc_attr_e( 'Choose exactly which font weights and subsets to load. Disable unused variants to reduce page weight.', 'olympus-google-fonts' ); ?>"
								<?php endif; ?>></button>
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
	 * Render Upload Fonts tab content (callback for Plinth Shell).
	 *
	 * @param \Plinth\Shell $shell Shell instance.
	 */
	public function render_tab_upload( $shell = null ) {
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
			<?php \Plinth\Components::panel_header( __( 'Upload Fonts', 'olympus-google-fonts' ), __( 'Add your own font files. Uploaded fonts are hosted on your domain and appear alongside Google Fonts.', 'olympus-google-fonts' ) ); ?>
			<?php
			$variant_label = sprintf(
				/* translators: %d: number of uploaded font variants */
				_n( '%d variant', '%d variants', count( $terms ), 'olympus-google-fonts' ),
				count( $terms )
			);
			\Plinth\Components::card(
				__( 'Custom Fonts', 'olympus-google-fonts' ),
				function() use ( $terms ) {
					if ( empty( $terms ) ) {
						?>
						<div class="pln-card__body">
							<p><?php esc_html_e( 'No custom fonts uploaded yet.', 'olympus-google-fonts' ); ?></p>
						</div>
						<?php
						return;
					}

					foreach ( $terms as $term ) {
						$this->render_custom_font_row( $term );
					}
				},
				array(
					'header_meta' => $variant_label,
					'wrap_body'   => false,
				)
			);

			\Plinth\Components::card(
				__( 'Upload a Custom Font', 'olympus-google-fonts' ),
				function() {
					?>
					<p class="ogf-upload-intro">
						<?php esc_html_e( 'Add a font file for each weight and style you need. Uploaded fonts are hosted on your domain and appear in the Customizer alongside Google Fonts.', 'olympus-google-fonts' ); ?>
					</p>
					<?php
					$this->render_custom_font_form( 0, null );
				}
			);
			?>
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
		$is_pro = ogf_is_pro_active();

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
						<button type="button" class="pln-btn--outline ogf-cf-upload"><?php esc_html_e( 'Upload', 'olympus-google-fonts' ); ?></button>
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
	 * Render Adobe Fonts tab content (callback for Plinth Shell).
	 *
	 * @param \Plinth\Shell $shell Shell instance.
	 */
	public function render_tab_adobe( $shell = null ) {
		$typekit_settings = get_option( 'fp-typekit', array() );
		$api_key          = is_array( $typekit_settings ) && isset( $typekit_settings['api_key'] ) ? $typekit_settings['api_key'] : '';
		$kits             = get_option( 'fp-typekit-data', false );
		$has_kits         = is_array( $kits ) && ! empty( $kits );
		?>
			<?php \Plinth\Components::panel_header( __( 'Adobe Fonts', 'olympus-google-fonts' ), __( 'Connect an Adobe Fonts (Typekit) account to use the families from your web projects.', 'olympus-google-fonts' ) ); ?>
			<?php
			\Plinth\Components::card(
				__( 'Adobe Fonts Configuration', 'olympus-google-fonts' ),
				function() use ( $api_key, $has_kits, $kits ) {
					?>
					<p class="ogf-adobe-intro">
						<?php esc_html_e( 'Connect your Adobe Fonts (Typekit) account to use your web project fonts.', 'olympus-google-fonts' ); ?>
						<a class="ogf-tk-link" target="_blank" href="https://fonts.adobe.com/account/tokens"><?php esc_html_e( 'Find your API token', 'olympus-google-fonts' ); ?></a>
					</p>
					<div class="ogf-adobe-input-group">
						<input type="text" id="ogf-typekit-key" value="<?php echo esc_attr( $api_key ); ?>" placeholder="<?php esc_attr_e( 'Paste your Adobe Fonts API token...', 'olympus-google-fonts' ); ?>" autocomplete="off" spellcheck="false">
						<button type="button" class="ogf-btn-pro" id="ogf-typekit-save"><?php esc_html_e( 'Save Token', 'olympus-google-fonts' ); ?></button>
						<?php if ( $api_key ) : ?>
							<button type="button" class="pln-btn--outline" id="ogf-typekit-refresh"><?php esc_html_e( 'Refresh Fonts', 'olympus-google-fonts' ); ?></button>
						<?php endif; ?>
					</div>
					<?php if ( $api_key && ! $has_kits ) : ?>
						<p class="ogf-fl-note">
							<?php
							if ( is_array( $kits ) ) {
								esc_html_e( 'No kits were found for this token.', 'olympus-google-fonts' );
							} else {
								esc_html_e( 'We could not connect to Adobe Fonts. Please check your token and try again.', 'olympus-google-fonts' );
							}
							?>
						</p>
					<?php endif; ?>
					<?php
				}
			);
			?>

			<?php if ( $has_kits ) : ?>
				<?php
				$kit_count_label = sprintf(
					/* translators: %d: number of Adobe Fonts kits */
					_n( '%d kit found', '%d kits found', count( $kits ), 'olympus-google-fonts' ),
					count( $kits )
				);
				\Plinth\Components::card(
					__( 'Your Kits', 'olympus-google-fonts' ),
					function() use ( $kits ) {
						foreach ( $kits as $kit_id => $kit ) {
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
									<button type="button" class="pln-btn--outline ogf-tk-toggle" data-kit="<?php echo esc_attr( $kit_id ); ?>" data-enabled="<?php echo $is_enabled ? '1' : '0'; ?>">
										<?php echo $is_enabled ? esc_html__( 'Disable', 'olympus-google-fonts' ) : esc_html__( 'Enable', 'olympus-google-fonts' ); ?>
									</button>
								</div>
							</div>
							<?php
						}
					},
					$kit_count_label
				);
				?>
			<?php endif; ?>
		<?php
	}

	/**
	 * Render Diagnostics tab content (callback for Plinth Shell).
	 *
	 * @param \Plinth\Shell $shell Shell instance.
	 */
	public function render_tab_diagnostics( $shell = null ) {
		$shell = $shell ? $shell : $this->get_shell();
		$fonts     = $this->get_active_fonts();
		$is_pro    = ogf_is_pro_active();
		$theme     = wp_get_theme();
		$ogf_fonts = OGF_Fonts::get_instance();
		$has_cache = false;

		if ( $ogf_fonts->has_google_fonts() ) {
			$url       = $ogf_fonts->build_url();
			$url_to_id = md5( $url );
			$has_cache = false !== get_transient( 'ogf_external_font_css_' . $url_to_id );
		}

		$type_counts     = $this->get_font_type_counts( $fonts );
		$google_count    = $type_counts['google'];
		$custom_count    = $type_counts['custom'];
		$adobe_count     = $type_counts['adobe'];
		$host_locally    = $is_pro && get_theme_mod( $this->get_setting_key( 'ogf_host_locally' ), false );
		$delivery        = $this->get_font_delivery_info( $google_count, $custom_count, $adobe_count, $host_locally );
		$active_plugins  = $this->get_active_plugins();
		$active_plugins_count = count( $active_plugins );
		?>
			<?php \Plinth\Components::panel_header( __( 'Diagnostics', 'olympus-google-fonts' ), __( 'Current configuration, environment details, and tools for clearing cached font data.', 'olympus-google-fonts' ) ); ?>
			<div class="pln-diag-grid">
				<?php
				\Plinth\Components::card(
					__( 'Configuration', 'olympus-google-fonts' ),
					function() use ( $delivery, $google_count, $custom_count, $adobe_count, $has_cache ) {
						?>
						<ul class="pln-diag-list">
							<li class="pln-diag__item">
								<span class="pln-diag__key"><?php esc_html_e( 'Loading Method', 'olympus-google-fonts' ); ?></span>
								<span class="pln-diag__val"><?php echo esc_html( implode( ', ', $delivery['labels'] ) ); ?></span>
							</li>
							<li class="pln-diag__item">
								<span class="pln-diag__key"><?php esc_html_e( 'Font Display', 'olympus-google-fonts' ); ?></span>
								<span class="pln-diag__val"><?php echo esc_html( get_theme_mod( 'ogf_font_display', 'swap' ) ); ?></span>
							</li>
							<li class="pln-diag__item">
								<span class="pln-diag__key"><?php esc_html_e( 'Force Styles', 'olympus-google-fonts' ); ?></span>
								<?php if ( get_theme_mod( 'ogf_force_styles', false ) ) : ?>
									<span class="pln-diag__status ok"><?php esc_html_e( 'On', 'olympus-google-fonts' ); ?></span>
								<?php else : ?>
									<span class="pln-diag__status warn"><?php esc_html_e( 'Off', 'olympus-google-fonts' ); ?></span>
								<?php endif; ?>
							</li>
							<li class="pln-diag__item">
								<span class="pln-diag__key"><?php esc_html_e( 'Google Fonts', 'olympus-google-fonts' ); ?></span>
								<span class="pln-diag__val"><?php echo esc_html( $google_count ); ?></span>
							</li>
							<li class="pln-diag__item">
								<span class="pln-diag__key"><?php esc_html_e( 'Custom Fonts', 'olympus-google-fonts' ); ?></span>
								<span class="pln-diag__val"><?php echo esc_html( $custom_count ); ?></span>
							</li>
							<li class="pln-diag__item">
								<span class="pln-diag__key"><?php esc_html_e( 'Adobe Fonts', 'olympus-google-fonts' ); ?></span>
								<span class="pln-diag__val"><?php echo esc_html( $adobe_count ); ?></span>
							</li>
							<li class="pln-diag__item">
								<span class="pln-diag__key"><?php esc_html_e( 'Cache Status', 'olympus-google-fonts' ); ?></span>
								<?php if ( $has_cache ) : ?>
									<span class="pln-diag__status ok"><?php esc_html_e( 'Active', 'olympus-google-fonts' ); ?></span>
								<?php else : ?>
									<span class="pln-diag__status warn"><?php esc_html_e( 'Empty', 'olympus-google-fonts' ); ?></span>
								<?php endif; ?>
							</li>
						</ul>
						<?php
					},
					array( 'wrap_body' => false )
				);

				\Plinth\Components::card(
					__( 'System', 'olympus-google-fonts' ),
					function() use ( $is_pro, $theme, $active_plugins, $active_plugins_count ) {
						?>
						<ul class="pln-diag-list">
							<li class="pln-diag__item">
								<span class="pln-diag__key"><?php esc_html_e( 'Plugin Version', 'olympus-google-fonts' ); ?></span>
								<span class="pln-diag__val"><?php echo esc_html( OGF_VERSION ); ?></span>
							</li>
							<li class="pln-diag__item">
								<span class="pln-diag__key"><?php esc_html_e( 'Version Status', 'olympus-google-fonts' ); ?></span>
								<?php if ( $is_pro ) : ?>
									<span class="pln-diag__status ok"><?php esc_html_e( 'Pro', 'olympus-google-fonts' ); ?></span>
								<?php else : ?>
									<span class="pln-diag__val"><?php esc_html_e( 'Free', 'olympus-google-fonts' ); ?></span>
								<?php endif; ?>
							</li>
							<li class="pln-diag__item">
								<span class="pln-diag__key"><?php esc_html_e( 'WordPress', 'olympus-google-fonts' ); ?></span>
								<span class="pln-diag__val"><?php echo esc_html( get_bloginfo( 'version' ) ); ?></span>
							</li>
							<li class="pln-diag__item">
								<span class="pln-diag__key"><?php esc_html_e( 'PHP', 'olympus-google-fonts' ); ?></span>
								<span class="pln-diag__val"><?php echo esc_html( PHP_VERSION ); ?></span>
							</li>
							<li class="pln-diag__item">
								<span class="pln-diag__key"><?php esc_html_e( 'Active Theme', 'olympus-google-fonts' ); ?></span>
								<span class="pln-diag__val"><?php echo esc_html( $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' ) ); ?></span>
							</li>
							<li class="pln-diag__item">
								<span class="pln-diag__key"><?php esc_html_e( 'Active Plugins', 'olympus-google-fonts' ); ?></span>
								<span class="pln-diag__val"><?php echo esc_html( $active_plugins_count ); ?></span>
							</li>
						</ul>
						<?php if ( $active_plugins_count > 0 ) : ?>
							<ul class="ogf-diag-plugins-report" hidden aria-hidden="true">
								<?php foreach ( $active_plugins as $plugin ) : ?>
									<li><?php echo esc_html( $plugin['name'] . ' ' . $plugin['version'] ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
						<?php
					},
					array( 'wrap_body' => false )
				);
				?>
			</div>

			<?php
			\Plinth\Components::card(
				__( 'Actions', 'olympus-google-fonts' ),
				function() use ( $shell ) {
					?>
					<p class="ogf-diag-intro"><?php esc_html_e( 'Copy the report above to paste into a support ticket. Cache stores a copy of the Google Fonts CSS locally, and reset removes all font assignments made by the plugin.', 'olympus-google-fonts' ); ?></p>
					<div class="pln-diag__actions">
						<button id="ogf-copy-diagnostics" class="pln-btn--outline">
							<?php $shell->icon( 'copy', 14 ); ?>
							<?php esc_html_e( 'Copy Diagnostics', 'olympus-google-fonts' ); ?>
						</button>
						<button id="ogf-clear-cache" class="pln-btn--outline">
							<?php $shell->icon( 'globe', 14 ); ?>
							<?php esc_html_e( 'Clear Font Cache', 'olympus-google-fonts' ); ?>
						</button>
						<button id="ogf-reset-fonts" class="pln-btn--outline pln-btn--danger">
							<?php $shell->icon( 'trash', 14 ); ?>
							<?php esc_html_e( 'Reset All Fonts', 'olympus-google-fonts' ); ?>
						</button>
					</div>
					<?php
				},
				array( 'class' => 'ogf-card--spaced' )
			);
			?>
		<?php
	}

	/**
	 * Render the upgrade modal.
	 */
	public function render_upgrade_modal( $shell = null ) {
		$shell          = $shell ? $shell : $this->get_shell();
		$needs_license  = ogf_is_pro_plugin_installed() && ! ogf_is_pro_active();
		$license_url    = admin_url( 'admin.php?page=fpp_license_page' );
		$upgrade_url    = 'https://fontsplugin.com/pro-upgrade/?utm_source=plugin&utm_medium=admin_page';
		?>
		<div id="ogf-modal" class="ogf-modal">
			<div class="ogf-modal-card" role="dialog" aria-modal="true" aria-labelledby="ogf-modal-title">
				<div class="ogf-modal-header">
					<h2 id="ogf-modal-title"><?php echo $needs_license ? esc_html__( 'Inactive License', 'olympus-google-fonts' ) : ''; ?></h2>
					<button class="ogf-modal-close" aria-label="<?php esc_attr_e( 'Close', 'olympus-google-fonts' ); ?>">
						<?php $shell->icon( 'close', 20 ); ?>
					</button>
				</div>
				<div class="ogf-modal-body">
					<p id="ogf-modal-desc"><?php echo $needs_license ? esc_html__( 'Please add your Fonts Plugin Pro license key to use this feature.', 'olympus-google-fonts' ) : ''; ?></p>
					<?php if ( ! $needs_license ) : ?>
					<ul class="ogf-modal-benefits">
						<li><?php $shell->icon( 'check', 14, 2.5 ); ?><?php esc_html_e( 'One-click activation', 'olympus-google-fonts' ); ?></li>
						<li><?php $shell->icon( 'check', 14, 2.5 ); ?><?php esc_html_e( 'Works with all themes', 'olympus-google-fonts' ); ?></li>
						<li><?php $shell->icon( 'check', 14, 2.5 ); ?><?php esc_html_e( 'Priority support included', 'olympus-google-fonts' ); ?></li>
					</ul>
					<?php endif; ?>
				</div>
				<div class="ogf-modal-footer">
					<?php if ( ! $needs_license ) : ?>
					<button type="button" class="ogf-btn-modal-cancel ogf-modal-close"><?php esc_html_e( 'Maybe later', 'olympus-google-fonts' ); ?></button>
					<?php endif; ?>
					<?php if ( $needs_license ) : ?>
					<a id="ogf-modal-link" href="<?php echo esc_url( $license_url ); ?>" class="ogf-btn-pro">
						<?php esc_html_e( 'Add License', 'olympus-google-fonts' ); ?>
					</a>
					<?php else : ?>
					<a id="ogf-modal-link" href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" class="ogf-btn-pro">
						<?php esc_html_e( 'Upgrade to Pro', 'olympus-google-fonts' ); ?>
						<?php $shell->icon( 'arrow-right', 14, 2.5 ); ?>
					</a>
					<?php endif; ?>
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
		return array(
			'ogf_host_locally',
			'ogf_preloading',
		);
	}

	/**
	 * Resolve a free-plugin setting to the corresponding Pro setting when Pro
	 * is active.
	 *
	 * @param string $setting Free-plugin setting name.
	 * @return string Setting name used by the active implementation.
	 */
	private function get_setting_key( $setting ) {
		if ( ! ogf_is_pro_active() ) {
			return $setting;
		}

		$pro_settings = array(
			'ogf_host_locally' => 'fpp_host_locally',
			'ogf_preloading'   => 'fpp_preloading',
			'ogf_use_woff2'    => 'fpp_use_woff2',
		);

		return isset( $pro_settings[ $setting ] ) ? $pro_settings[ $setting ] : $setting;
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

		if ( ! in_array( $setting, $this->get_allowed_settings(), true ) ) {
			wp_send_json_error( 'invalid_setting' );
		}

		set_theme_mod( $this->get_setting_key( $setting ), '1' === $value );

		wp_send_json_success();
	}

	/**
	 * AJAX handler: save selective font loading settings.
	 *
	 * Weight selections are stored per Google font. Subset exclusions are a
	 * single Pro setting shared by all Google fonts, so subset requests update
	 * that global list one value at a time.
	 */
	public function ajax_save_font_loading() {
		check_ajax_referer( 'ogf_admin_nonce' );

		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to do that.', 'olympus-google-fonts' ) ) );
		}

		if ( ! ogf_is_pro_active() ) {
			wp_send_json_error( array( 'message' => __( 'Selective font loading is a Pro feature.', 'olympus-google-fonts' ) ) );
		}

		$font_id = isset( $_POST['font_id'] ) ? sanitize_key( wp_unslash( $_POST['font_id'] ) ) : '';
		$kind    = isset( $_POST['kind'] ) ? sanitize_key( wp_unslash( $_POST['kind'] ) ) : '';

		if ( ! $font_id || ! ogf_is_google_font( $font_id ) || ! in_array( $kind, array( 'weight', 'subset' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'That font loading setting is not valid.', 'olympus-google-fonts' ) ) );
		}

		$fonts = OGF_Fonts::get_instance();

		if ( 'weight' === $kind ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON array; each entry is sanitized after decode.
			$raw_values = isset( $_POST['values'] ) ? wp_unslash( $_POST['values'] ) : '';

			if ( ! is_string( $raw_values ) ) {
				wp_send_json_error( array( 'message' => __( 'The selected weights are not valid.', 'olympus-google-fonts' ) ) );
			}

			$values = json_decode( $raw_values, true );

			if ( ! is_array( $values ) ) {
				wp_send_json_error( array( 'message' => __( 'The selected weights are not valid.', 'olympus-google-fonts' ) ) );
			}

			$selected = array();
			foreach ( $values as $value ) {
				if ( is_scalar( $value ) ) {
					$selected[] = sanitize_text_field( (string) $value );
				}
			}

			$available = array_keys( $fonts->get_font_weights( $font_id ) );
			$selected  = array_values( array_unique( array_intersect( $selected, $available ) ) );

			if ( empty( $selected ) ) {
				wp_send_json_error( array( 'message' => __( 'Select at least one weight.', 'olympus-google-fonts' ) ) );
			}

			if ( count( $selected ) === count( $available ) ) {
				remove_theme_mod( $font_id . '_weights' );
			} else {
				set_theme_mod( $font_id . '_weights', $selected );
			}

			wp_send_json_success();
		}

		$subset            = isset( $_POST['subset'] ) ? sanitize_text_field( wp_unslash( $_POST['subset'] ) ) : '';
		$enabled           = ! empty( $_POST['enabled'] );
		$available_subsets = array();

		foreach ( $fonts->choices as $choice ) {
			if ( ogf_is_google_font( $choice ) ) {
				$available_subsets = array_merge( $available_subsets, array_keys( $fonts->get_font_subsets( $choice ) ) );
			}
		}
		$available_subsets = array_values( array_unique( $available_subsets ) );

		if ( ! $subset || ! in_array( $subset, $available_subsets, true ) ) {
			wp_send_json_error( array( 'message' => __( 'That font subset is not valid.', 'olympus-google-fonts' ) ) );
		}

		$disabled = get_theme_mod( 'fpp_disable_subsets', array() );
		$disabled = is_array( $disabled ) ? $disabled : array();

		if ( $enabled ) {
			$disabled = array_values( array_diff( $disabled, array( $subset ) ) );
		} elseif ( ! in_array( $subset, $disabled, true ) ) {
			$disabled[] = $subset;
		}

		set_theme_mod( 'fpp_disable_subsets', array_values( array_unique( $disabled ) ) );
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
