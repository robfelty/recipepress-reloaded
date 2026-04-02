(function ($) {

    // Adds default WP color picker UI to settings page
    $( '.cta-color' ).minicolors({
        format: 'rgb',
        opacity: true,
        position: 'top right',
        swatches: [
            '#F44336', '#E91E63', '#9C27B0', '#673AB7', '#2196F3', '#03A9F4', '#00BCD4',
            '#009688', '#4CAF50', '#8BC34A', '#CDDC39', '#FFEB3B', '#FFC107', '#FF9800'
        ]
    });

}(jQuery));
