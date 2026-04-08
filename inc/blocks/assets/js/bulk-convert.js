/* global rprBulkConvert */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		const btn      = document.getElementById( 'rpr-bulk-convert-btn' );
		const progress = document.getElementById( 'rpr-bulk-convert-progress' );
		const bar      = document.getElementById( 'rpr-bulk-convert-bar' );
		const status   = document.getElementById( 'rpr-bulk-convert-status' );
		const errBox   = document.getElementById( 'rpr-bulk-convert-error' );
		const errMsg   = document.getElementById( 'rpr-bulk-convert-error-msg' );
		const doneBox  = document.getElementById( 'rpr-bulk-convert-done' );

		if ( ! btn ) {
			return;
		}

		const total = parseInt( btn.dataset.total, 10 ) || 0;
		let converted = 0;

		btn.addEventListener( 'click', function () {
			btn.disabled = true;
			progress.style.display = 'block';
			errBox.style.display   = 'none';
			doneBox.style.display  = 'none';
			converted = 0;
			bar.value = 0;
			runBatch();
		} );

		function runBatch() {
			const fd = new FormData();
			fd.append( 'action', rprBulkConvert.action );
			fd.append( 'nonce',  rprBulkConvert.nonce );
			// Always offset 0: converted recipes drop out of the unconverted query,
			// so the next unconverted batch is always at the top of results.
			fd.append( 'offset', 0 );

			fetch( rprBulkConvert.ajaxUrl, { method: 'POST', body: fd } )
				.then( function ( res ) {
					if ( ! res.ok ) {
						throw new Error( 'HTTP ' + res.status );
					}
					return res.json();
				} )
				.then( function ( json ) {
					if ( ! json.success ) {
						showError( json.data && json.data.message ? json.data.message : 'Conversion failed.' );
						return;
					}

					converted += json.data.converted;
					bar.value = converted;
					status.textContent = converted + ' / ' + total;

					if ( json.data.done ) {
						showDone();
					} else {
						runBatch();
					}
				} )
				.catch( function ( err ) {
					showError( err.message || 'An unexpected error occurred.' );
				} );
		}

		function showError( msg ) {
			errMsg.textContent    = msg;
			errBox.style.display  = 'block';
			btn.disabled          = false;
		}

		function showDone() {
			doneBox.style.display = 'block';
			btn.disabled          = true;
			btn.textContent       = 'All Done';
		}
	} );
} )();
