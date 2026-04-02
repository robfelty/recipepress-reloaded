function socialShare(e) {
	jQuery.ajax({
		type: 'post',
		url: rpr_public_vars.ajax_url,
		data: {
			action: 'save_share_count',
			social_site: e.parentNode.getAttribute('data-social'),
			share_nonce: rpr_public_vars.share_nonce,
			recipe_id: rpr_public_vars.recipe_id,
		}
	});
}

(function( $ ) {
	'use strict';

	$(function() {

		/*$('.rpr-ingredients-container .rpr-ingredient').each(function () {
			// convert_units( $(this) ); // $(this) == each ingredient line.
		});*/

		function convert_units($ing) {

			// Find the `.rpr-ingredient-quantity` span then read its `data-ingredient-quantity` attribute.
			var qnty = $ing.find('.rpr-ingredient-quantity').data('ingredient-quantity');
			// Find the `.rpr-ingredient-unit` span then read its `data-ingredient-unit` attribute.
			var unit = $ing.find('.rpr-ingredient-unit').data('ingredient-unit');

			if ( typeof unit !== 'undefined' ) {

				console.log(unit);

				if ( unit === 'cup' || unit === 'cups' ) {
					$ing.find( '.rpr-ingredient-quantity' ).text( Math.ceil( qnty * 2 ) );
					$ing.find( '.rpr-ingredient-unit' ).text( 'mg' );
				}

				if ( unit === 'tsp' || unit === 'teaspoon' || unit === 'teaspoons' ) {
					$ing.find( '.rpr-ingredient-quantity' ).text( Math.ceil( qnty * 1.45 ) );
					$ing.find( '.rpr-ingredient-unit' ).text( 'g' );
				}

				if ( unit === 'tbsp' || unit === 'tablespoon' || unit === 'tablespoons' ) {
					$ing.find( '.rpr-ingredient-quantity' ).text( Math.ceil( qnty * 1.45 ) );
					$ing.find( '.rpr-ingredient-unit' ).text( 'g' );
				}

			}

		}

		$('.rpr-filter-section input').click(function () {
			$('form#rpr-recipe-archive-filter').submit();
		});

		$('.rpr-filter-dropdown select').change(function () {
			$('form#rpr-recipe-archive-filter').submit();
		});

	});

})( jQuery );

function buildIframe(div) {
	const iframe = document.createElement('iframe');
	iframe.setAttribute('src', 'https://www.youtube.com/embed/' + div.dataset.id + '?autoplay=1&rel=0');
	iframe.setAttribute('frameborder', '0');
	iframe.setAttribute('allowfullscreen', '1');
	iframe.setAttribute('modestbranding', '1');
	iframe.setAttribute('allow', 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture');
	div.parentNode.replaceChild(iframe, div);
}

function speedUpYouTubeVideos() {
	const playerElements = document.getElementsByClassName('rpr-youtube-player');

	for (let n = 0; n < playerElements.length; n++) {

		const videoId = playerElements[n].dataset.id;
		const div = document.createElement('div');

		div.setAttribute('data-id', videoId);

		const thumbRes = (document.body.clientWidth > 640) ? 'maxresdefault.jpg' : 'hqdefault.jpg';
		const thumbNode = document.createElement('img');
		thumbNode.setAttribute('src', '//i.ytimg.com/vi/' + videoId + '/' + thumbRes);
		thumbNode.setAttribute('loading', 'lazy');
		thumbNode.setAttribute('alt', rpr_public_vars.rpr_youtube_thumb_alt);
		thumbNode.setAttribute('class', 'rpr-youtube-thumbnail');

		div.appendChild(thumbNode);
		const playButton = document.createElement('div');
		playButton.setAttribute('class', 'play');
		div.appendChild(playButton);
		div.onclick = function () {
			buildIframe(this);
		};

		playerElements[n].appendChild(div);
	}
}

document.addEventListener('DOMContentLoaded', speedUpYouTubeVideos);
