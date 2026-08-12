<?php
/**
 * Build the customizer controls for Optimization options.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2019, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * OGF_Optimization_Controls Class.
 */
class OGF_Optimization_Controls {

	/**
	 * The constructor.
	 */
	public function __construct() {

		if ( ogf_is_pro_active() ) {
			return;
		}

		add_action( 'customize_register', array( $this, 'register_settings' ) );
		add_action( 'customize_register', array( $this, 'register_section' ) );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'customize_controls_enqueue' ), 110 );
		add_action( 'customize_controls_print_footer_scripts', array( $this, 'render_pro_modal' ) );
	}

	/**
	 * Pass Pro feature metadata to the customizer controls script.
	 */
	public function customize_controls_enqueue() {
		wp_localize_script(
			'ogf-customize-controls',
			'ogfCustomizerPro',
			array(
				'upgradeUrl'   => 'https://fontsplugin.com/pro-upgrade/?utm_source=plugin&utm_medium=customizer',
				'needsLicense'        => ogf_is_pro_plugin_installed() && ! ogf_is_pro_active(),
				'licenseUrl'          => admin_url( 'admin.php?page=fpp_license_page' ),
				'inactiveLicenseTitle' => __( 'Inactive License', 'olympus-google-fonts' ),
				'inactiveLicenseDesc'  => __( 'Please add your Fonts Plugin Pro license key to use this feature.', 'olympus-google-fonts' ),
				'addLicense'           => __( 'Add License', 'olympus-google-fonts' ),
				'fontLoading'  => array(
					'title' => __( 'Selective Font Loading', 'olympus-google-fonts' ),
					'desc'  => __( 'Choose exactly which font weights and subsets to load. Disable unused variants to reduce page weight.', 'olympus-google-fonts' ),
				),
				'features'     => array(
					'ogf_host_locally' => array(
						'title' => __( 'Host Google Fonts Locally', 'olympus-google-fonts' ),
						'desc'  => __( 'Download and serve Google Fonts from your own domain instead of the CDN.', 'olympus-google-fonts' ),
					),
					'ogf_use_woff2'    => array(
						'title' => __( 'Use WOFF2 File Format', 'olympus-google-fonts' ),
						'desc'  => __( 'Serve locally hosted fonts in the WOFF2 format for smaller file sizes and faster loading.', 'olympus-google-fonts' ),
					),
					'ogf_preloading'   => array(
						'title' => __( 'Enable Preloading', 'olympus-google-fonts' ),
						'desc'  => __( 'Add preload resource hints for active fonts.', 'olympus-google-fonts' ),
					),
					'ogf_removal'      => array(
						'title' => __( 'Remove External Fonts', 'olympus-google-fonts' ),
						'desc'  => __( 'Remove Google Fonts loaded by other plugins and your theme to reduce external requests and improve page speed.', 'olympus-google-fonts' ),
					),
					'ogf_rewrite'      => array(
						'title' => __( 'Rewrite External Fonts', 'olympus-google-fonts' ),
						'desc'  => __( 'Automatically rewrite Google Fonts URLs from your theme and plugins to serve from your domain, improving privacy and performance.', 'olympus-google-fonts' ),
					),
				),
			)
		);
	}

	/**
	 * Output the Pro upgrade modal markup in the Customizer.
	 */
	public function render_pro_modal() {
		$needs_license = ogf_is_pro_plugin_installed() && ! ogf_is_pro_active();
		$license_url   = admin_url( 'admin.php?page=fpp_license_page' );
		$upgrade_url   = 'https://fontsplugin.com/pro-upgrade/?utm_source=plugin&utm_medium=customizer';
		?>
		<div id="ogf-customizer-modal" class="ogf-customizer-modal" aria-hidden="true">
			<div class="ogf-customizer-modal__card" role="dialog" aria-modal="true" aria-labelledby="ogf-customizer-modal-title">
				<div class="ogf-customizer-modal__header">
					<h2 id="ogf-customizer-modal-title"><?php echo $needs_license ? esc_html__( 'Inactive License', 'olympus-google-fonts' ) : ''; ?></h2>
					<button type="button" class="ogf-customizer-modal__close ogf-modal-close" aria-label="<?php esc_attr_e( 'Close', 'olympus-google-fonts' ); ?>">&times;</button>
				</div>
				<div class="ogf-customizer-modal__body">
					<p id="ogf-customizer-modal-desc"><?php echo $needs_license ? esc_html__( 'Please add your Fonts Plugin Pro license key to use this feature.', 'olympus-google-fonts' ) : ''; ?></p>
					<?php if ( ! $needs_license ) : ?>
					<ul class="ogf-customizer-modal__benefits">
						<li><?php esc_html_e( 'One-click activation', 'olympus-google-fonts' ); ?></li>
						<li><?php esc_html_e( 'Works with all themes', 'olympus-google-fonts' ); ?></li>
						<li><?php esc_html_e( 'Priority support included', 'olympus-google-fonts' ); ?></li>
					</ul>
					<?php endif; ?>
				</div>
				<div class="ogf-customizer-modal__footer">
					<?php if ( ! $needs_license ) : ?>
					<button type="button" class="button ogf-modal-close"><?php esc_html_e( 'Maybe later', 'olympus-google-fonts' ); ?></button>
					<?php endif; ?>
					<?php if ( $needs_license ) : ?>
					<a id="ogf-customizer-modal-link" href="<?php echo esc_url( $license_url ); ?>" class="button button-primary">
						<?php esc_html_e( 'Add License', 'olympus-google-fonts' ); ?>
					</a>
					<?php else : ?>
					<a id="ogf-customizer-modal-link" href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noopener noreferrer" class="button button-primary">
						<?php esc_html_e( 'Upgrade to Pro', 'olympus-google-fonts' ); ?>
					</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Register the Customizer section.
	 *
	 * @param WP_Customize_Manager $wp_customize the Customizer object.
	 */
	public function register_section( $wp_customize ) {
		$description = '<p>' . esc_html__( 'Optimize the delivery of font files for improved performance and user-privacy.', 'olympus-google-fonts' ) . '</p>';

		if ( ! ogf_is_pro_plugin_installed() ) {
			$description .= '<p>' . sprintf(
				/* translators: %s: link to Fonts Plugin Pro */
				__( 'Upgrade to %s to unlock these features.', 'olympus-google-fonts' ),
				'<a href="https://fontsplugin.com/pro-upgrade/?utm_source=plugin&utm_medium=customizer&utm_campaign=ogf_optimization_intro">Fonts Plugin Pro</a>'
			) . '</p>';
		}

		$wp_customize->add_section(
			'ogf_optimization',
			array(
				'title'       => __( 'Optimization', 'olympus-google-fonts' ),
				'description' => $description,
				'panel'       => 'ogf_google_fonts',
			)
		);
	}

	/**
	 * Register the Customizer setting.
	 *
	 * @param WP_Customize_Manager $wp_customize the Customizer object.
	 */
	public function register_settings( $wp_customize ) {

		$site_url = site_url( '', 'https' );
		$url      = preg_replace( '(^https?://)', '', $site_url );
		$pro_args = array(
			'default'           => false,
			'transport'         => 'postMessage',
			'sanitize_callback' => array( $this, 'sanitize_pro_setting' ),
		);

		$wp_customize->add_setting(
			'ogf_host_locally',
			$pro_args
		);

		$wp_customize->add_control(
			'ogf_host_locally',
			array(
				'label'       => esc_html__( 'Host Google Fonts Locally', 'olympus-google-fonts' ),
				/* translators: %s: URL where fonts will be served from */
				'description' => sprintf( esc_html__( 'Fonts will be served from %s instead of fonts.googleapis.com.', 'olympus-google-fonts' ), $url ),
				'section'     => 'ogf_optimization',
				'type'        => 'checkbox',
				'settings'    => 'ogf_host_locally',
			)
		);

		$wp_customize->add_setting(
			'ogf_use_woff2',
			$pro_args
		);

		$wp_customize->add_control(
			'ogf_use_woff2',
			array(
				'label'       => esc_html__( 'Use WOFF2 File Format', 'olympus-google-fonts' ),
				'description' => esc_html__( 'Use the WOFF2 file format instead of WOFF.', 'olympus-google-fonts' ),
				'section'     => 'ogf_optimization',
				'type'        => 'checkbox',
				'settings'    => 'ogf_use_woff2',
			)
		);

		$wp_customize->add_setting(
			'ogf_preloading',
			$pro_args
		);

		$wp_customize->add_control(
			'ogf_preloading',
			array(
				'label'       => esc_html__( 'Enable Preloading', 'olympus-google-fonts' ),
				'description' => esc_html__( 'Add preload resource hints.', 'olympus-google-fonts' ),
				'section'     => 'ogf_optimization',
				'type'        => 'checkbox',
				'settings'    => 'ogf_preloading',
			)
		);

		$wp_customize->add_setting(
			'ogf_removal',
			$pro_args
		);

		$wp_customize->add_control(
			'ogf_removal',
			array(
				'label'       => esc_html__( 'Remove External Fonts', 'olympus-google-fonts' ),
				'description' => esc_html__( 'Remove Google Fonts loaded by other plugins and your theme.', 'olympus-google-fonts' ),
				'section'     => 'ogf_optimization',
				'type'        => 'checkbox',
				'settings'    => 'ogf_removal',
			)
		);

		$wp_customize->add_setting(
			'ogf_rewrite',
			$pro_args
		);

		$wp_customize->add_control(
			'ogf_rewrite',
			array(
				'label'       => esc_html__( 'Rewrite External Fonts', 'olympus-google-fonts' ),
				'description' => esc_html__( 'Convert fonts added by your theme and plugins to be locally hosted on your domain.', 'olympus-google-fonts' ),
				'section'     => 'ogf_optimization',
				'type'        => 'checkbox',
				'settings'    => 'ogf_rewrite',
			)
		);
	}

	/**
	 * Pro optimization settings cannot be enabled from the free Customizer UI.
	 *
	 * @param mixed $value Submitted value.
	 * @return bool
	 */
	public function sanitize_pro_setting( $value ) {
		return (bool) $value && ogf_is_pro_active();
	}
}

$ogf_optimization_controls = new OGF_Optimization_Controls();
