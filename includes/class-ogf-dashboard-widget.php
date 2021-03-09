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
			esc_html__( 'Typography News', 'olympus-google-fonts' ),
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

		include_once ABSPATH . WPINC . '/feed.php';

		$rss_items = get_transient( 'ogf_feed' );
		if ( false === $rss_items ) {

			$rss = fetch_feed( 'https://fontsplugin.com/feed/' );
			if ( is_wp_error( $rss ) ) {
				echo esc_html__( 'Temporarily unable to load feed.', 'olympus-google-fonts' );

				return;
			}
			$rss_items = $rss->get_items( 0, 4 ); // Show four items.

			$cached = array();
			foreach ( $rss_items as $item ) {
				$cached[] = array(
					'url'     => $item->get_permalink(),
					'title'   => $item->get_title(),
					'date'    => $item->get_date( 'M jS Y' ),
					'content' => substr( wp_strip_all_tags( $item->get_content() ), 0, 128 ) . '...',
				);
			}
			$rss_items = $cached;

			set_transient( 'ogf_feed', $cached, WEEK_IN_SECONDS );

		}

		?>

		<ul>
			<?php
			if ( false === $rss_items ) {
				echo esc_html__( 'Temporarily unable to load feed.', 'olympus-google-fonts' );

				return;
			}

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
