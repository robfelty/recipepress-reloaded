(function( $ ) {
    'use strict';

    $(function() {

        const appID  = '7cbd246f';
        const appKey = 'eec01b95e813c9cdd82f74bcddbafc79';
        const url    = 'https://api.edamam.com/api/nutrition-details?app_id=' + appID + '&app_key=' + appKey;

        let recipe      = null;
        let ingredients = [];
        let recipeYield = $('#rpr_recipe_servings').val().trim() + ' ' + $('#rpr_recipe_servings_type').val().trim();
        let recipeTitle = $('#titlewrap #title').val().trim();

        $('table#recipe-ingredients tr.rpr-ing-row').each(function () {

            let row = $(this);

            row.each(function () {
                let item   = $(this), ingredient = [];
                let amount = item.find('.ingredients_amount').val().trim();
                let unit   = item.find('.ingredients_unit').val().trim();
                let name   = item.find('.rpr-ing-name-input').val().trim();
                let note   = item.find('.ingredients_notes').val().trim();

                ingredient.push(amount, unit, name);
                ingredient = ingredient.filter(item => item); // Remove empty items
                ingredients.push(ingredient.join(' ').trim());
            });
        });

        recipe = {
            title: recipeTitle,
            ingr: ingredients.filter(item => item), // Remove empty items
            yield: recipeYield
        };

        // Create CORS request.
        function createCORSRequest(method, url) {

            let xhr = new XMLHttpRequest();

            if ("withCredentials" in xhr) {
                xhr.open(method, url, true); // XHR for Chrome/Firefox/Opera/Safari.
            } else if (typeof XDomainRequest != "undefined") {
                xhr = new XDomainRequest(); // XDomainRequest for IE.
                xhr.open(method, url);
            } else {
                xhr = null; // CORS not supported.
            }

            return xhr;
        }

        // Make the actual CORS request.
        function makeCorsRequest() {

            let xhr = createCORSRequest('POST', url);

            if (!xhr) {
                console.log('CORS not supported');
                return;
            }

            // Response handlers.
            xhr.onload = function() {
                console.log(xhr.responseText);
            };

            xhr.onerror = function() {
                console.log('Whoops, there was an error making the request.');
            };

            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.send(JSON.stringify(recipe));
        }

        // makeCorsRequest();
    });

})( jQuery );
