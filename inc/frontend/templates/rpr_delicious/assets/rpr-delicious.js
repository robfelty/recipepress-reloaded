(function( $ ) {
    'use strict';

    $('.rpr-jump-to-recipe').on('click', function (e) {
        e.preventDefault();
        $('html, body').stop().animate({scrollTop: $('#rpr-recipe').offset().top}, 1000, 'swing');
    });

     $(function() {
         $('a.rpr-print-recipe').on('click', function() {
             $(print_options.print_area).print({
                 globalStyles: false,
                 noPrintSelector: print_options.no_print_area,
                 stylesheet: print_options.print_css,
             });
             return false;
         });
     });

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
    });

})( jQuery );
