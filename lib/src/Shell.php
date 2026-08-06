<?php

namespace Plinth;

class Shell {

	private $config = array();

	private $settings;

	private $icons = array(
		'check'         => '<polyline points="20 6 9 17 4 12"/>',
		'package'       => '<path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 002 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0022 16z"/>',
		'zap'           => '<path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>',
		'clock'         => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
		'edit'          => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 013 3L7 19l-4 1 1-4z"/>',
		'upload'        => '<path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>',
		'book'          => '<path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/>',
		'chevron-right' => '<path d="M9 18l6-6-6-6"/>',
		'arrow-right'   => '<path d="M5 12h14"/><path d="M12 5l7 7-7 7"/>',
		'globe'         => '<path d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.66 0 3-4.03 3-9s-1.34-9-3-9m0 18c-1.66 0-3-4.03-3-9s1.34-9 3-9"/>',
		'trash'         => '<path d="M3 6h18"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>',
		'close'         => '<path d="M18 6L6 18"/><path d="M6 6l12 12"/>',
		'activity'      => '<path d="M3 12h4l3 8 4-16 3 8h4"/>',
		'type'          => '<path d="M4 7V5h16v2M12 5v14M9 19h6"/>',
		'adobe'         => '<path d="M12 3l9 16H3l9-16z"/>',
		'settings'      => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 11-4 0v-.09A1.65 1.65 0 008 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H2a2 2 0 110-4h.09A1.65 1.65 0 004.6 8a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06A1.65 1.65 0 009 3.68 1.65 1.65 0 0010 2.17V2a2 2 0 114 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06A1.65 1.65 0 0020.32 8a1.65 1.65 0 001.51 1H22a2 2 0 110 4h-.09a1.65 1.65 0 00-1.51 1z"/>',
		'pulse'         => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
		'copy'          => '<rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>',
		'life-buoy'     => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/><path d="M4.93 4.93l4.24 4.24"/><path d="M14.83 14.83l4.24 4.24"/><path d="M14.83 9.17l4.24-4.24"/><path d="M4.93 19.07l4.24-4.24"/>',
	);

	public function __construct( array $config ) {
		$defaults = array(
			'slug'        => '',
			'title'       => '',
			'version'     => '1.0.0',
			'icon'        => '',
			'tabs'        => array(),
			'default_tab' => '',
			'capability'  => 'manage_options',
			'help'        => null,
			'nav_groups'  => null,
			'after_tabs'  => null,
		);

		$this->config   = array_merge( $defaults, $config );
		$this->settings = new Settings( $this->config['slug'] );
	}

	public function settings() {
		return $this->settings;
	}

	public function register_settings( array $definitions ) {
		$this->settings->register( $definitions );
	}

	public function register_menu( $parent = null ) {
		add_action( 'admin_menu', function () use ( $parent ) {
			if ( $parent ) {
				add_submenu_page(
					$parent,
					$this->config['title'],
					$this->config['title'],
					$this->config['capability'],
					$this->config['slug'],
					array( $this, 'render' )
				);
			} else {
				add_menu_page(
					$this->config['title'],
					$this->config['title'],
					$this->config['capability'],
					$this->config['slug'],
					array( $this, 'render' ),
					$this->config['icon'] ? $this->config['icon'] : 'dashicons-admin-generic'
				);
			}
		} );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		$this->settings->register_ajax();
	}

	public function enqueue( $hook ) {
		$slug   = $this->config['slug'];
		$suffix = $slug;

		if ( false === strpos( $hook, $slug ) ) {
			return;
		}

		$lib_url = $this->get_lib_url();

		wp_enqueue_style(
			'plinth-shell',
			$lib_url . 'assets/css/plinth.css',
			array(),
			$this->config['version']
		);

		wp_enqueue_script(
			'plinth-shell',
			$lib_url . 'assets/js/plinth.js',
			array(),
			$this->config['version'],
			true
		);

		wp_localize_script( 'plinth-shell', 'plinthAdmin', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( $slug . '_plinth_nonce' ),
			'slug'    => $slug,
			'i18n'    => array(
				'saved' => __( 'Saved', 'plinth' ),
				'error' => __( 'Something went wrong', 'plinth' ),
			),
		) );
	}

	private function get_lib_url() {
		$lib_dir  = dirname( __DIR__ );
		$abs_path = wp_normalize_path( ABSPATH );
		$lib_path = wp_normalize_path( $lib_dir );
		$relative = str_replace( $abs_path, '', $lib_path );

		return site_url( '/' . $relative . '/' );
	}

	public function render() {
		$tabs        = $this->config['tabs'];
		$default_tab = $this->config['default_tab'];
		$nav_groups  = $this->config['nav_groups'];

		if ( ! $default_tab && ! empty( $tabs ) ) {
			$default_tab = array_key_first( $tabs );
		}

		?>
		<div class="pln-wrap" data-default-tab="<?php echo esc_attr( $default_tab ); ?>">
			<nav class="pln-nav" aria-label="<?php echo esc_attr( $this->config['title'] ); ?>">
				<div class="pln-nav-head">
					<?php if ( $this->config['icon'] ) : ?>
						<div class="pln-nav-icon">
							<img src="<?php echo esc_url( $this->config['icon'] ); ?>" alt="" width="34" height="34">
						</div>
					<?php endif; ?>
					<div class="pln-nav-id">
						<span class="pln-nav-name"><?php echo esc_html( $this->config['title'] ); ?></span>
						<span class="pln-nav-version">v<?php echo esc_html( $this->config['version'] ); ?></span>
					</div>
				</div>

				<div class="pln-nav-list" role="tablist">
					<?php
					if ( $nav_groups ) {
						$this->render_nav_groups( $nav_groups, $default_tab );
					} else {
						$this->render_nav_flat( $tabs, $default_tab );
					}
					?>
				</div>

				<?php if ( $this->config['help'] ) : ?>
					<div class="pln-nav-foot">
						<div class="pln-help">
							<?php echo wp_kses_post( $this->config['help'] ); ?>
						</div>
					</div>
				<?php endif; ?>
			</nav>

			<div class="pln-main">
				<?php
				foreach ( $tabs as $slug => $tab ) {
					$is_active = $default_tab === $slug;
					$type      = isset( $tab['type'] ) ? $tab['type'] : 'custom';

					printf(
						'<div class="pln-tab-panel%s" id="pln-tab-%s">',
						$is_active ? ' active' : '',
						esc_attr( $slug )
					);

					if ( 'settings' === $type ) {
						$this->settings->render_tab( $tab );
					} elseif ( isset( $tab['callback'] ) && is_callable( $tab['callback'] ) ) {
						call_user_func( $tab['callback'], $this );
					}

					echo '</div>';
				}
				?>
			</div>

			<?php
			if ( isset( $this->config['after_tabs'] ) && is_callable( $this->config['after_tabs'] ) ) {
				call_user_func( $this->config['after_tabs'], $this );
			}
			?>

			<div id="pln-toast" class="pln-toast">
				<?php $this->icon( 'check', 16, 2.5 ); ?>
				<span id="pln-toast-msg"></span>
			</div>
		</div>
		<?php
	}

	private function render_nav_groups( array $groups, $default_tab ) {
		foreach ( $groups as $index => $group ) {
			$label = isset( $group['group'] ) ? $group['group'] : null;
			$items = isset( $group['items'] ) ? $group['items'] : array();

			if ( $label ) {
				echo '<div class="pln-nav-group">' . esc_html( $label ) . '</div>';
			} elseif ( $index > 0 ) {
				echo '<div class="pln-nav-rule"></div>';
			}

			foreach ( $items as $item ) {
				$this->render_nav_item( $item, $default_tab );
			}
		}
	}

	private function render_nav_flat( array $tabs, $default_tab ) {
		foreach ( $tabs as $slug => $tab ) {
			$this->render_nav_item(
				array(
					$slug,
					isset( $tab['label'] ) ? $tab['label'] : $slug,
					isset( $tab['icon'] ) ? $tab['icon'] : null,
					isset( $tab['count'] ) ? $tab['count'] : null,
				),
				$default_tab
			);
		}
	}

	private function render_nav_item( array $item, $default_tab ) {
		list( $slug, $label, $icon, $count ) = array_pad( $item, 4, null );
		?>
		<button class="pln-nav-item<?php echo $default_tab === $slug ? ' active' : ''; ?>" role="tab" data-tab="<?php echo esc_attr( $slug ); ?>">
			<?php
			if ( $icon ) {
				$this->icon( $icon, 15 );
			}
			?>
			<span class="pln-nav-label"><?php echo esc_html( $label ); ?></span>
			<?php if ( $count ) : ?>
				<span class="pln-nav-count"><?php echo esc_html( $count ); ?></span>
			<?php endif; ?>
		</button>
		<?php
	}

	public function icon( $name, $size = 16, $stroke = 2, $classname = '' ) {
		if ( ! isset( $this->icons[ $name ] ) ) {
			return;
		}

		$svg = sprintf(
			'<svg %1$swidth="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="%3$s" aria-hidden="true" focusable="false">%4$s</svg>',
			$classname ? 'class="' . esc_attr( $classname ) . '" ' : '',
			(int) $size,
			esc_attr( (string) $stroke ),
			$this->icons[ $name ]
		);

		echo wp_kses( $svg, $this->allowed_svg_tags() );
	}

	public function add_icon( $name, $path ) {
		$this->icons[ $name ] = $path;
	}

	private function allowed_svg_tags() {
		return array(
			'svg'      => array(
				'class'        => true,
				'width'        => true,
				'height'       => true,
				'viewbox'      => true,
				'fill'         => true,
				'stroke'       => true,
				'stroke-width' => true,
				'aria-hidden'  => true,
				'focusable'    => true,
			),
			'path'     => array( 'd' => true ),
			'polyline' => array( 'points' => true ),
			'circle'   => array(
				'cx' => true,
				'cy' => true,
				'r'  => true,
			),
			'rect'     => array(
				'x'      => true,
				'y'      => true,
				'width'  => true,
				'height' => true,
				'rx'     => true,
			),
			'line'     => array(
				'x1' => true,
				'y1' => true,
				'x2' => true,
				'y2' => true,
			),
		);
	}
}
