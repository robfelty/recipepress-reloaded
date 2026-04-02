/**
 * Nutritional information metabox functions.
 *
 * @since 1.0.0
 */

var nutritionCalc = (function($){
    'use strict';

    /* PRIVATE METHODS */
    function _init() {
        /* Automatically convert calories to joules and vice versa*/
        $('#rpr_recipe_calorific_value').on('change', function(e) {
            var kilojoule = Math.round($('#rpr_recipe_calorific_value').val() * 4.18);
            $('#rpr_recipe_calorific_value_kj').val( kilojoule );
        });
        $('#rpr_recipe_calorific_value_kj').on('change', function(e) {
            var kcal = Math.round($('#rpr_recipe_calorific_value_kj').val() / 4.18);
            $('#rpr_recipe_calorific_value').val( kcal );
        });

    }

    /* PUBLIC ACCESSOR METHODS */
    return {
        init: _init
    };

})(jQuery);


// Initialize.
jQuery(document).ready(function($){
    'use strict';
    nutritionCalc.init();
});
