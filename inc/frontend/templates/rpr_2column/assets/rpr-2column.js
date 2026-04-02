(function( $ ) {
    'use strict';

    $('.rpr-jump-to-recipe').on('click', function (e) {
        e.preventDefault();
        $('html, body').stop().animate({scrollTop: $('#ingredients').offset().top}, 1000, 'swing');
    });

    $(function() {
        $('a.rpr-print-recipe').click(function(e) {
            e.preventDefault();

            const printClass = '.' + $(this).data('print-area');

            $(printClass).print({
                globalStyles: false,
                noPrintSelector: print_options.no_print_area,
                stylesheet: print_options.print_css,
            });
            return false;
        });
    });

})( jQuery );
