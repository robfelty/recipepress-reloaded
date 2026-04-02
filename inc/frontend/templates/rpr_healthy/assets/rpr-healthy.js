(function( $ ) {
    'use strict';

    $(function() {
        $('.rpr-print-button').on('click', function(e) {
            e.preventDefault();
            $(print_options.print_area).print({
                globalStyles: false,
                noPrintSelector: print_options.no_print_area,
                stylesheet: print_options.print_css,
            });
            return false;
        });

        $('.jump-to-recipe').on('click', function(e) {
            e.preventDefault();
            $('html, body').stop().animate({
                scrollTop: $('#ingredients').offset().top
            }, 1000, 'swing');
        });
    });

})( jQuery );
