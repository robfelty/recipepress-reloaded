(function( $ ) {
	'use strict';

	console.log('RPR Admin Page')

	$(function() {
		// Adds selectize.js support to text boxes on settings page.
		if ( 'undefined' !== typeof window.Selectize ) {

			$('.rpr-filter-selected-taxonomies').selectize({
				create: true,
				plugins: ['remove_button', 'restore_on_backspace', 'drag_drop'],
			});

			$('#recipepress_settings\\[rpr_taxonomy_selection\\]').selectize({
				create: true,
				plugins: ['remove_button', 'restore_on_backspace', 'drag_drop']
			});

			$('#recipepress_settings\\[rpr_ingredient_unit_list\\]').selectize({
				create: true,
				plugins: ['remove_button', 'restore_on_backspace', 'drag_drop']
			});

			$('#recipepress_settings\\[rpr_serving_unit_list\\]').selectize({
				create: true,
				plugins: ['remove_button', 'restore_on_backspace', 'drag_drop']
			});

			$('#recipepress_settings\\[rpr_recipe_template_print_area\\]').selectize({
				create: true,
				plugins: ['restore_on_backspace', 'remove_button']
			});

			const no_print_data = [
				{key: '.no-print', value: '.no-print'},
				{key: '.rpr-instruction-image', value: '.rpr-instruction-image'},
				{key: '.rpr-terms-container', value: '.rpr-terms-container'},
				{key: '.rpr-description-container', value: '.rpr-description-container'},
				{key: '.rpr-source-container', value: '.rpr-source-container'},
				{key: '.rpr-nutrition-container', value: '.rpr-nutrition-container'},
				{key: '.rpr-ingredients-container', value: '.rpr-ingredients-container'},
				{key: '.rpr-times-container', value: '.rpr-times-container'},
				{key: '.rpr-instruction-container', value: '.rpr-instruction-container'},
				{key: '.rpr-notes-container', value: '.rpr-notes-container'}
			]

			function get_no_print( data = [] ) {
				const input = $('#recipepress_settings\\[rpr_recipe_template_no_print_area\\]').val()
				if (input === undefined) return
				for (const string of input.split(',')) {
					data.push({key: string, value: string})
				}

				return Array.from(new Set(data.map(JSON.stringify))).map(JSON.parse)
			}

			$('#recipepress_settings\\[rpr_recipe_template_no_print_area\\]').selectize({
				plugins: ['remove_button', 'restore_on_backspace', 'drag_drop'],
				options: get_no_print(no_print_data),
				valueField: 'value',
				labelField: 'key',
				searchField: 'key',
				create: function(input) {
					return {key: input, value: input};
				}
			});

			$('#recipepress_settings\\[rpr_diet_selection\\]').selectize({
				create: false,
				plugins: ['remove_button', 'restore_on_backspace', 'drag_drop'],
				options: rpr.rprSettings.dietOptions || [
					{key: 'DiabeticDiet', value: 'Diabetic'},
					{key: 'GlutenFreeDiet', value: 'Gluten-Free'},
					{key: 'HalalDiet', value: 'Halal'},
					{key: 'HinduDiet', value: 'Hindu'},
					{key: 'KosherDiet', value: 'Kosher'},
					{key: 'LowCalorieDiet', value: 'Low Calorie'},
					{key: 'LowFatDiet', value: 'Low Fat'},
					{key: 'LowLactoseDiet', value: 'Low Lactose'},
					{key: 'LowSaltDiet', value: 'Low Salt'},
					{key: 'VeganDiet', value: 'Vegan'},
					{key: 'VegetarianDiet', value: 'Vegetarian'},
				],
				valueField: 'key',
				labelField: 'value',
				searchField: 'value'
			});

			// $('#recipepress_settings\\[rpr_additional_nutrition\\]').selectize({
			// 	create: true,
			// 	plugins: ['restore_on_backspace', 'remove_button', 'drag_drop']
			// })

			function camelize(str) {
				return str.toLowerCase().replace(/[^a-zA-Z0-9]+(.)/g, (m, chr) => chr.toUpperCase());
			}

			const ad_nutrition = rpr.rprSettings.nutritionOptions || [
				// {key: 'calories', value: 'Calories'},
				// {key: 'carbohydrateContent', value: 'Carbohydrates'},
				{key: 'cholesterolContent', value: 'Cholesterol'},
				// {key: 'fatContent', value: 'Fat'},
				{key: 'fibreContent', value: 'Fiber'},
				// {key: 'proteinContent', value: 'Protein'},
				{key: 'saturatedFatContent', value: 'Saturated Fat'},
				{key: 'sodiumContent', value: 'Sodium'},
				{key: 'sugarContent', value: 'Sugar'},
				{key: 'transFatContent', value: 'Trans Fat'},
				{key: 'unsaturatedFatContent', value: 'Unsaturated Fat'},
			]

			function get_add_nutrition( data = [] ) {
				const input = $('#recipepress_settings\\[rpr_additional_nutrition\\]').val()
				if (input === undefined) return
				for (const string of input.split(',')) {
					data.push({key: string, value: string})
				}

				return Array.from(new Set(data.map(JSON.stringify))).map(JSON.parse)
			}

			$('#recipepress_settings\\[rpr_additional_nutrition\\]').selectize({
				plugins: ['remove_button', 'restore_on_backspace', 'drag_drop'],
				options: get_add_nutrition(ad_nutrition),
				valueField: 'key',
				labelField: 'value',
				searchField: 'value',
				create: function(input) {
					return {key: camelize(input), value: input};
				}
			})
		}

		// Adds and removes 'checked' class on recipe template selection
		$('.template-label-image').on('click', function() {
			var x = $(this);
			$('.template-label-image').removeClass('checked');
			$(x).closest('.template-options').find(':radio').prop('checked', true);
			$(x).addClass('checked');
		});

		// Ingredient thumbnail selector button.
		$('.ingredient_custom_meta__image')
			.on('click', function (e) {
				e.preventDefault()

				var custom_uploader = wp.media({
					title: 'Thumbnail image',
					button: {
						text: 'Use image'
					},
					multiple: false  // Set this to true to allow multiple files to be selected
				})
					.on('select', function () {
						var attachment = custom_uploader.state()
							.get('selection')
							.first()
							.toJSON()
						$('.ingredient_custom_meta__image').css('background-image', 'url(' + attachment.sizes.thumbnail.url + ')')
						$('#ingredient_custom_meta\\[thumbnail_image\\]\\[url\\]').val(attachment.sizes.thumbnail.url)
						$('#ingredient_custom_meta\\[thumbnail_image\\]\\[id\\]').val(attachment.id)
					})
					.open()
			})

		// Ingredient thumbnail selector button.
		$('.photo-container')
		.on('click', function(e) {
			e.preventDefault();
			console.log(e);

			var custom_uploader = wp.media( {
				title: 'Author photo',
				button: {
					text: 'Use image'
				},
				multiple: false  // Set this to true to allow multiple files to be selected
			} )
			.on('select', function() {
				let attachment = custom_uploader.state()
				.get('selection')
				.first()
				.toJSON();
				console.log(attachment);

				let img = $('<img class="image-file">');
				img.attr('src', attachment.sizes.medium.url);
				img.attr('alt', attachment.alt);
				$('.photo-container .image').html(img);

				$('.image-file.saved').remove();
				$('input.image-file').val(attachment.sizes.medium.url).trigger('change');
			} )
			.open();
		});

		// Codemirror settings for the CSS box in our settings page.
		if ( 'undefined' !== typeof wp.codeEditor ) {
			$(function () {
				var css_box = $('.rpr-css-box');
				var editorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};
				editorSettings.codemirror = _.extend(
					{},
					editorSettings.codemirror,
					{
						indentUnit: 2,
						tabSize: 2,
						mode: 'css'
					}
				);
				if (css_box.length > 0) {
					var editor = wp.codeEditor.initialize(css_box, editorSettings);
				}
			});
		}

		// Reset settings page options via AJAX. Is this necessary?
		$( '.rpr-options-reset' ).on( 'click', function( e ) {
			e.preventDefault();
			$.ajax( {
				type: 'post',
				url: rpr_script_vars.ajax_url,
				data: {
					action: 'reset_all_options',
					reset_nonce: rpr_script_vars.rpr_options_reset_nonce
				}
			} );

			alert( rpr_script_vars.rpr_options_reset_msg );
		} );

		// Adds default WP color picker UI to settings page.
		if ( 'undefined' !== typeof $.minicolors ) {
			$( '.rpr-color-input' ).minicolors({
				format: 'rgb',
				opacity: true,
				swatches: [
					'#F44336', '#E91E63', '#9C27B0', '#673AB7', '#2196F3', '#03A9F4', '#00BCD4',
					'#009688', '#4CAF50', '#8BC34A', '#CDDC39', '#FFEB3B', '#FFC107', '#FF9800',
				]
			});
		}

		// Handles the background tasks.
		$('.rpr-update-button').on('click', function(e) {
			e.preventDefault();
			$.ajax({
						type: 'POST',
						url: rpr_script_vars.ajax_url,
						data: {
							action: 'run_background_tasks',
							update_task_nonce: rpr_script_vars.rpr_update_task_nonce,
							update_task_target: $(e.target).attr('data-update-notice'),
						},
					},
			).done(function() {
				$('.rpr-update-button').parent().hide();
			});
		});

		// If the widget is updated, re-attach Selectize
		$( document ).on( 'widget-updated',  function () {
			if ( 'undefined' !== typeof window.Selectize ) {
				$('.rpr-filter-selected-taxonomies').selectize({
					create: true,
					plugins: ['remove_button', 'restore_on_backspace', 'drag_drop'],
				});
			}
		} );


		var recipe_slug  = $('#recipepress_settings\\[rpr_recipe_slug\\]');
		var recipe_label = $('label.rpr-settings-label b');

		recipe_label.text(recipe_slug.val());
		recipe_slug.on('keyup change paste', function(e) {
			recipe_label.text(e.target.value);
			if (e.target.value.length < 3) {
				$('form #submit').attr('disabled', true);
			} else {
				$('form #submit').attr('disabled', false);
			}
		});

		// Reset the colors of the "About Me" widget.
		$('.rpr.author-profile .reset').on('click', function() {
			let input = $(this).siblings("input[type=color]");
			let color = input.data('default');
			input.val(color);
			input.trigger('change'); // To activate the `Save` button
		});


	});

})( jQuery );
