(function ($) {
	$(document).ready(function(){

		var carousel = $('.rpr-carousel');
		var recipes = new Stoor({ namespace: 'recipes', storage: 'session'});
		var data = {
			'action': 'get_recipes',
			'carousel_nonce': 1234,
			'recipe_id': rpr_public_vars.recipe_id
		};

		if (recipes.get('results')) {
			console.log('Have data...');
			var results = recipes.get('results');
			var html = '';
			for ( var i = 0; i < results.data.length; i++ ) {
				html += '<div class="rpr-carousel-item">';
				html += '<a href="' + results.data[i].url + '">';
				html += '<img src="' + results.data[i].thumb + '" alt="View recipe" width="50px">';
				html += '<p>' + results.data[i].title + '</p>';
				html += '</a>';
				html += '</div>';
			}
			carousel.html(html);
			carousel.slick({
				dots: false,
				infinite: false,
				speed: 500,
				slidesToShow: 5,
				slidesToScroll: 5,
				responsive: [
					{
						breakpoint: 1024,
						settings: {
							slidesToShow: 3,
							slidesToScroll: 3,
						}
					},
					{
						breakpoint: 600,
						settings: {
							slidesToShow: 2,
							slidesToScroll: 2
						}
					},
					{
						breakpoint: 480,
						settings: {
							slidesToShow: 1,
							slidesToScroll: 1
						}
					}
				]
			});
		} else {
			$.ajax({
				url: rpr_public_vars.ajax_url,
				data: data,
				success: function (res) {
					console.log('Fetching data...');
					recipes.set('results', res);
					var html = '';
					for ( var i = 0; i < res.data.length; i++ ) {
						html += '<div class="rpr-carousel-item">';
						html += '<a href="' + res.data[i].url + '">';
						html += '<img src="' + res.data[i].thumb + '" alt="View recipe" width="50px">';
						html += '<p>' + res.data[i].title + '</p>';
						html += '</a>';
						html += '</div>';
					}
					carousel.html(html);
					carousel.slick({
						dots: false,
						infinite: false,
						speed: 500,
						slidesToShow: 5,
						slidesToScroll: 5,
						responsive: [
							{
								breakpoint: 1024,
								settings: {
									slidesToShow: 3,
									slidesToScroll: 3,
								}
							},
							{
								breakpoint: 600,
								settings: {
									slidesToShow: 2,
									slidesToScroll: 2
								}
							},
							{
								breakpoint: 480,
								settings: {
									slidesToShow: 1,
									slidesToScroll: 1
								}
							}
						]
					});
				},
				error: function (res) {
					console.log(res);
				}
			});
		}

	});
}(jQuery));
