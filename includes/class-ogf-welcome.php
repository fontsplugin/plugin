<?php
/**
 * Welcome Notice Class.
 *
 * @package   olympus-google-fonts
 * @copyright Copyright (c) 2020, Fonts Plugin
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! class_exists( 'OGF_Welcome' ) ) :
	/**
	 * The welcome.
	 */
	class OGF_Welcome {

		/**
		 * Slug.
		 *
		 * @var string $slug
		 */
		private $slug;

		/**
		 * Message.
		 *
		 * @var string $message
		 */
		private $message;

		/**
		 * Type.
		 *
		 * @var string $type
		 */
		private $type;

		/**
		 * Class constructor.
		 *
		 * @param string $slug Slug.
		 * @param string $message Message.
		 * @param string $type Type.
		 */
		public function __construct( $slug, $message, $type = 'success' ) {
			$this->slug    = $slug;
			$this->message = $message;
			$this->type    = $type;

			// Add actions.
			add_action( 'admin_notices', array( $this, 'display_admin_notice' ) );
			add_action( 'wp_ajax_ogf_dismiss_notice', array( $this, 'dismiss_notice' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

		}

		/**
		 * Enqeue the styles and scripts.
		 */
		public function enqueue() {

			wp_enqueue_script( 'ogf-dismiss-welcome', esc_url( OGF_DIR_URL . 'assets/js/dismiss.js' ), 'jquery', OGF_VERSION, false );

		}

		/**
		 * AJAX handler to store the state of dismissible notices.
		 */
		public function dismiss_notice() {

			if ( isset( $_POST['type'] ) ) {
				// Pick up the notice "type" - passed via jQuery (the "data-notice" attribute on the notice).
				$type = sanitize_text_field( wp_unslash( $_POST['type'] ) );
				// Store it in the options table.
				update_option( 'dismissed-' . $type, true );
			}

		}

		/**
		 * Display the admin notice.
		 */
		public function display_admin_notice() {

			if ( get_option( 'dismissed-' . $this->slug, false ) ) {
				return;
			}
			?>

			<div class="notice notice-<?php echo esc_attr( $this->type ); ?> is-dismissible notice-dismiss-dc"  data-notice="<?php echo esc_attr( $this->slug ); ?>">
				<p>
					<?php
						echo $this->message; // WPCS: XSS ok.
					?>
				</p>
			</div>
			<?php
		}

	}
endif;

$message = sprintf(
	// translators: %s Link to Google Fonts customizer panel.
	__( 'Thank you for installing <strong>Google Fonts for WordPress</strong>! Configure your fonts here: <a href="%s">WordPress Customizer</a>', 'olympus-google-fonts' ),
	esc_url( admin_url( '/customize.php?autofocus[panel]=ogf_google_fonts' ) )
);

/*
* Instantiate the OGF_Welcome class.
*/
new OGF_Welcome( 'ogf-welcome', $message, 'success' );
