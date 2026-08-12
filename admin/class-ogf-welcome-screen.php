<?php
/**
 * Olympus Google Fonts Admin Pages.
 *
 * @package olympus-google-fonts
 */

/**
 * Create the admin pages.
 */
class OGF_Welcome_Screen {

	/**
	 * Start up
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ), 1 );
		add_action( 'load-toplevel_page_fonts-plugin', array( $this, 'suppress_admin_notices' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_ajax_ogf_dismiss_guide', array( $this, 'dismiss_guide' ) );
	}

	/**
	 * Remove wp-admin notices on the custom dashboard screen.
	 */
	public function suppress_admin_notices() {
		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page() {

		add_menu_page(
			__( 'Fonts Plugin', 'olympus-google-fonts' ),
			'Fonts Plugin',
			'manage_options',
			'fonts-plugin',
			array( $this, 'render_welcome_page' ),
			'dashicons-editor-textcolor',
			61
		);

		add_submenu_page(
			'fonts-plugin',
			__( 'Customize Fonts', 'olympus-google-fonts' ),
			__( 'Customize Fonts', 'olympus-google-fonts' ),
			'manage_options',
			esc_url( admin_url( '/customize.php?autofocus[panel]=ogf_google_fonts' ) ),
			'',
			5
		);

		add_submenu_page(
			'fonts-plugin',
			__( 'Documentation', 'olympus-google-fonts' ),
			__( 'Documentation', 'olympus-google-fonts' ),
			'manage_options',
			'https://docs.fontsplugin.com/',
			'',
			25
		);
	}

	/**
	 * Add options page
	 */
	public function enqueue() {

		if ( get_current_screen()->id === 'toplevel_page_fonts-plugin' ) {
			global $ogf_admin_page;
			if ( $ogf_admin_page instanceof OGF_Admin_Page ) {
				$ogf_admin_page->get_shell()->enqueue( 'toplevel_page_fonts-plugin' );
			}

			wp_enqueue_media();
			wp_enqueue_style( 'ogf-admin-page', plugins_url( 'admin/admin-page.css', __DIR__ ), array( 'plinth-shell' ), OGF_VERSION );
			wp_enqueue_script( 'ogf-admin-page', plugins_url( 'admin/admin-page.js', __DIR__ ), array( 'plinth-shell' ), OGF_VERSION, true );
			wp_localize_script(
				'ogf-admin-page',
				'ogfAdmin',
				array(
					'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
					'nonce'        => wp_create_nonce( 'ogf_admin_nonce' ),
					'upgradeUrl'   => 'https://fontsplugin.com/pro-upgrade/?utm_source=plugin&utm_medium=admin_page',
					'needsLicense' => ogf_is_pro_plugin_installed() && ! ogf_is_pro_active(),
					'licenseUrl'   => admin_url( 'admin.php?page=fpp_license_page' ),
					'i18n'         => array(
						'saved'             => __( 'Settings saved.', 'olympus-google-fonts' ),
						'error'             => __( 'Something went wrong.', 'olympus-google-fonts' ),
						'cleared'           => __( 'Cache cleared.', 'olympus-google-fonts' ),
						'clearCacheConfirm' => __( 'Are you sure you want to clear the font cache?', 'olympus-google-fonts' ),
						'resetFontsConfirm' => __( 'This will reset all fonts to their defaults. This action cannot be reversed.', 'olympus-google-fonts' ),
						'resetDone'         => __( 'All fonts have been reset.', 'olympus-google-fonts' ),
						'fontSaved'         => __( 'Font saved.', 'olympus-google-fonts' ),
						'fontDeleted'       => __( 'Font deleted.', 'olympus-google-fonts' ),
						'deleteFontConfirm' => __( 'Delete this font variant? This cannot be undone.', 'olympus-google-fonts' ),
						'selectFontFile'    => __( 'Select a font file', 'olympus-google-fonts' ),
						'useFontFile'       => __( 'Use this file', 'olympus-google-fonts' ),
						'kitUpdated'        => __( 'Kit updated.', 'olympus-google-fonts' ),
						'enableKit'         => __( 'Enable', 'olympus-google-fonts' ),
						'disableKit'        => __( 'Disable', 'olympus-google-fonts' ),
						'kitActive'         => __( 'Active', 'olympus-google-fonts' ),
						'kitInactive'       => __( 'Inactive', 'olympus-google-fonts' ),
						'saving'            => __( 'Saving…', 'olympus-google-fonts' ),
						'refreshing'        => __( 'Refreshing…', 'olympus-google-fonts' ),
						'diagnosticsCopied' => __( 'Diagnostics copied to clipboard.', 'olympus-google-fonts' ),
						'copyFailed'          => __( 'Could not copy. Select the text and copy it manually.', 'olympus-google-fonts' ),
						'inactiveLicenseTitle' => __( 'Inactive License', 'olympus-google-fonts' ),
						'inactiveLicenseDesc'  => __( 'Please add your Fonts Plugin Pro license key to use this feature.', 'olympus-google-fonts' ),
						'addLicense'           => __( 'Add License', 'olympus-google-fonts' ),
					),
				)
			);
		}

		wp_enqueue_script( 'ogf-admin', esc_url( OGF_DIR_URL . 'assets/js/admin.js' ), 'jquery', OGF_VERSION, false );
	}

	/**
	 * Options page callback
	 */
	public function render_welcome_page() {
		global $ogf_admin_page;

		if ( $ogf_admin_page instanceof OGF_Admin_Page ) {
			$ogf_admin_page->render();
		}
	}

	/**
	 * AJAX handler to store the state of dismissible notices.
	 */
	public function dismiss_guide() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'insufficient_permissions' );
		}

		update_option( 'ogf_dismiss_guide', true );
		wp_send_json_success();
	}
}

if ( is_admin() ) {
	$ogf_welcome_screen = new OGF_Welcome_Screen();
}
