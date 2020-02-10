<?php
/**
 * Deactivation Feedback Class.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * The feedback.
 */
class OGF_Deactivation {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		// Add actions.
		add_action( 'wp_ajax_ogf_submit_feedback', array( $this, 'ogf_submit_feedback' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Enqeue the styles and scripts.
	 *
	 * @param string $hook The hook tells us which page of wp-admin we are on.
	 */
	public function enqueue( $hook ) {

		if ( 'plugins.php' !== $hook ) {
			return;
		}

		wp_enqueue_script( 'featherlight', esc_url( OGF_DIR_URL . 'assets/js/featherlight.min.js' ), 'jquery', OGF_VERSION, true );
		wp_enqueue_script( 'ogf-admin-modal', esc_url( OGF_DIR_URL . 'assets/js/deactivation.js' ), 'jquery', OGF_VERSION, false );

		wp_enqueue_style( 'ogf-deactivation', esc_url( OGF_DIR_URL . 'assets/css/deactivation.css' ), '', OGF_VERSION );

	}

	/**
	 * Remove WordPress email so annonymity is kept.
	 *
	 * @param  $string $email WordPress email address.
	 */
	public function ogf_mail_from_email( $email ) {
		return 'team@fontsplugin.com';
	}

	/**
	 * Remove WordPress name so annonymity is kept.
	 *
	 * @param  $string $name WordPress install name.
	 */
	public function ogf_mail_from_name( $name ) {
		return 'Deactivation Survey [OGF]';
	}

	/**
	 * Send user feedback regarding plugin deactivation.
	 */
	public function ogf_submit_feedback() {

		$current_user = wp_get_current_user();
		$url          = site_url();
		$user         = $current_user->user_email;
		$theme        = wp_get_theme();
		$reason       = ( isset( $_POST['reason'] ) ? wp_unslash( $_POST['reason'] ) : '' );
		$explanation  = ( isset( $_POST['explanation'] ) ? wp_unslash( $_POST['explanation'] ) : '' );
		$anon         = ( isset( $_POST['anon'] ) ? wp_unslash( $_POST['anon'] ) : '' );

		if ( ! $explanation ) {
			return;
		}

		add_filter( 'wp_mail_from_name', array( $this, 'ogf_mail_from_name' ) );
		add_filter( 'wp_mail_from', array( $this, 'ogf_mail_from_email' ) );

		if ( 'true' === $anon ) {
			$url  = 'https://google.com';
			$user = 'anon@anonymous.com';
		}

		$to      = 'hello@fontsplugin.com';
		$subject = 'Deactivation Survey';
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		$body = '<html><body>' .
						'<p>Version: ' . OGF_VERSION . '</p>' .
						'<p>URL: ' . esc_url( $url ) . '</p>' .
						'<p>User: ' . sanitize_email( $user ) . '</p>' .
						'<p>Theme: ' . esc_attr( $theme->get( 'Name' ) ) . '</p>' .
						'<p>Reason: ' . esc_html( $reason ) . '</p>' .
						'<p>Explanation: ' . esc_html( $explanation ) . '</p>' .
						'</html></body>';

		wp_mail( $to, $subject, $body, $headers );

		remove_filter( 'wp_mail_from_name', array( $this, 'ogf_mail_from_name' ) );
		remove_filter( 'wp_mail_from', array( $this, 'ogf_mail_from_email' ) );

		wp_die();
	}
}

/*
* Instantiate the OGF_Deactivation class.
*/
new OGF_Deactivation();
