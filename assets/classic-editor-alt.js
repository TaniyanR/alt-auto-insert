( function () {
	'use strict';

	var originalSendToEditor;

	function getPostTitle() {
		var titleField = document.getElementById( 'title' );
		return titleField && 'string' === typeof titleField.value ? titleField.value.trim() : '';
	}

	function fillEmptyImageAlt( html, title ) {
		if ( ! html || ! title || -1 === html.toLowerCase().indexOf( '<img' ) ) {
			return html;
		}

		var holder = document.createElement( 'div' );
		holder.innerHTML = html;

		Array.prototype.forEach.call( holder.querySelectorAll( 'img' ), function ( image ) {
			var currentAlt = image.getAttribute( 'alt' );
			if ( null === currentAlt || '' === currentAlt.trim() ) {
				image.setAttribute( 'alt', title );
			}
		} );

		return holder.innerHTML;
	}

	function installWrapper() {
		if ( 'function' !== typeof window.send_to_editor || window.send_to_editor.__altAutoInsertWrapped ) {
			return false;
		}

		originalSendToEditor = window.send_to_editor;
		window.send_to_editor = function ( html ) {
			var title = getPostTitle();
			var args = Array.prototype.slice.call( arguments );
			args[ 0 ] = fillEmptyImageAlt( html, title );
			return originalSendToEditor.apply( this, args );
		};
		window.send_to_editor.__altAutoInsertWrapped = true;
		return true;
	}

	if ( ! installWrapper() ) {
		var attempts = 0;
		var timer = window.setInterval( function () {
			attempts += 1;
			if ( installWrapper() || attempts >= 50 ) {
				window.clearInterval( timer );
			}
		}, 100 );
	}
}() );
