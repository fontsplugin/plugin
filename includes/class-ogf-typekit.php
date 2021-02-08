<?php
/**
 * Fonts Plugin Typekit Addon.
 *
 * @package olympus-google-fonts
 */

/**
 * Create the admin pages.
 */
class OGF_Typekit {

	/**
	 * Start up
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_head', array( $this, 'get_kits' ) );
		add_action( 'admin_head', array( $this, 'css_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_css' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_css' ) );
	}

	/**
	 * Add submenu page to Fonts Plugin menu.
	 */
	public function add_settings_page() {
		add_submenu_page(
			'fonts-plugin',
			__( 'Adobe Fonts', 'olympus-google-fonts' ),
			__( 'Adobe Fonts', 'olympus-google-fonts' ),
			'manage_options',
			'fonts-plugin-typekit',
			array( $this, 'render_settings_page' ),
		);
	}

	/**
	 * Register the settings and sections.
	 */
	public function register_settings() {
		register_setting( 'fonts-plugin', 'fp-typekit' );

		add_settings_section( 'section-1', __( 'Configuration', 'olympus-google-fonts' ), array( $this, 'render_config_section' ), 'fonts-plugin-typekit' );
		add_settings_section( 'section-2', __( 'Results', 'fonts-plugin-typekit' ), array( $this, 'render_results_section' ), 'fonts-plugin-typekit' );
		add_settings_field( 'api_key', __( 'API Key', 'olympus-google-fonts' ), array( $this, 'render_settings' ), 'fonts-plugin-typekit', 'section-1' );
	}

	/**
	 * Render the Typekit submenu page.
	 */
	public function render_settings_page() {
		?>
		<h2><?php esc_html_e( 'Adobe Fonts (Typekit) Configuration', 'olympus-google-fonts' ); ?></h2>
		<form action="options.php" method="post">
		<?php
		settings_fields( 'fonts-plugin' );
		do_settings_sections( 'fonts-plugin-typekit' );
		if ( get_option( 'fp-typekit-data', false ) ) {
			echo '<a class="button button-primary" href="' . esc_url( admin_url( 'admin.php?page=fonts-plugin-typekit&action=reset' ) ) . '">' . esc_html__( 'Refresh Fonts', 'olympus-google-fonts' ) . '</a>';
		}
		?>
		</form>
		<?php
	}

	/**
	 * Render the settings intro section of the Typekit page.
	 */
	public function render_config_section() {
			_e( '<p>You can retrieve your Adobe Fonts API Key here: <a target="_blank" href="https://fonts.adobe.com/account/tokens">https://fonts.adobe.com/account/tokens</a></p>', 'olympus-google-fonts' );
	}

	/**
	 * Render the Typekit settings.
	 */
	public function render_settings() {
		$settings = get_option( 'fp-typekit', array() );
		$value = '';

		if ( array_key_exists( 'api_key', $settings ) ) {
			$value = $settings['api_key'];
		}

		echo '<input type="text" name="fp-typekit[api_key]" value="' . esc_attr( $value ) . '" />';
		echo '<input name="submit" class="button button-primary" type="submit" value="' . esc_attr__( 'Save', 'olympus-google-fonts' ) . '" />';
	}

	/**
	 * Render the results section of the admin page.
	 */
	public function render_results_section() {
		echo '<p>' . esc_html__( 'The following data was retrieved from the Typekit API:', 'olympus-google-fonts' ) . '</p>';
		echo '<ul class="fp-typekit-results">';
		$kits = get_option( 'fp-typekit-data' );

		if ( ! is_array( $kits ) ) {
			return;
		}

		foreach ( $kits as $id => $kit ) {
			echo '<li><strong>' . esc_html__( 'Kit: ', 'olympus-google-fonts' ) . '</strong>' . esc_attr( $id ) . '</li><ul>';
			foreach ( $kit['families'] as $family ) {
				echo '<li><strong>' . esc_html__( 'Font Family: ', 'olympus-google-fonts' ) . '</strong>' . esc_attr( $family['label'] ) . '</li>';
			}
			echo '</ul>';
		}
		echo '</ul>';
	}

	/**
	 * Quickly add inline CSS styles.
	 */
	public function css_styles() {
		if ( get_current_screen()->id !== 'fonts-plugin_page_fonts-plugin-typekit' ) {
			return;
		}
		echo '<style>.fp-typekit-results > ul {padding: 0 0 .5rem .5rem}</style>';
	}

	/**
	 * Get kits from Typekit API.
	 */
	public function get_kits() {

		if ( isset( $_GET['action'] ) && $_GET['action'] === 'reset' ) {
			update_option( 'fp-typekit-data', false );
		}

		if ( get_current_screen()->id !== 'fonts-plugin_page_fonts-plugin-typekit' ) {
			return;
		}

		if ( get_option( 'fp-typekit-data', false ) ) {
			return;
		}

		$settings = get_option( 'fp-typekit', array() );

		if ( ! array_key_exists( 'api_key', $settings ) ) {
			return;
		}

		$api_key   = $settings['api_key'];
		$url       = 'https://typekit.com/api/v1/json/kits/';
		$curl_args = array();
		$response  = wp_remote_request( $url . '?token=' . esc_attr( $api_key ), $curl_args );

		if ( wp_remote_retrieve_response_code( $response ) != '200' ) {
			return;
		}

		$response_body = json_decode( wp_remote_retrieve_body( $response ) );
		$kits          = array();

		if ( is_array( $response_body->kits ) ) {
			// loop through the kits object.
			foreach ( $response_body->kits as $kit ) {
				// perform an API request for the individual kit.
				$data = $this->get_kit_from_api( $api_key, $kit->id );

				if ( $data ) {
					// Enable kits by default.
					$kits[ $kit->id ]['enabled'] = true;
					// loop through the kit and standardize the data.
					foreach ( $data->families as $family ) {
						$kits[ $kit->id ]['families'][] = array(
							'label'    => $family->name,
							'id'       => $family->slug,
							'variants' => array_map( array( $this, 'standardize_variant_names' ), $family->variations ),
							'stack'    => $family->css_stack,
						);
					}
				}
			}
		}
		// Save the results so we don't need to query the API again.
		update_option( 'fp-typekit-data', $kits );
	}

	/**
	 * Get kit data from API.
	 *
	 * @param string $api_key The API key.
	 * @param string $kit_id  The Kit ID we are looking for.
	 */
	public function get_kit_from_api( $api_key, $kit_id ) {
		$url           = 'https://typekit.com/api/v1/json/kits/' . esc_attr( $kit_id ) . '?token=' . esc_attr( $api_key );
		$curl_args     = array();
		$response      = wp_remote_request( $url, $curl_args );

		if ( wp_remote_retrieve_response_code( $response ) == '200' ) {
			$response_body = json_decode( wp_remote_retrieve_body( $response ) );
			return $response_body->kit;
		}

		return false;
	}

	/**
	 * Use the Fonts Plugin naming convention instead of Typekit's.
	 *
	 * @param string $variant The variant to standarize.
	 */
	public function standardize_variant_names( $variant ) {
		$variants_key = array(
			'n1' => '100',
			'n2' => '200',
			'n3' => '300',
			'n4' => '400',
			'n5' => '500',
			'n6' => '600',
			'n7' => '700',
			'n8' => '800',
			'n9' => '900',
			'i1' => '100i',
			'i2' => '200i',
			'i3' => '300i',
			'i4' => '400i',
			'i5' => '500i',
			'i6' => '600i',
			'i7' => '700i',
			'i8' => '800i',
			'i9' => '900i',
		);

		if ( array_key_exists( $variant, $variants_key ) ) {
			return $variants_key[ $variant ];
		} else {
			return $variant;
		}
	}

	/**
	 * Get Typekit fonts array.
	 */
	public static function get_fonts() {
		$fonts = array();
		$kits  = get_option( 'fp-typekit-data', array() );

		if ( ! is_array( $kits ) ) {
			return $fonts;
		}

		foreach ( $kits as $kit ) {
			foreach ( $kit['families'] as $family ) {
				$fonts[ 'tk-' . $family['id'] ] = array(
					'id'       => $family['id'],
					'label'    => $family['label'],
					'variants' => $family['variants'],
					'stack'    => $family['stack'],
				);
			}
		}
		return $fonts;
	}

	/**
	 * Enqueue typekit CSS files.
	 */
	public function enqueue_css() {
		$typekit_data = get_option( 'fp-typekit-data', array() );

		if ( is_array( $typekit_data ) ) {

			foreach ( $typekit_data as $id => $values ) {
				wp_enqueue_style( 'typekit-' . $id, 'https://use.typekit.com/' . $id . '.css', array(), OGF_VERSION );
			}
		}
	}

}

new OGF_Typekit();
