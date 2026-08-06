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
		var msgEl = document.getElementById( 'ogf-toast-msg' );
		if ( ! toast ) {
			return;
		}

		if ( toastTimer ) {
			clearTimeout( toastTimer );
		}

		// Write into the message span so the leading icon survives.
		( msgEl || toast ).textContent = message;
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
		var buttons = document.querySelectorAll( '.ogf-nav-item' );
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

		// Controls elsewhere on the page that jump to a tab.
		document.querySelectorAll( '[data-goto-tab]' ).forEach( function( el ) {
			el.addEventListener( 'click', function() {
				var target = el.getAttribute( 'data-goto-tab' );
				activateTab( target );
				window.location.hash = '#' + target;
				window.scrollTo( 0, 0 );
			} );
		} );

		window.addEventListener( 'hashchange', function() {
			var hash = window.location.hash.replace( '#', '' );
			if ( hash && document.getElementById( 'ogf-tab-' + hash ) ) {
				activateTab( hash );
				window.scrollTo( 0, 0 );
			}
		} );

		// Activate tab from hash or fall back to the server default.
		var wrap = document.querySelector( '.ogf-wrap' );
		var defaultTab = wrap ? wrap.getAttribute( 'data-default-tab' ) : 'dashboard';
		var hash = window.location.hash.replace( '#', '' );
		var tab = hash || defaultTab || 'dashboard';

		if ( ! document.getElementById( 'ogf-tab-' + tab ) ) {
			tab = defaultTab || 'dashboard';
		}

		activateTab( tab );
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
					.then( function( response ) {
						if ( ! response.success ) {
							throw new Error( 'Could not save setting' );
						}
						syncFontToggles( setting, value );
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

		// Disabled Host / Preload toggles on font rows.
		document.querySelectorAll( '.ogf-font-toggle-group[data-pro-toggle]' ).forEach( function( group ) {
			var toggle = group.querySelector( '.ogf-toggle-sm' );
			if ( toggle ) {
				toggle.addEventListener( 'click', function( e ) {
					e.stopPropagation();
					openProModal(
						group.getAttribute( 'data-feature' ) || 'Pro Feature',
						group.getAttribute( 'data-desc' ) || ''
					);
				} );
			}
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
	 * Global Host / Preload toggles shown on active font rows
	 * ------------------------------------------------------------- */
	function syncFontToggles( setting, value ) {
		document.querySelectorAll( '.ogf-font-toggles .ogf-toggle-sm[data-setting]' ).forEach( function( toggle ) {
			if ( toggle.getAttribute( 'data-setting' ) === setting ) {
				toggle.classList.toggle( 'on', 1 === Number( value ) );
			}
		} );
	}

	function initFontToggles() {
		document.querySelectorAll( '.ogf-font-toggles .ogf-toggle-sm[data-setting]' ).forEach( function( toggle ) {
			toggle.addEventListener( 'click', function( e ) {
				e.stopPropagation();

				if ( toggle.classList.contains( 'disabled' ) ) {
					return;
				}

				var setting = toggle.getAttribute( 'data-setting' ) || '';
				var value   = toggle.classList.contains( 'on' ) ? 0 : 1;

				toggle.classList.toggle( 'on', 1 === value );

				ajaxPost( 'ogf_save_settings', { setting: setting, value: value } )
					.then( function( response ) {
						if ( ! response.success ) {
							throw new Error( 'Could not save setting' );
						}
						syncFontToggles( setting, value );
						showToast( ogfAdmin.i18n.saved );
					} )
					.catch( function() {
						toggle.classList.toggle( 'on', 1 !== value );
						showToast( ogfAdmin.i18n.error );
					} );
			} );
		} );
	}

	/* ---------------------------------------------------------------
	 * Selective font loading
	 * ------------------------------------------------------------- */
	function initFontLoading() {
		document.querySelectorAll( '.ogf-fl-grid-row .ogf-toggle-xs:not(.disabled)' ).forEach( function( toggle ) {
			toggle.addEventListener( 'click', function() {
				var card      = toggle.closest( '.ogf-fl-card' );
				var kind      = toggle.getAttribute( 'data-kind' ) || '';
				var fontId    = toggle.getAttribute( 'data-font' ) || '';
				var wasOn     = toggle.classList.contains( 'on' );
				var isEnabled = ! wasOn;

				toggle.classList.toggle( 'on', isEnabled );

				var payload = {
					font_id: fontId,
					kind: kind,
				};

				if ( 'weight' === kind ) {
					var selected = [];
					card.querySelectorAll( '[data-kind="weight"]' ).forEach( function( weightToggle ) {
						if ( weightToggle.classList.contains( 'on' ) ) {
							selected.push( weightToggle.getAttribute( 'data-weight' ) );
						}
					} );

					// The existing loader cannot represent zero selected weights.
					if ( ! selected.length ) {
						toggle.classList.add( 'on' );
						return;
					}

					payload.values = JSON.stringify( selected );
				} else if ( 'subset' === kind ) {
					payload.subset  = toggle.getAttribute( 'data-subset' ) || '';
					payload.enabled = isEnabled ? 1 : 0;
				} else {
					toggle.classList.toggle( 'on', wasOn );
					return;
				}

				ajaxPost( 'ogf_save_font_loading', payload )
					.then( function( response ) {
						if ( ! response.success ) {
							toggle.classList.toggle( 'on', wasOn );
							showToast( ( response.data && response.data.message ) || ogfAdmin.i18n.error );
							return;
						}

						if ( 'subset' === kind ) {
							document.querySelectorAll( '[data-kind="subset"]' ).forEach( function( subsetToggle ) {
								if ( subsetToggle.getAttribute( 'data-subset' ) === payload.subset ) {
									subsetToggle.classList.toggle( 'on', isEnabled );
								}
							} );
						}

						showToast( ogfAdmin.i18n.saved );
					} )
					.catch( function() {
						toggle.classList.toggle( 'on', wasOn );
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
	 * Copy diagnostics for support
	 * ------------------------------------------------------------- */

	/**
	 * Build a plain-text report from the rendered Diagnostics panel.
	 *
	 * Read off the DOM rather than duplicated in PHP, so a row added to the
	 * panel shows up in the report without anyone remembering to add it twice.
	 *
	 * @return {string} Report ready to paste into a ticket.
	 */
	function buildDiagnosticsReport() {
		var panel = document.getElementById( 'ogf-tab-diagnostics' );
		var lines = [];

		if ( ! panel ) {
			return '';
		}

		panel.querySelectorAll( '.ogf-card' ).forEach( function( card ) {
			var rows = card.querySelectorAll( '.ogf-diag-item' );
			if ( ! rows.length ) {
				return;
			}

			var heading = card.querySelector( '.ogf-card-header' );
			lines.push( '## ' + ( heading ? heading.textContent.trim() : '' ) );

			rows.forEach( function( row ) {
				var key = row.querySelector( '.ogf-diag-key' );
				var val = row.querySelector( '.ogf-diag-val, .ogf-diag-status' );
				if ( key && val ) {
					lines.push( key.textContent.trim() + ': ' + val.textContent.trim() );
				}
			} );

			lines.push( '' );
		} );

		if ( ! lines.length ) {
			return '';
		}

		return 'Fonts Plugin diagnostics\n' +
			'Generated: ' + new Date().toISOString().replace( 'T', ' ' ).slice( 0, 19 ) + ' UTC\n\n' +
			lines.join( '\n' ).trim() + '\n';
	}

	/**
	 * Copy via a temporary selection. Works without a secure context and
	 * without the document holding focus.
	 *
	 * @param {string} text Text to place on the clipboard.
	 * @return {Promise} Resolves when the copy succeeds.
	 */
	function legacyCopy( text ) {
		return new Promise( function( resolve, reject ) {
			var field = document.createElement( 'textarea' );
			field.value = text;
			field.setAttribute( 'readonly', '' );
			field.style.position = 'fixed';
			field.style.top = '-1000px';
			document.body.appendChild( field );
			field.select();

			try {
				if ( document.execCommand( 'copy' ) ) {
					resolve();
				} else {
					reject();
				}
			} catch ( e ) {
				reject();
			} finally {
				document.body.removeChild( field );
			}
		} );
	}

	/**
	 * Copy text to the clipboard.
	 *
	 * The async API is tried first, but it rejects in more situations than
	 * "unsupported" — a document that lost focus, a restrictive permissions
	 * policy, an embedded frame. Fall through to the legacy path on rejection
	 * rather than only when the API is missing, since it still succeeds in
	 * several of those cases.
	 *
	 * @param {string} text Text to place on the clipboard.
	 * @return {Promise} Resolves when the copy succeeds.
	 */
	function copyText( text ) {
		if ( navigator.clipboard && window.isSecureContext ) {
			return navigator.clipboard.writeText( text ).catch( function() {
				return legacyCopy( text );
			} );
		}

		return legacyCopy( text );
	}

	function initCopyDiagnostics() {
		var button = document.getElementById( 'ogf-copy-diagnostics' );
		if ( ! button ) {
			return;
		}

		button.addEventListener( 'click', function() {
			var report = buildDiagnosticsReport();

			if ( ! report ) {
				showToast( ogfAdmin.i18n.error );
				return;
			}

			copyText( report )
				.then( function() {
					showToast( ogfAdmin.i18n.diagnosticsCopied );
				} )
				.catch( function() {
					showToast( ogfAdmin.i18n.copyFailed );
				} );
		} );
	}

	/* ---------------------------------------------------------------
	 * Shared helpers for the Upload / Adobe Fonts tabs
	 * ------------------------------------------------------------- */

	/**
	 * Reload the page, landing back on the given tab.
	 *
	 * @param {string} tab Tab slug.
	 */
	function reloadToTab( tab ) {
		window.location.hash = '#' + tab;
		window.location.reload();
	}

	/**
	 * Put a button into its loading state. Returns a restore function.
	 *
	 * @param {Element} button Button element.
	 * @param {string}  label  Text to show while busy.
	 * @return {Function} Call to restore the original label.
	 */
	function setBusy( button, label ) {
		var original = button.textContent;
		button.disabled = true;
		button.textContent = label;

		return function() {
			button.disabled = false;
			button.textContent = original;
		};
	}

	/**
	 * Read every [data-field] control inside a form wrapper.
	 *
	 * @param {Element} form The .ogf-cf-form wrapper.
	 * @return {Object} Payload for the AJAX request.
	 */
	function collectFontForm( form ) {
		var payload = { term_id: form.getAttribute( 'data-term-id' ) || 0 };

		form.querySelectorAll( '[data-field]' ).forEach( function( el ) {
			var field = el.getAttribute( 'data-field' );

			if ( 'BUTTON' === el.tagName ) {
				payload[ field ] = el.classList.contains( 'on' ) ? 1 : 0;
			} else {
				payload[ field ] = el.value;
			}
		} );

		return payload;
	}

	/* ---------------------------------------------------------------
	 * Upload Fonts tab
	 * ------------------------------------------------------------- */
	var mediaFrame = null;

	/**
	 * Open the media library and write the chosen URL into an input.
	 *
	 * @param {Element} input The text input to fill.
	 */
	function openFontPicker( input ) {
		if ( ! window.wp || ! window.wp.media ) {
			return;
		}

		if ( ! mediaFrame ) {
			mediaFrame = window.wp.media( {
				title: ogfAdmin.i18n.selectFontFile,
				button: { text: ogfAdmin.i18n.useFontFile },
				multiple: false,
			} );
		}

		// Rebind on every open so the URL lands in the input that was clicked.
		mediaFrame.off( 'select' );
		mediaFrame.on( 'select', function() {
			var attachment = mediaFrame.state().get( 'selection' ).first().toJSON();
			input.value = attachment.url;
		} );

		mediaFrame.open();
	}

	function initCustomFonts() {
		var panel = document.getElementById( 'ogf-tab-upload' );
		if ( ! panel ) {
			return;
		}

		// Preload toggles are read on save rather than saved individually.
		panel.querySelectorAll( '.ogf-cf-form .ogf-toggle-sm[data-field]' ).forEach( function( toggle ) {
			toggle.addEventListener( 'click', function() {
				toggle.classList.toggle( 'on' );
			} );
		} );

		panel.querySelectorAll( '.ogf-cf-upload' ).forEach( function( button ) {
			button.addEventListener( 'click', function() {
				openFontPicker( button.parentElement.querySelector( 'input[data-field]' ) );
			} );
		} );

		panel.querySelectorAll( '.ogf-cf-edit' ).forEach( function( button ) {
			button.addEventListener( 'click', function() {
				var row = button.closest( '.ogf-uploaded-font-row' );
				var editPanel = row.nextElementSibling;

				if ( editPanel && editPanel.classList.contains( 'ogf-cf-edit-panel' ) ) {
					editPanel.classList.toggle( 'open' );
				}
			} );
		} );

		panel.querySelectorAll( '.ogf-cf-cancel' ).forEach( function( button ) {
			button.addEventListener( 'click', function() {
				button.closest( '.ogf-cf-edit-panel' ).classList.remove( 'open' );
			} );
		} );

		panel.querySelectorAll( '.ogf-cf-save' ).forEach( function( button ) {
			button.addEventListener( 'click', function() {
				var form = button.closest( '.ogf-cf-form' );
				var restore = setBusy( button, ogfAdmin.i18n.saving );

				ajaxPost( 'ogf_save_custom_font', collectFontForm( form ) )
					.then( function( response ) {
						if ( ! response.success ) {
							restore();
							showToast( ( response.data && response.data.message ) || ogfAdmin.i18n.error );
							return;
						}

						showToast( ogfAdmin.i18n.fontSaved );
						reloadToTab( 'upload' );
					} )
					.catch( function() {
						restore();
						showToast( ogfAdmin.i18n.error );
					} );
			} );
		} );

		panel.querySelectorAll( '.ogf-cf-delete' ).forEach( function( button ) {
			button.addEventListener( 'click', function() {
				if ( ! window.confirm( ogfAdmin.i18n.deleteFontConfirm ) ) {
					return;
				}

				var row = button.closest( '.ogf-uploaded-font-row' );
				var restore = setBusy( button, ogfAdmin.i18n.saving );

				ajaxPost( 'ogf_delete_custom_font', { term_id: row.getAttribute( 'data-term-id' ) } )
					.then( function( response ) {
						if ( ! response.success ) {
							restore();
							showToast( ( response.data && response.data.message ) || ogfAdmin.i18n.error );
							return;
						}

						showToast( ogfAdmin.i18n.fontDeleted );
						reloadToTab( 'upload' );
					} )
					.catch( function() {
						restore();
						showToast( ogfAdmin.i18n.error );
					} );
			} );
		} );
	}

	/* ---------------------------------------------------------------
	 * Adobe Fonts tab
	 * ------------------------------------------------------------- */
	function initTypekit() {
		var keyInput = document.getElementById( 'ogf-typekit-key' );
		var saveBtn = document.getElementById( 'ogf-typekit-save' );
		var refreshBtn = document.getElementById( 'ogf-typekit-refresh' );

		/**
		 * Run a token request and reload so the kit list reflects the result.
		 *
		 * @param {Element} button  Button that triggered the request.
		 * @param {string}  label   Busy label.
		 * @param {string}  action  WP AJAX action.
		 * @param {Object}  payload Request body.
		 */
		function run( button, label, action, payload ) {
			var restore = setBusy( button, label );

			ajaxPost( action, payload )
				.then( function( response ) {
					if ( ! response.success ) {
						restore();
						showToast( ( response.data && response.data.message ) || ogfAdmin.i18n.error );
						return;
					}

					showToast( ( response.data && response.data.message ) || ogfAdmin.i18n.saved );
					reloadToTab( 'adobe' );
				} )
				.catch( function() {
					restore();
					showToast( ogfAdmin.i18n.error );
				} );
		}

		if ( saveBtn && keyInput ) {
			saveBtn.addEventListener( 'click', function() {
				run( saveBtn, ogfAdmin.i18n.saving, 'ogf_save_typekit_key', { api_key: keyInput.value } );
			} );
		}

		if ( refreshBtn ) {
			refreshBtn.addEventListener( 'click', function() {
				run( refreshBtn, ogfAdmin.i18n.refreshing, 'ogf_refresh_typekit', {} );
			} );
		}

		document.querySelectorAll( '.ogf-tk-toggle' ).forEach( function( button ) {
			button.addEventListener( 'click', function() {
				var enabled = '1' !== button.getAttribute( 'data-enabled' );
				var restore = setBusy( button, ogfAdmin.i18n.saving );

				ajaxPost( 'ogf_toggle_typekit_kit', {
					kit_id: button.getAttribute( 'data-kit' ),
					enabled: enabled ? 1 : 0,
				} )
					.then( function( response ) {
						restore();

						if ( ! response.success ) {
							showToast( ( response.data && response.data.message ) || ogfAdmin.i18n.error );
							return;
						}

						var status = button.parentElement.querySelector( '.ogf-kit-status' );

						button.setAttribute( 'data-enabled', enabled ? '1' : '0' );
						button.textContent = enabled ? ogfAdmin.i18n.disableKit : ogfAdmin.i18n.enableKit;

						if ( status ) {
							status.classList.toggle( 'active', enabled );
							status.classList.toggle( 'inactive', ! enabled );
							status.textContent = enabled ? ogfAdmin.i18n.kitActive : ogfAdmin.i18n.kitInactive;
						}

						showToast( ogfAdmin.i18n.kitUpdated );
					} )
					.catch( function() {
						restore();
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
		initFontLoading();
		initDiagnostics();
		initSelects();
		initCustomFonts();
		initTypekit();
		initCopyDiagnostics();
	} );
} )();
