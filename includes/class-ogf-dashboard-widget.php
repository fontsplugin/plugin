<?php
/**
 * Dashboard Widget
 *
 * @package olympus-google-fonts
 */

/**
 * Class ogf_dashboard_widget
 */
class OGF_Dashboard_Widget {

	/**
	 * Constructor
	 *
	 * Add the action to the constructor.
	 */
	public function __construct() {
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );
	}

	/**
	 * Add Dashboard Widget
	 *
	 * @since 2.3.10
	 */
	public function add_dashboard_widget() {
		wp_add_dashboard_widget(
			'ogf-rss-feed',
			esc_html__( 'Typography News from Fonts Plugin', 'olympus-google-fonts' ),
			array(
				$this,
				'display_rss_dashboard_widget',
			)
		);
	}

	/**
	 * Show Widget
	 */
	public function show_widget() {
		$show = true;

		if ( apply_filters( 'ogf_show_news', true ) === false ) {
			// API filter hook to disable showing dashboard widget.
			return false;
		}

		return $show;
	}

	/**
	 * Display RSS Dashboard Widget
	 */
	public function display_rss_dashboard_widget() {
		// check if the user has chosen not to display this widget through screen options.
		$current_screen = get_current_screen();
		$hidden_widgets = get_user_meta( get_current_user_id(), 'metaboxhidden_' . $current_screen->id );
		if ( $hidden_widgets && count( $hidden_widgets ) > 0 && is_array( $hidden_widgets[0] ) && in_array( 'ogf-rss-feed', $hidden_widgets[0], true ) ) {
			return;
		}

		$rss_items = array(
			'first'  => array(
				'url'     => 'https://fontsplugin.com/speed-up-wordpress/',
				'title'   => 'How To Speed Up WordPress (A Non-Technical Guide)',
				'date'    => 'Nov 16th 2020',
				'content' => 'You hate slow websites, your visitors do too. Studies have shown that 47% of users expect pages to load in two seconds or less. ...',
			),
			'second' => array(
				'url'     => 'https://fontsplugin.com/disable-google-fonts/',
				'title'   => 'How to Disable Google Fonts in WordPress',
				'date'    => 'Aug 10th 2019',
				'content' => 'Google Fonts can be a great addition to your website that considerably improves your typography. However, they do add an externa...',
			),
			'third'  => array(
				'url'     => 'https://fontsplugin.com/how-to-download-google-fonts/',
				'title'   => 'How To Download Google Fonts',
				'date'    => 'May 29th 2019',
				'content' => 'Google Fonts are free for both personal and commercial use. That means you can download and use them in your projects without ha...',
			),
			'fourth' => array(
				'url'     => 'https://fontsplugin.com/google-fonts-univers/',
				'title'   => 'Google Fonts Similar to Univers',
				'date'    => 'May 29th 2019',
				'content' => 'Released in 1957, Univers is a sans-serif font designed by Adrian Frutiger and released by his employer Deberny & Peignot. ...',
			),
		);
		?>
		<ul>
			<?php
			foreach ( $rss_items as $item ) {
				?>
				<li>
					<a class="rsswidget" target="_blank" href="<?php echo esc_url( $item['url'] ); ?>">
						<?php echo esc_html( $item['title'] ); ?>
					</a>
					<div class="rssSummary ogf_news">
						<?php echo wp_kses_post( $item['content'] ); ?>
					</div>
				</li>
				<?php
			}
			?>
		</ul>
		<?php
	}
}

new OGF_Dashboard_Widget();
