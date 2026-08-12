/**
 * Plinth — Admin Shell JS.
 *
 * Handles tab switching, toggle/select auto-save, and toast notifications.
 * Uses window.plinthAdmin for AJAX URL, nonce, slug, and i18n strings.
 *
 * The AJAX action name is {slug}_save_setting. The nonce name is
 * {slug}_plinth_nonce. These are separate from any domain-specific nonces
 * the consumer plugin uses for its own AJAX handlers.
 *
 * Exposes window.Plinth.showToast() and window.Plinth.ajaxPost() so the
 * consumer can reuse them for its own notifications and AJAX calls.
 */

( function () {
	'use strict';

	var config = window.plinthAdmin || {};
	var toastTimer = null;

	function ajaxPost( action, data ) {
		var formData = new FormData();
		formData.append( 'action', action );
		formData.append( '_ajax_nonce', config.nonce );

		Object.keys( data ).forEach( function ( key ) {
			formData.append( key, data[ key ] );
		} );

		return fetch( config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: formData,
		} ).then( function ( response ) {
			return response.json();
		} );
	}

	function showToast( message ) {
		var toast = document.getElementById( 'pln-toast' );
		var msgEl = document.getElementById( 'pln-toast__msg' );
		if ( ! toast ) {
			return;
		}

		if ( toastTimer ) {
			clearTimeout( toastTimer );
		}

		( msgEl || toast ).textContent = message;
		toast.classList.add( 'visible' );

		toastTimer = setTimeout( function () {
			toast.classList.remove( 'visible' );
			toastTimer = null;
		}, 3000 );
	}

	function initTabs() {
		var buttons = document.querySelectorAll( '.pln-nav__item' );
		var panels = document.querySelectorAll( '.pln-tab-panel' );

		function activateTab( name ) {
			buttons.forEach( function ( btn ) {
				btn.classList.toggle( 'active', btn.getAttribute( 'data-tab' ) === name );
			} );
			panels.forEach( function ( panel ) {
				panel.classList.toggle( 'active', panel.id === 'pln-tab-' + name );
			} );
		}

		buttons.forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var tab = btn.getAttribute( 'data-tab' );
				activateTab( tab );
				window.location.hash = '#' + tab;
			} );
		} );

		document.querySelectorAll( '[data-goto-tab]' ).forEach( function ( el ) {
			el.addEventListener( 'click', function () {
				var target = el.getAttribute( 'data-goto-tab' );
				activateTab( target );
				window.location.hash = '#' + target;
				window.scrollTo( 0, 0 );
			} );
		} );

		window.addEventListener( 'hashchange', function () {
			var hash = window.location.hash.replace( '#', '' );
			if ( hash && document.getElementById( 'pln-tab-' + hash ) ) {
				activateTab( hash );
				window.scrollTo( 0, 0 );
			}
		} );

		var wrap = document.querySelector( '.pln-wrap' );
		var defaultTab = wrap ? wrap.getAttribute( 'data-default-tab' ) : '';
		var hash = window.location.hash.replace( '#', '' );
		var tab = hash || defaultTab;

		if ( ! tab || ! document.getElementById( 'pln-tab-' + tab ) ) {
			tab = defaultTab;
		}

		if ( tab ) {
			activateTab( tab );
		}
	}

	function initToggles() {
		var saveAction = config.slug + '_save_setting';

		document.querySelectorAll( '.pln-setting .pln-toggle' ).forEach( function ( toggle ) {
			toggle.addEventListener( 'click', function () {
				if ( toggle.classList.contains( 'disabled' ) || toggle.disabled ) {
					return;
				}

				toggle.classList.toggle( 'on' );
				var isOn = toggle.classList.contains( 'on' );
				toggle.setAttribute( 'aria-checked', isOn ? 'true' : 'false' );

				var setting = toggle.getAttribute( 'data-setting' );

				ajaxPost( saveAction, { setting: setting, value: isOn ? 1 : 0 } )
					.then( function ( response ) {
						if ( ! response.success ) {
							toggle.classList.toggle( 'on' );
							toggle.setAttribute( 'aria-checked', ! isOn ? 'true' : 'false' );
							showToast( config.i18n.error );
							return;
						}
						showToast( config.i18n.saved );
					} )
					.catch( function () {
						toggle.classList.toggle( 'on' );
						toggle.setAttribute( 'aria-checked', ! isOn ? 'true' : 'false' );
						showToast( config.i18n.error );
					} );
			} );
		} );
	}

	function initSelects() {
		var saveAction = config.slug + '_save_setting';

		document.querySelectorAll( '.pln-setting__select[data-setting]' ).forEach( function ( select ) {
			select.addEventListener( 'change', function () {
				ajaxPost( saveAction, {
					setting: select.getAttribute( 'data-setting' ),
					value: select.value,
				} )
					.then( function ( response ) {
						if ( ! response.success ) {
							showToast( config.i18n.error );
							return;
						}
						showToast( config.i18n.saved );
					} )
					.catch( function () {
						showToast( config.i18n.error );
					} );
			} );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		initTabs();
		initToggles();
		initSelects();
	} );

	window.Plinth = {
		ajaxPost: ajaxPost,
		showToast: showToast,
	};
} )();
