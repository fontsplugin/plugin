<?php
/**
 * Upload  Fonts Admin UI
 *
 * @package olympus-google-fonts
 */

/**
 * OGF_Upload_Fonts_Screen
 */
class OGF_Upload_Fonts_Screen {

	/**
	 * Instance of OGF_Upload_Fonts_Screen
	 *
	 * @var (Object) OGF_Upload_Fonts_Screen
	 */
	private static $instance = null;

	/**
	 * Parent Menu Slug
	 *
	 * @var (string) $parent_menu_slug
	 */
	protected $parent_menu_slug = 'fonts-plugin';

	/**
	 * Instance of OGF_Upload_Fonts_Screen.
	 *
	 * @return object Class object.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

		add_action( 'admin_menu', array( $this, 'register_custom_fonts_menu' ), 101 );
		add_action( 'admin_head', array( $this, 'customize_output' ) );

		add_filter( 'manage_edit-' . OGF_Fonts_Taxonomy::$taxonomy_slug . '_columns', array( $this, 'manage_columns' ) );

		add_action( OGF_Fonts_Taxonomy::$taxonomy_slug . '_add_form_fields', array( $this, 'add_new_taxonomy_data' ) );
		add_action( OGF_Fonts_Taxonomy::$taxonomy_slug . '_edit_form_fields', array( $this, 'edit_taxonomy_data' ) );

		add_action( 'edited_' . OGF_Fonts_Taxonomy::$taxonomy_slug, array( $this, 'save_metadata' ) );
		add_action( 'create_' . OGF_Fonts_Taxonomy::$taxonomy_slug, array( $this, 'save_metadata' ) );

		add_filter( 'upload_mimes', array( $this, 'add_to_allowed_mimes' ) );
		add_filter( 'wp_check_filetype_and_ext', array( $this, 'update_mime_types' ), 10, 3 );
	}

	/**
	 * Add options page
	 */
	public function enqueue() {
		if ( get_current_screen()->id !== 'edit-ogf_custom_fonts' ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script( 'olympus-google-fonts-upload', plugins_url( 'assets/js/uploadFonts.js', dirname( __FILE__ ) ), [], '1.0.0' );
	}

	/**
	 * Register custom font menu
	 */
	public function register_custom_fonts_menu() {
		if ( ! defined( 'OGF_PRO' ) ) {
			return;
		}

		$title = apply_filters( 'ogf_custom_fonts_menu_title', __( 'Upload Fonts', 'olympus-google-fonts' ) );
		add_submenu_page(
			$this->parent_menu_slug,
			$title,
			$title,
			OGF_Fonts_Taxonomy::$capability,
			'edit-tags.php?taxonomy=' . OGF_Fonts_Taxonomy::$taxonomy_slug
		);
	}

	/**
	 * Modify taxonomy output.
	 */
	public function customize_output() {
		global $parent_file, $submenu_file;

		if ( 'edit-tags.php?taxonomy=' . OGF_Fonts_Taxonomy::$taxonomy_slug === $submenu_file ) {
			$parent_file = $this->parent_menu_slug; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
		if ( get_current_screen()->id != 'edit-' . OGF_Fonts_Taxonomy::$taxonomy_slug ) {
			return;
		}

		?><style>#addtag div.form-field.term-slug-wrap, #edittag tr.form-field.term-slug-wrap { display: none; }
			#addtag div.form-field.term-description-wrap, #edittag tr.form-field.term-description-wrap { display: none; }</style><script>jQuery( document ).ready( function( $ ) {
				var $wrapper = $( '#addtag, #edittag' );
				$wrapper.find( 'tr.form-field.term-name-wrap p, div.form-field.term-name-wrap > p' ).text( '<?php esc_html_e( 'The name of the font as it appears in the customizer options.', 'olympus-google-fonts' ); ?>' );
			} );</script>
			<?php
	}

	/**
	 * Manage Columns
	 *
	 * @param array $columns default columns.
	 * @return array $columns updated columns.
	 */
	public function manage_columns( $columns ) {
		$screen = get_current_screen();
		// If current screen is add new custom fonts screen.
		if ( isset( $screen->base ) && 'edit-tags' == $screen->base ) {

			$old_columns = $columns;
			$columns     = array(
				'cb'   => $old_columns['cb'],
				'name' => $old_columns['name'],
			);

		}
		return $columns;
	}

	/**
	 * Add new Taxonomy data
	 */
	public function add_new_taxonomy_data() {
		$this->font_file_new_field( 'woff', __( 'WOFF Font File', 'olympus-google-fonts' ), '' );
		$this->font_file_new_field( 'woff2', __( 'WOFF2 Font File', 'olympus-google-fonts' ), '' );
		$this->font_file_new_field( 'otf', __( 'OpenType (.otf) Font File', 'olympus-google-fonts' ), '' );
		$this->font_file_new_field( 'ttf', __( 'TrueType (.ttf) Font File', 'olympus-google-fonts' ), '' );
	}

	/**
	 * Edit Taxonomy data
	 *
	 * @param object $term taxonomy terms.
	 */
	public function edit_taxonomy_data( $term ) {
		$data = OGF_Fonts_Taxonomy::get_font_links( $term->term_id );
		$this->font_file_edit_field( 'woff', __( 'Font .woff', 'olympus-google-fonts' ), $data['woff'], __( 'Upload the font\'s .woff file or enter the URL.', 'olympus-google-fonts' ) );
		$this->font_file_edit_field( 'woff2', __( 'Font .woff2', 'olympus-google-fonts' ), $data['woff2'], __( 'Upload the font\'s .woff2 file or enter the URL.', 'olympus-google-fonts' ) );
		$this->font_file_edit_field( 'ttf', __( 'Font .ttf', 'olympus-google-fonts' ), $data['ttf'], __( 'Upload the font\'s .ttf file or enter the URL.', 'olympus-google-fonts' ) );
		$this->font_file_edit_field( 'otf', __( 'Font .otf', 'olympus-google-fonts' ), $data['otf'], __( 'Upload the font\'s .otf file or enter the URL.', 'olympus-google-fonts' ) );

	}

	/**
	 * Add Taxonomy data field
	 *
	 * @param string $id current term id.
	 * @param string $title font type title.
	 * @param string $description title font type description.
	 * @param string $value title font type meta values.
	 */
	protected function font_file_new_field( $id, $title, $description, $value = '' ) {
		?>
		<div class="ogf-custom-fonts-file-wrap form-field term-<?php echo esc_attr( $id ); ?>-wrap" >

			<label for="font-<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $title ); ?></label>
			<input type="text" id="font-<?php echo esc_attr( $id ); ?>" class="ogf-custom-fonts-link <?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( OGF_Fonts_Taxonomy::$taxonomy_slug ); ?>[<?php echo esc_attr( $id ); ?>]" value="<?php echo esc_attr( $value ); ?>" />
			<a href="#" class="ogf-custom-fonts-upload button" data-upload-type="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Upload', 'olympus-google-fonts' ); ?></a>
			<p><?php echo esc_html( $description ); ?></p>
		</div>
		<?php
	}

	/**
	 * Add Taxonomy data field
	 *
	 * @param string $id current term id.
	 * @param string $title font type title.
	 * @param string $value title font type meta values.
	 * @param string $description title font type description.
	 */
	protected function font_file_edit_field( $id, $title, $value = '', $description = '' ) {
		?>
		<tr class="ogf-custom-fonts-file-wrap form-field term-<?php echo esc_attr( $id ); ?>-wrap ">
			<th scope="row">
				<label for="metadata-<?php echo esc_attr( $id ); ?>">
					<?php echo esc_html( $title ); ?>
				</label>
			</th>
			<td>
				<input id="metadata-<?php echo esc_attr( $id ); ?>" type="text" class="ogf-custom-fonts-link <?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( OGF_Fonts_Taxonomy::$taxonomy_slug ); ?>[<?php echo esc_attr( $id ); ?>]" value="<?php echo esc_attr( $value ); ?>" />
				<a href="#" class="ogf-custom-fonts-upload button" data-upload-type="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Upload', 'olympus-google-fonts' ); ?></a>
				<p><?php echo esc_html( $description ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save Taxonomy meta data value
	 *
	 * @since 1.0.0
	 * @param int $term_id current term id.
	 */
	public function save_metadata( $term_id ) {
		if ( isset( $_POST[ OGF_Fonts_Taxonomy::$taxonomy_slug ] ) ) {// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$value = array_map( 'esc_attr', $_POST[ OGF_Fonts_Taxonomy::$taxonomy_slug ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			OGF_Fonts_Taxonomy::update_font_links( $value, $term_id );
		}
	}

	/**
	 * Add WOFF and WOFF2 to the allowed mime types.
	 *
	 * @param array $mimes Current array of mime types.
	 * @return array $mimes Updated array of mime types.
	 */
	public function add_to_allowed_mimes( $mimes ) {

		$php_7_ttf_mime_type = PHP_VERSION_ID >= 70300 ? 'application/font-sfnt' : 'application/x-font-ttf';

		$mimes['otf']   = 'application/vnd.ms-opentype';
		$mimes['ttf']   = PHP_VERSION_ID >= 70400 ? 'font/sfnt' : $php_7_ttf_mime_type;
		$mimes['woff']  = PHP_VERSION_ID >= 80112 ? 'font/woff' : 'application/font-woff';
		$mimes['woff2'] = PHP_VERSION_ID >= 80112 ? 'font/woff2' : 'application/font-woff2';

		return $mimes;
	}

	/**
	 * Correct the mime types and extension for the font types.
	 *
	 * @param array  $defaults File data array containing 'ext', 'type', and
	 *                                          'proper_filename' keys.
	 * @param string $file                      Full path to the file.
	 * @param string $filename                  The name of the file (may differ from $file due to
	 *                                          $file being in a tmp directory).
	 * @return Array File data array containing 'ext', 'type', and
	 */
	public function update_mime_types( $defaults, $file, $filename ) {
		if ( 'ttf' === pathinfo( $filename, PATHINFO_EXTENSION ) ) {
			$defaults['type'] = 'application/x-font-ttf';
			$defaults['ext']  = 'ttf';
		}

		if ( 'otf' === pathinfo( $filename, PATHINFO_EXTENSION ) ) {
			$defaults['type'] = 'application/x-font-otf';
			$defaults['ext']  = 'otf';
		}

		return $defaults;
	}
}

OGF_Upload_Fonts_Screen::get_instance();
