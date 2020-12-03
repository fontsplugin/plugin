<?php
/**
 * Olympus Google Fonts Admin Pages.
 *
 * @package olympus-google-fonts
 */

/**
 * Create the admin pages.
 */
class OGF_Admin_Welcome_Screen {

	/**
	 * Start up
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ), 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_ajax_ogf_dismiss_guide', array( $this, 'dismiss_guide' ) );
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
		wp_enqueue_style( 'olympus-google-fonts-welcome', plugins_url( 'admin/style.css', dirname( __FILE__ ) ), false, '1.0.0' );
		wp_enqueue_script( 'ogf-admin', esc_url( OGF_DIR_URL . 'assets/js/admin.js' ), 'jquery', OGF_VERSION, false );
	}

	/**
	 * Options page callback
	 */
	public function render_welcome_page() {
		?>
			<div class="eb-wrap">
				<div class="eb-content">
					<div class="eb-content__header">
						<h1>Your Quickstart Guide</h1>
					</div>
					<div class="eb-content__inner">
						<img class="ebook-cover" src="<?php echo esc_url( plugins_url( 'admin/fonts-plugin-quickstart-guide.png', dirname( __FILE__ ) ) ); ?>">
						<p>To help you get the most out of the Google Fonts plugin we’ve put together a free quickstart guide.</p>
						<p>In this beautifully-formatted, easy-to-read PDF you will learn:
						<ul>
							<li>How to <strong>easily</strong> customize your typography.</li>
							<li>How to host fonts <strong>locally</strong> for speed, GDPR & DSGVO.</li>
							<li>How to use Google Fonts without <strong>slowing down</strong> your website.</li>
						</ul>
						<p>Download your free copy today.</p>

						<?php if ( get_option( 'ogf_dismiss_guide', false ) === false ) : ?>
							<form action="https://fontsplugin.email/subscribe" method="post" class="validate" target="_blank" novalidate>
								<input type="email" value="" placeholder="Your email address..." name="email" class="required email" id="mce-EMAIL">
								<input type="hidden" name="list" value="2guyf8U56tOENOh6892lBQ6w"/>
		<input type="hidden" name="subform" value="yes"/>
								<input type="submit" value="Send My Guide!" name="submit" class="ogf-send-guide-button button">
							</form>
						<?php else : ?>

							<a class="ogf-send-guide-button button" href="https://fontsplugin.com/wp-content/uploads/qs-guide.pdf" target="_blank">Read Guide</a>

						<?php endif; ?>

					</div>
				</div>
			</div>
			<?php
	}

	/**
	 * AJAX handler to store the state of dismissible notices.
	 */
	public function dismiss_guide() {
		// Store it in the options table.
		update_option( 'ogf_dismiss_guide', true );
	}
}

if ( is_admin() ) {
	$my_settings_page = new OGF_Admin_Welcome_Screen();
}
