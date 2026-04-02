(function($) {

  $(document).ready(function() {

    $('.rpr-source-fetch-data').on('click', function(e) {
      e.preventDefault();

      $.sanitize = function(input) {
        return input.replace(/<script[^>]*?>.*?<\/script>/gi, '').
            replace(/<[\/\!]*?[^<>]*?>/gi, '').
            replace(/<style[^>]*?>.*?<\/style>/gi, '').
            replace(/<![\s\S]*?--[ \t\n\r]*>/gi, '').
            trim();

      };

      const video_url = $('#rpr_recipe_video_url').val();
      let video_id = video_url.match(
          /(?:https?:\/{2})?(?:w{3}\.)?youtu(?:be)?\.(?:com|be)(?:\/watch\?v=|\/)([^\s&]+)/,
      );

      if (video_id !== null) {
        video_id = video_id[1];
      } else {
        alert('Invalid YouTube video URL provided. For now, this feature only supports YouTube videos.');
        return false;
      }

      $.ajax({
        url: rpr_script_vars.ajax_url,
        type: 'POST',
        data: {
          action: 'fetch_video_data',
          video_id: video_id,
          rpr_video_nonce: rpr_script_vars.rpr_video_nonce,
        },
      }).done(function(data) {
        const thumbnail_container = $('.rpr-video-thumb-container');

        if (data.data.hasOwnProperty('error')) {
          thumbnail_container.html('<p>' + data.data.error.message + '</p>');
          return;
        }

        let video = data.data.items[0];
        console.log(video);

        $('#rpr_recipe_video_title').val($.sanitize(video.snippet.title));
        $('#rpr_recipe_video_description').val($.sanitize(video.snippet.description));
        $('#rpr_recipe_video_date').val($.sanitize(video.snippet.publishedAt));
        $('#rpr_recipe_video_thumb_0').val($.sanitize(video.snippet.thumbnails.default.url));
        $('#rpr_recipe_video_thumb_1').val($.sanitize(video.snippet.thumbnails.high.url));
        $('#rpr_recipe_video_thumb_2').val($.sanitize(video.snippet.thumbnails.medium.url));

        thumbnail_container.html(
            '<img src="' + $.sanitize(video.snippet.thumbnails.high.url) + '" />',
        );
      }).fail(function(data) {
        alert(data.responseJSON.error.message);
      });
    });

    $('.rpr-source-del-data').on('click', function(e) {
      e.preventDefault();
      $('#rpr_recipe_video_url').val('');
      $('#rpr_recipe_video_title').val('');
      $('#rpr_recipe_video_description').val('');
      $('#rpr_recipe_video_date').val('');
      $('#rpr_recipe_video_thumb_0').val('');
      $('#rpr_recipe_video_thumb_1').val('');
      $('#rpr_recipe_video_thumb_2').val('');
      $('.rpr-video-thumb-container>img').remove();
    });

  });

})(jQuery);
