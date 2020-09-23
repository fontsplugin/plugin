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
			5
		);

	}

	/**
	 * Add options page
	 */
	public function enqueue() {
		wp_enqueue_style( 'olympus-google-fonts-welcome', plugins_url( 'admin/style.css', dirname( __FILE__ ) ), false, '1.0.0' );
	}

	/**
	 * Options page callback
	 */
	public function render_upload_page() {
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
						<!-- Begin Mailchimp Signup Form -->
						<form action="https://fontsplugin.us9.list-manage.com/subscribe/post?u=1ed15f4383eb532a1a1034fb9&amp;id=2ed49283a0" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
							<input type="email" value="<?php echo sanitize_email( $email ); ?>" placeholder="Your email address..." name="EMAIL" class="required email" id="mce-EMAIL">
							<div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_1ed15f4383eb532a1a1034fb9_2ed49283a0" tabindex="-1" value=""></div>
							<input type="submit" value="Send My Guide!" name="subscribe" id="mc-embedded-subscribe" class="button">
						</form>
						<!--End mc_embed_signup-->
					</div>
				</div>
			</div>
			<?php
	}

	/**
	 * Options page callback
	 */
	public function render_welcome_page() {
		$current_user = wp_get_current_user();
		$email = ( string ) $current_user->user_email;
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
						<!-- Begin Mailchimp Signup Form -->
						<form action="https://fontsplugin.us9.list-manage.com/subscribe/post?u=1ed15f4383eb532a1a1034fb9&amp;id=2ed49283a0" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
							<input type="email" value="<?php echo sanitize_email( $email ); ?>" placeholder="Your email address..." name="EMAIL" class="required email" id="mce-EMAIL">
							<div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_1ed15f4383eb532a1a1034fb9_2ed49283a0" tabindex="-1" value=""></div>
							<input type="submit" value="Send My Guide!" name="subscribe" id="mc-embedded-subscribe" class="button">
						</form>
						<!--End mc_embed_signup-->
					</div>
				</div>
			</div>
			<?php
	}
}

if ( is_admin() ) {
	$my_settings_page = new OGF_Admin_Welcome_Screen();
}
