(function ($) {
    $('.buttons-position').on('click', function(){
    	if($(this).val() === '4'){
			$('.radio-button-wrapper small').show();
		}
	});

	$( '.social-media-sites' ).selectize( {
		valueField: 'network',
		labelField: 'network',
		searchField: 'network',
		options: [
			{network: 'Facebook'},
			{network: 'Twitter'},
			{network: 'Google+'},
			{network: 'Yummly'},
			{network: 'Flipboard'},
			{network: 'Pinterest'},
			{network: 'Mix'},
			{network: 'Tumblr'},
			{network: 'Reddit'},
			{network: 'Pocket'},
			{network: 'Digg'},
			{network: 'InstaPaper'},
			{network: 'Buffer'},
			{network: 'Email'},
			{network: 'Print'}
		],
		create: false,
		plugins: ['remove_button', 'restore_on_backspace', 'drag_drop']
	} );

	// Adds default WP color picker UI to settings page
	$( '.buttons-color' ).minicolors({
		format: 'rgb',
		opacity: true,
		swatches: [
			'#F44336', '#E91E63', '#9C27B0', '#673AB7', '#2196F3', '#03A9F4', '#00BCD4',
			'#009688', '#4CAF50', '#8BC34A', '#CDDC39', '#FFEB3B', '#FFC107', '#FF9800'
		]
	});
}(jQuery));
