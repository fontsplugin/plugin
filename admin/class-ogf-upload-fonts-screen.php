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
		add_action( OGF_Fonts_Taxonomy::$taxonomy_slug . '_term_new_form_tag', array( $this, 'intro_text' ) );

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

		wp_enqueue_style( 'olympus-google-fonts-admin', plugins_url( 'admin/style.css', __DIR__ ), false, '1.0.0' );

		wp_enqueue_media();
		wp_enqueue_script( 'olympus-google-fonts-upload', plugins_url( 'assets/js/uploadFonts.js', __DIR__ ), array(), '1.0.1' );
	}

	/**
	 * Register custom font menu
	 */
	public function register_custom_fonts_menu() {
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
		$screen = get_current_screen();
		if ( ! $screen || $screen->id != 'edit-' . OGF_Fonts_Taxonomy::$taxonomy_slug ) {
			return;
		}

		?><style>#addtag div.form-field.term-slug-wrap, #edittag tr.form-field.term-slug-wrap { display: none; }
			#addtag div.form-field.term-description-wrap, #edittag tr.form-field.term-description-wrap { display: none; }</style><script>jQuery( document ).ready( function( $ ) {
				var $wrapper = $( '#addtag, #edittag' );
				$wrapper.find( 'tr.form-field.term-name-wrap p, div.form-field.term-name-wrap > p' ).text( '<?php esc_html_e( 'A unique name to describe this variant.', 'olympus-google-fonts' ); ?>' );
			} );</script>
			<p>
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
		if ( ! $screen || $screen->id != 'edit-' . OGF_Fonts_Taxonomy::$taxonomy_slug ) {
			return;
		}
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
	 * Add options page
	 */
	public function intro_text() {
		echo '><span>Documentation for this feature is available here: <a href="https://fontsplugin.com/upload-fonts-wordpress/">Upload Fonts to WordPress</a>.</span';
	}

	/**
	 * Add new Taxonomy data
	 */
	public function add_new_taxonomy_data() {
		$this->font_family_new_field( 'family', __( 'Font Family', 'olympus-google-fonts' ), __( 'The name of the font family. For example, Helvetica or Proxima Nova.', 'olympus-google-fonts' ) );
		$this->font_file_new_field( 'woff', __( 'WOFF Font File', 'olympus-google-fonts' ), '' );
		$this->font_file_new_field( 'woff2', __( 'WOFF2 Font File', 'olympus-google-fonts' ), '' );
		$this->font_file_new_field( 'otf', __( 'OpenType (.otf) Font File', 'olympus-google-fonts' ), '' );
		$this->font_file_new_field( 'ttf', __( 'TrueType (.ttf) Font File', 'olympus-google-fonts' ), '' );
		$this->font_weight_new_field( 'weight', __( 'Font Weight', 'olympus-google-fonts' ), '' );
		$this->font_style_new_field( 'style', __( 'Font Style', 'olympus-google-fonts' ), '' );
	}

	/**
	 * Edit Taxonomy data
	 *
	 * @param object $term taxonomy terms.
	 */
	public function edit_taxonomy_data( $term ) {
		$data = OGF_Fonts_Taxonomy::get_font_data( $term->term_id );
		$this->font_family_edit_field( 'family', __( 'Font Family', 'olympus-google-fonts' ), $data['family'], __( 'The name of the font family. For example, Helvetica or Proxima Nova.', 'olympus-google-fonts' ) );
		$this->font_file_edit_field( 'woff', __( 'Font .woff', 'olympus-google-fonts' ), $data['woff'], __( 'Upload the font\'s .woff file or enter the URL.', 'olympus-google-fonts' ) );
		$this->font_file_edit_field( 'woff2', __( 'Font .woff2', 'olympus-google-fonts' ), $data['woff2'], __( 'Upload the font\'s .woff2 file or enter the URL.', 'olympus-google-fonts' ) );
		$this->font_file_edit_field( 'ttf', __( 'Font .ttf', 'olympus-google-fonts' ), $data['ttf'], __( 'Upload the font\'s .ttf file or enter the URL.', 'olympus-google-fonts' ) );
		$this->font_file_edit_field( 'otf', __( 'Font .otf', 'olympus-google-fonts' ), $data['otf'], __( 'Upload the font\'s .otf file or enter the URL.', 'olympus-google-fonts' ) );
		$this->font_weight_edit_field( 'weight', __( 'Font Weight', 'olympus-google-fonts' ), $data['weight'] );
		$this->font_style_edit_field( 'style', __( 'Font Weight', 'olympus-google-fonts' ), $data['style'] );
		$this->font_preload_edit_field( 'preload', __( 'Preload Font', 'olympus-google-fonts' ), $data['preload'],  __( 'Preloading is a <a href="https://fontsplugin.com/pro-upgrade">Fonts Plugin Pro</a> feature.', 'olympus-google-fonts' )  );
	}


	/**
	 * Add Taxonomy data field
	 *
	 * @param string $id current term id.
	 * @param string $title font type title.
	 * @param string $description title font type description.
	 */
	protected function font_family_new_field( $id, $title, $description = '' ) {
		?>
		<div class="ogf-custom-fonts-file-wrap form-field term-<?php echo esc_attr( $id ); ?>-wrap ">
			<th scope="row">
				<label for="metadata-<?php echo esc_attr( $id ); ?>">
					<?php echo esc_html( $title ); ?>
				</label>
			</th>
			<td>
				<input id="metadata-<?php echo esc_attr( $id ); ?>" type="text" class="ogf-custom-fonts-family-input <?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( OGF_Fonts_Taxonomy::$taxonomy_slug ); ?>[<?php echo esc_attr( $id ); ?>]"/>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			</td>
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
	protected function font_family_edit_field( $id, $title, $value = '', $description = '' ) {
		?>
		<tr class="ogf-custom-fonts-file-wrap form-field term-<?php echo esc_attr( $id ); ?>-wrap ">
			<th scope="row">
				<label for="metadata-<?php echo esc_attr( $id ); ?>">
					<?php echo esc_html( $title ); ?>
				</label>
			</th>
			<td>
				<input id="metadata-<?php echo esc_attr( $id ); ?>" type="text" class="ogf-custom-fonts-family-input <?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( OGF_Fonts_Taxonomy::$taxonomy_slug ); ?>[<?php echo esc_attr( $id ); ?>]" value="<?php echo esc_attr( $value ); ?>" />
				<p class="description"><?php echo esc_html( $description ); ?></p>
			</td>
		</tr>
		<?php
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
			<div style="display:flex; width: 95%">
				<input style="flex:1; margin-right: .5rem" type="text" id="font-<?php echo esc_attr( $id ); ?>" class="ogf-custom-fonts-link-input <?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( OGF_Fonts_Taxonomy::$taxonomy_slug ); ?>[<?php echo esc_attr( $id ); ?>]" value="<?php echo esc_attr( $value ); ?>" />
				<a href="#" class="ogf-custom-fonts-upload button" data-upload-type="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Upload File', 'olympus-google-fonts' ); ?></a>
			</div>
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
				<input placeholder="https://" id="metadata-<?php echo esc_attr( $id ); ?>" type="text" class="ogf-custom-fonts-link-input <?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( OGF_Fonts_Taxonomy::$taxonomy_slug ); ?>[<?php echo esc_attr( $id ); ?>]" value="<?php echo esc_attr( $value ); ?>" />
				<a href="#" class="ogf-custom-fonts-upload button" data-upload-type="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Upload File', 'olympus-google-fonts' ); ?></a>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Add Taxonomy data field
	 *
	 * @param string $id current term id.
	 * @param string $title font type title.
	 * @param string $description title font type description.
	 */
	protected function font_weight_new_field( $id, $title, $description = '' ) {
		?>
		<div class="ogf-custom-fonts-file-wrap form-field term-<?php echo esc_attr( $id ); ?>-wrap" >
				<label for="metadata-<?php echo esc_attr( $id ); ?>">
					<?php echo esc_html( $title ); ?>
				</label>

				<select id="metadata-<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( OGF_Fonts_Taxonomy::$taxonomy_slug ); ?>[<?php echo esc_attr( $id ); ?>]">
					<option value="">All</option>
					<option value="100"><?php esc_html_e( 'Thin (100)', 'olympus-google-fonts' ); ?></option>
					<option value="200"><?php esc_html_e( 'Extra Light (200)', 'olympus-google-fonts' ); ?></option>
					<option value="300"><?php esc_html_e( 'Light (300)', 'olympus-google-fonts' ); ?></option>
					<option value="400"><?php esc_html_e( 'Normal (400)', 'olympus-google-fonts' ); ?></option>
					<option value="500"><?php esc_html_e( 'Medium (500)', 'olympus-google-fonts' ); ?></option>
					<option value="600"><?php esc_html_e( 'Semi Bold (600)', 'olympus-google-fonts' ); ?></option>
					<option value="700"><?php esc_html_e( 'Bold (700)', 'olympus-google-fonts' ); ?></option>
					<option value="800"><?php esc_html_e( 'Extra Bold (800)', 'olympus-google-fonts' ); ?></option>
					<option value="900"><?php esc_html_e( 'Black (900)', 'olympus-google-fonts' ); ?></option>
				</select>
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
	protected function font_weight_edit_field( $id, $title, $value = '', $description = '' ) {
		?>
		<tr class="ogf-custom-fonts-file-wrap form-field term-<?php echo esc_attr( $id ); ?>-wrap ">
			<th scope="row">
				<label for="metadata-<?php echo esc_attr( $id ); ?>">
					<?php echo esc_html( $title ); ?>
				</label>
			</th>
			<td>
				<select id="metadata-<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( OGF_Fonts_Taxonomy::$taxonomy_slug ); ?>[<?php echo esc_attr( $id ); ?>]">
					<option value="">All</option>
					<option value="100" <?php selected( $value, '100' ); ?>><?php esc_html_e( 'Thin (100)', 'olympus-google-fonts' ); ?></option>
					<option value="200" <?php selected( $value, '200' ); ?>><?php esc_html_e( 'Extra Light (200)', 'olympus-google-fonts' ); ?></option>
					<option value="300" <?php selected( $value, '300' ); ?>><?php esc_html_e( 'Light (300)', 'olympus-google-fonts' ); ?></option>
					<option value="400" <?php selected( $value, '400' ); ?>><?php esc_html_e( 'Normal (400)', 'olympus-google-fonts' ); ?></option>
					<option value="500" <?php selected( $value, '500' ); ?>><?php esc_html_e( 'Medium (500)', 'olympus-google-fonts' ); ?></option>
					<option value="600" <?php selected( $value, '600' ); ?>><?php esc_html_e( 'Semi Bold (600)', 'olympus-google-fonts' ); ?></option>
					<option value="700" <?php selected( $value, '700' ); ?>><?php esc_html_e( 'Bold (700)', 'olympus-google-fonts' ); ?></option>
					<option value="800" <?php selected( $value, '800' ); ?>><?php esc_html_e( 'Extra Bold (800)', 'olympus-google-fonts' ); ?></option>
					<option value="900" <?php selected( $value, '900' ); ?>><?php esc_html_e( 'Black (900)', 'olympus-google-fonts' ); ?></option>
				</select>
			</td>
		</tr>
		<?php
	}

	/**
	 * Add Taxonomy data field
	 *
	 * @param string $id current term id.
	 * @param string $title font type title.
	 * @param string $description title font type description.
	 */
	protected function font_style_new_field( $id, $title, $description = '' ) {
		?>
		<div class="ogf-custom-fonts-file-wrap form-field term-<?php echo esc_attr( $id ); ?>-wrap" >
				<label for="metadata-<?php echo esc_attr( $id ); ?>">
					<?php echo esc_html( $title ); ?>
				</label>
				<select id="metadata-<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( OGF_Fonts_Taxonomy::$taxonomy_slug ); ?>[<?php echo esc_attr( $id ); ?>]">
					<option value="">All</option>
					<option value="normal"><?php esc_html_e( 'Normal', 'olympus-google-fonts' ); ?></option>
					<option value="italic"><?php esc_html_e( 'Italic', 'olympus-google-fonts' ); ?></option>
					<option value="oblique"><?php esc_html_e( 'Oblique', 'olympus-google-fonts' ); ?></option>
				</select>
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
	protected function font_style_edit_field( $id, $title, $value = '', $description = '' ) {
		?>
		<tr class="ogf-custom-fonts-file-wrap form-field term-<?php echo esc_attr( $id ); ?>-wrap ">
			<th scope="row">
				<label for="metadata-<?php echo esc_attr( $id ); ?>">
					<?php echo esc_html( $title ); ?>
				</label>
			</th>
			<td>
				<select id="metadata-<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( OGF_Fonts_Taxonomy::$taxonomy_slug ); ?>[<?php echo esc_attr( $id ); ?>]">
					<option value="">All</option>
					<option value="normal" <?php selected( $value, 'normal' ); ?>><?php esc_html_e( 'Normal', 'olympus-google-fonts' ); ?></option>
					<option value="italic" <?php selected( $value, 'italic' ); ?>><?php esc_html_e( 'Italic', 'olympus-google-fonts' ); ?></option>
					<option value="oblique" <?php selected( $value, 'oblique' ); ?>><?php esc_html_e( 'Oblique', 'olympus-google-fonts' ); ?></option>
				</select>
			</td>
		</tr>
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
	protected function font_preload_edit_field( $id, $title, $value = '', $description = '' ) {
		?>
		<tr class="ogf-custom-fonts-file-wrap form-field term-<?php echo esc_attr( $id ); ?>-wrap ">
			<th scope="row">
				<label for="metadata-<?php echo esc_attr( $id ); ?>">
					<?php echo esc_html( $title ); ?>
				</label>
			</th>
			<td>
				<?php if( defined('OGF_PRO') ) : ?>
					<input type="checkbox" id="metadata-<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( OGF_Fonts_Taxonomy::$taxonomy_slug ); ?>[<?php echo esc_attr( $id ); ?>]" <?php checked( $value, 1 ); ?> value="1">
				<?php else : ?>
					<p class="description"><?php echo wp_kses_post( $description ); ?></p>
				<?php endif; ?>
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
			OGF_Fonts_Taxonomy::update_font_data( $value, $term_id );
		}
	}

	/**
	 * Get the correct MIME type for a font file extension.
	 *
	 * @param string $extension The file extension.
	 * @return string The MIME type.
	 */
	private function get_font_mime_type( $extension ) {
		switch ( $extension ) {
			case 'ttf':
				$php_7_ttf_mime_type = PHP_VERSION_ID >= 70300 ? 'application/font-sfnt' : 'application/x-font-ttf';
				return PHP_VERSION_ID >= 70400 ? 'font/sfnt' : $php_7_ttf_mime_type;

			case 'otf':
				return 'application/vnd.ms-opentype';

			case 'woff':
				return PHP_VERSION_ID >= 80112 ? 'font/woff' : 'application/font-woff';

			case 'woff2':
				return PHP_VERSION_ID >= 80112 ? 'font/woff2' : 'application/font-woff2';

			default:
				return '';
		}
	}

	/**
	 * Add WOFF and WOFF2 to the allowed mime types.
	 *
	 * @param array $mimes Current array of mime types.
	 * @return array $mimes Updated array of mime types.
	 */
	public function add_to_allowed_mimes( $mimes ) {
		$mimes['otf']   = $this->get_font_mime_type( 'otf' );
		$mimes['ttf']   = $this->get_font_mime_type( 'ttf' );
		$mimes['woff']  = $this->get_font_mime_type( 'woff' );
		$mimes['woff2'] = $this->get_font_mime_type( 'woff2' );

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
		$extension = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

		// Get the MIME type using the same logic as add_to_allowed_mimes().
		$mime_type = $this->get_font_mime_type( $extension );

		if ( ! empty( $mime_type ) ) {
			$defaults['type'] = $mime_type;
			$defaults['ext']  = $extension;
		}

		return $defaults;
	}
}

OGF_Upload_Fonts_Screen::get_instance();
