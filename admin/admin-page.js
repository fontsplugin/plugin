/**
 * Fonts Plugin (olympus-google-fonts) Admin Page JS.
 *
 * Domain-specific modules. Generic tab switching, toggle/select auto-save,
 * and toast notifications are handled by Plinth (window.Plinth).
 *
 * Relies on localized data from WordPress via window.ogfAdmin.
 *
 * @package suspended
 */

( function() {
	'use strict';

	var ogfConfig = window.ogfAdmin || {};
	var showToast = window.Plinth.showToast;

	// Local AJAX wrapper that uses the OGF nonce (ogf_admin_nonce), not the
	// Plinth nonce (fonts-plugin_plinth_nonce). Font-row toggles and other
	// domain-specific handlers verify the OGF nonce on the server side.
	// Settings tab toggles use Plinth's own ajaxPost with its own nonce.
	function ajaxPost( action, data ) {
		var formData = new FormData();
		formData.append( 'action', action );
		formData.append( '_ajax_nonce', ogfConfig.nonce );

		Object.keys( data ).forEach( function( key ) {
			formData.append( key, data[ key ] );
		} );

		return fetch( ogfConfig.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: formData,
		} ).then( function( response ) {
			return response.json();
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

		if ( ogfAdmin.needsLicense && ogfAdmin.i18n ) {
			if ( titleEl ) {
				titleEl.textContent = ogfAdmin.i18n.inactiveLicenseTitle || 'Inactive License';
			}
			if ( descEl ) {
				descEl.textContent = ogfAdmin.i18n.inactiveLicenseDesc || '';
			}
		} else {
			if ( titleEl ) {
				titleEl.textContent = featureTitle || '';
			}
			if ( descEl ) {
				descEl.textContent = featureDesc || '';
			}
		}

		var slug = ( featureTitle || 'pro' )
			.toLowerCase()
			.replace( /[^a-z0-9]+/g, '-' )
			.replace( /^-|-$/g, '' );

		var link = document.getElementById( 'ogf-modal-link' );
		var benefits = modal.querySelector( '.ogf-modal-benefits' );

		if ( link ) {
			if ( ogfAdmin.needsLicense && ogfAdmin.licenseUrl ) {
				link.href = ogfAdmin.licenseUrl;
				link.removeAttribute( 'target' );
				link.removeAttribute( 'rel' );
				link.textContent = ( ogfAdmin.i18n && ogfAdmin.i18n.addLicense ) || 'Add License';
				if ( benefits ) {
					benefits.style.display = 'none';
				}
			} else if ( ogfAdmin.upgradeUrl ) {
				link.href = ogfAdmin.upgradeUrl + '&utm_campaign=' + encodeURIComponent( slug );
				link.setAttribute( 'target', '_blank' );
				if ( benefits ) {
					benefits.style.display = '';
				}
			}
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

		document.querySelectorAll( '[data-pro-feature]' ).forEach( function( el ) {
			el.addEventListener( 'click', function() {
				openProModal(
					el.getAttribute( 'data-feature' ) || '',
					el.getAttribute( 'data-desc' ) || ''
				);
			} );
		} );

		document.querySelectorAll( '.ogf-fl-grid-row .ogf-toggle-xs.disabled' ).forEach( function( toggle ) {
			toggle.addEventListener( 'click', function() {
				openProModal(
					toggle.getAttribute( 'data-feature' ) || 'Font Loading',
					toggle.getAttribute( 'data-desc' ) || ''
				);
			} );
		} );

		document.querySelectorAll( '[data-pro-trigger]' ).forEach( function( btn ) {
			btn.addEventListener( 'click', function() {
				openProModal(
					btn.getAttribute( 'data-feature' ) || '',
					btn.getAttribute( 'data-desc' ) || ''
				);
			} );
		} );

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
				if ( e.target.closest( '.ogf-toggle-sm, .ogf-toggle-xs' ) ) {
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
	 * Copy diagnostics for support
	 * ------------------------------------------------------------- */
	function buildDiagnosticsReport() {
		var panel = document.getElementById( 'pln-tab-diagnostics' );
		var lines = [];

		if ( ! panel ) {
			return '';
		}

		panel.querySelectorAll( '.pln-card' ).forEach( function( card ) {
			var rows = card.querySelectorAll( '.pln-diag__item' );
			if ( ! rows.length ) {
				return;
			}

			var heading = card.querySelector( '.pln-card__header' );
			lines.push( '## ' + ( heading ? heading.textContent.trim() : '' ) );

			rows.forEach( function( row ) {
				var key = row.querySelector( '.pln-diag__key' );
				var val = row.querySelector( '.pln-diag__val, .pln-diag__status' );
				if ( key && val ) {
					lines.push( key.textContent.trim() + ': ' + val.textContent.trim() );
				}
			} );

			card.querySelectorAll( '.ogf-diag-plugins-report li' ).forEach( function( pluginLine ) {
				lines.push( '- ' + pluginLine.textContent.trim() );
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
	function reloadToTab( tab ) {
		window.location.hash = '#' + tab;
		window.location.reload();
	}

	function setBusy( button, label ) {
		var original = button.textContent;
		button.disabled = true;
		button.textContent = label;

		return function() {
			button.disabled = false;
			button.textContent = original;
		};
	}

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

		mediaFrame.off( 'select' );
		mediaFrame.on( 'select', function() {
			var attachment = mediaFrame.state().get( 'selection' ).first().toJSON();
			input.value = attachment.url;
		} );

		mediaFrame.open();
	}

	function initCustomFonts() {
		var panel = document.getElementById( 'pln-tab-upload' );
		if ( ! panel ) {
			return;
		}

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
		initProModal();
		initFontRows();
		initFontToggles();
		initFontLoading();
		initDiagnostics();
		initCustomFonts();
		initTypekit();
		initCopyDiagnostics();
	} );
} )();
