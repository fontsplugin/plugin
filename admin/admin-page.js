/**
 * Fonts Plugin (olympus-google-fonts) Admin Page JS.
 *
 * Relies on localized data from WordPress via window.ogfAdmin.
 *
 * @package suspended
 */

( function() {
	'use strict';

	var toastTimer = null;

	/**
	 * POST helper wrapping fetch + FormData.
	 *
	 * @param {string} action WP AJAX action name.
	 * @param {Object} data   Key/value pairs to send.
	 * @return {Promise}       Parsed JSON response.
	 */
	function ajaxPost( action, data ) {
		var formData = new FormData();
		formData.append( 'action', action );
		formData.append( '_ajax_nonce', ogfAdmin.nonce );

		Object.keys( data ).forEach( function( key ) {
			formData.append( key, data[ key ] );
		} );

		return fetch( ogfAdmin.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: formData,
		} ).then( function( response ) {
			return response.json();
		} );
	}

	/**
	 * Display a toast notification.
	 *
	 * @param {string} message Text to show.
	 */
	function showToast( message ) {
		var toast = document.getElementById( 'ogf-toast' );
		if ( ! toast ) {
			return;
		}

		if ( toastTimer ) {
			clearTimeout( toastTimer );
		}

		toast.textContent = message;
		toast.classList.add( 'visible' );

		toastTimer = setTimeout( function() {
			toast.classList.remove( 'visible' );
			toastTimer = null;
		}, 3000 );
	}

	/* ---------------------------------------------------------------
	 * Tab switching
	 * ------------------------------------------------------------- */
	function initTabs() {
		var buttons = document.querySelectorAll( '.ogf-tab-btn' );
		var panels  = document.querySelectorAll( '.ogf-tab-panel' );

		function activateTab( name ) {
			buttons.forEach( function( btn ) {
				btn.classList.toggle( 'active', btn.getAttribute( 'data-tab' ) === name );
			} );
			panels.forEach( function( panel ) {
				panel.classList.toggle( 'active', panel.id === 'ogf-tab-' + name );
			} );
		}

		buttons.forEach( function( btn ) {
			btn.addEventListener( 'click', function() {
				var tab = btn.getAttribute( 'data-tab' );
				activateTab( tab );
				window.location.hash = '#' + tab;
			} );
		} );

		// Activate tab from hash or fall back to dashboard.
		var hash = window.location.hash.replace( '#', '' );
		activateTab( hash || 'dashboard' );
	}

	/* ---------------------------------------------------------------
	 * Setting toggles
	 * ------------------------------------------------------------- */
	function initToggles() {
		document.querySelectorAll( '.ogf-setting-row .ogf-toggle' ).forEach( function( toggle ) {
			toggle.addEventListener( 'click', function() {
				if ( toggle.classList.contains( 'disabled' ) ) {
					return;
				}

				toggle.classList.toggle( 'on' );

				var setting = toggle.getAttribute( 'data-setting' );
				var value   = toggle.classList.contains( 'on' ) ? 1 : 0;

				ajaxPost( 'ogf_save_settings', { setting: setting, value: value } )
					.then( function() {
						showToast( ogfAdmin.i18n.saved );
					} )
					.catch( function() {
						showToast( ogfAdmin.i18n.error );
					} );
			} );
		} );
	}

	/* ---------------------------------------------------------------
	 * Pro feature modal
	 * ------------------------------------------------------------- */
	function openProModal( featureTitle, featureDesc ) {
		var modal = document.getElementById( 'ogf-modal' );
		if ( ! modal ) {
			return;
		}

		var titleEl = document.getElementById( 'ogf-modal-title' );
		var descEl  = document.getElementById( 'ogf-modal-desc' );

		if ( titleEl ) {
			titleEl.textContent = featureTitle || '';
		}
		if ( descEl ) {
			descEl.textContent = featureDesc || '';
		}

		// Build the campaign slug from the feature title.
		var slug = ( featureTitle || 'pro' )
			.toLowerCase()
			.replace( /[^a-z0-9]+/g, '-' )
			.replace( /^-|-$/g, '' );

		var link = document.getElementById( 'ogf-modal-link' );
		if ( link && ogfAdmin.upgradeUrl ) {
			link.href = ogfAdmin.upgradeUrl + '&utm_campaign=' + encodeURIComponent( slug );
		}

		modal.classList.add( 'visible' );
	}

	function closeProModal() {
		var modal = document.getElementById( 'ogf-modal' );
		if ( modal ) {
			modal.classList.remove( 'visible' );
		}
	}

	function initProModal() {
		var modal = document.getElementById( 'ogf-modal' );
		if ( ! modal ) {
			return;
		}

		// Generic pro-feature triggers.
		document.querySelectorAll( '[data-pro-feature]' ).forEach( function( el ) {
			el.addEventListener( 'click', function() {
				openProModal(
					el.getAttribute( 'data-feature' ) || '',
					el.getAttribute( 'data-desc' ) || ''
				);
			} );
		} );

		// Pro toggle groups.
		document.querySelectorAll( '.ogf-font-toggle-group[data-pro-toggle]' ).forEach( function( group ) {
			var toggle = group.querySelector( '.ogf-toggle-sm' );
			if ( toggle ) {
				toggle.addEventListener( 'click', function( e ) {
					e.stopPropagation();
					openProModal(
						group.getAttribute( 'data-feature' ) || '',
						group.getAttribute( 'data-desc' ) || ''
					);
				} );
			}
		} );

		// Font loading disabled toggles.
		document.querySelectorAll( '.ogf-fl-grid-row .ogf-toggle-xs.disabled' ).forEach( function( toggle ) {
			toggle.addEventListener( 'click', function() {
				openProModal(
					toggle.getAttribute( 'data-feature' ) || 'Font Loading',
					toggle.getAttribute( 'data-desc' ) || ''
				);
			} );
		} );

		// Recommendation "Fix" buttons.
		document.querySelectorAll( '[data-pro-trigger]' ).forEach( function( btn ) {
			btn.addEventListener( 'click', function() {
				openProModal(
					btn.getAttribute( 'data-feature' ) || '',
					btn.getAttribute( 'data-desc' ) || ''
				);
			} );
		} );

		// Disabled settings toggles.
		document.querySelectorAll( '.ogf-setting-row .ogf-toggle.disabled' ).forEach( function( toggle ) {
			toggle.addEventListener( 'click', function() {
				openProModal(
					toggle.getAttribute( 'data-feature' ) || 'Pro Feature',
					toggle.getAttribute( 'data-desc' ) || ''
				);
			} );
		} );

		// Close handlers.
		modal.addEventListener( 'click', function( e ) {
			if ( e.target === modal ) {
				closeProModal();
			}
		} );

		modal.querySelectorAll( '.ogf-modal-close' ).forEach( function( btn ) {
			btn.addEventListener( 'click', closeProModal );
		} );

		document.addEventListener( 'keydown', function( e ) {
			if ( e.key === 'Escape' ) {
				closeProModal();
			}
		} );
	}

	/* ---------------------------------------------------------------
	 * Font row expand / collapse
	 * ------------------------------------------------------------- */
	function initFontRows() {
		document.querySelectorAll( '.ogf-font-row-header' ).forEach( function( header ) {
			header.addEventListener( 'click', function( e ) {
				// Don't toggle when clicking a toggle switch inside the row.
				if ( e.target.closest( '.ogf-toggle, .ogf-toggle-sm, .ogf-toggle-xs' ) ) {
					return;
				}
				header.closest( '.ogf-font-row' ).classList.toggle( 'open' );
			} );
		} );
	}

	/* ---------------------------------------------------------------
	 * Per-font HOST / PRELOAD toggles
	 * ------------------------------------------------------------- */
	function initFontToggles() {
		document.querySelectorAll( '.ogf-font-toggle-group:not([data-pro-toggle])' ).forEach( function( group ) {
			var toggle = group.querySelector( '.ogf-toggle-sm' );
			if ( ! toggle ) {
				return;
			}

			toggle.addEventListener( 'click', function( e ) {
				e.stopPropagation();

				var fontId  = toggle.getAttribute( 'data-font' ) || '';
				var setting = toggle.getAttribute( 'data-setting' ) || '';

				// Allow for custom fonts (already hosted) or Pro users.
				var isCustom = fontId && fontId.indexOf( 'cf-' ) === 0;
				if ( ! ogfAdmin.isPro && ! isCustom ) {
					return;
				}

				toggle.classList.toggle( 'on' );

				ajaxPost( 'ogf_save_font_settings', {
					font_id: fontId,
					setting: setting,
					value: toggle.classList.contains( 'on' ) ? 1 : 0,
				} ).then( function() {
					showToast( ogfAdmin.i18n.saved );
				} ).catch( function() {
					showToast( ogfAdmin.i18n.error );
				} );
			} );
		} );
	}

	/* ---------------------------------------------------------------
	 * Diagnostics: clear cache and reset fonts
	 * ------------------------------------------------------------- */
	function initDiagnostics() {
		var clearBtn = document.getElementById( 'ogf-clear-cache' );
		var resetBtn = document.getElementById( 'ogf-reset-fonts' );

		if ( clearBtn ) {
			clearBtn.addEventListener( 'click', function() {
				if ( ! window.confirm( ogfAdmin.i18n.clearCacheConfirm ) ) {
					return;
				}
				ajaxPost( 'ogf_clear_font_cache', {} )
					.then( function() {
						showToast( ogfAdmin.i18n.cleared );
					} )
					.catch( function() {
						showToast( ogfAdmin.i18n.error );
					} );
			} );
		}

		if ( resetBtn ) {
			resetBtn.addEventListener( 'click', function() {
				if ( ! window.confirm( ogfAdmin.i18n.resetFontsConfirm ) ) {
					return;
				}
				ajaxPost( 'ogf_reset_all_fonts', {} )
					.then( function() {
						showToast( ogfAdmin.i18n.resetDone );
					} )
					.catch( function() {
						showToast( ogfAdmin.i18n.error );
					} );
			} );
		}
	}

	/* ---------------------------------------------------------------
	 * Font display select
	 * ------------------------------------------------------------- */
	function initSelects() {
		document.querySelectorAll( '.ogf-setting-select[data-setting]' ).forEach( function( select ) {
			select.addEventListener( 'change', function() {
				ajaxPost( 'ogf_save_settings', {
					setting: select.getAttribute( 'data-setting' ),
					value: select.value,
				} ).then( function() {
					showToast( ogfAdmin.i18n.saved );
				} ).catch( function() {
					showToast( ogfAdmin.i18n.error );
				} );
			} );
		} );
	}

	/* ---------------------------------------------------------------
	 * Boot
	 * ------------------------------------------------------------- */
	document.addEventListener( 'DOMContentLoaded', function() {
		initTabs();
		initToggles();
		initProModal();
		initFontRows();
		initFontToggles();
		initDiagnostics();
		initSelects();
	} );
} )();
