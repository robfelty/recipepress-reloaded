var add_source_link_btn = (function($){
    'use strict';

    // Used to track whether or not the link dialogue actually existed on this page, ie was wp_editor invoked.
    var _link_sideload = true;

    /* PRIVATE METHODS */
    function _init() {

        $('.rpr-source-add-link').on('click', function(e) {

            e.preventDefault();

            var button = $(this);
            var link   = button.siblings('.rpr-recipe-source-link');

            _addLinkListeners(link);
            _link_sideload = false;

            // Load existing data for editing
            wpLink.setDefaultValues = function () {
                $('#wp-link-url').val(link.val());
            };

            if ( typeof wpActiveEditor != 'undefined') {
                wpLink.open();
                wpLink.textarea = $(link);
            } else {
                window.wpActiveEditor = true;
                _link_sideload = true;
                wpLink.open();
                wpLink.textarea = $(link);
            }
            return false;
        });

        $('.rpr-source-del-link').on('click', function(e) {

            e.preventDefault();

            var del_button = $(this);
            var add_button = del_button.siblings('.rpr-source-add-link');
            var link       = del_button.siblings('.rpr-recipe-source-link');
            var text       = del_button.siblings('.rpr-recipe-source');

            link.val('');
            text.val('');
            add_button.removeClass('has-link');
            del_button.addClass('rpr-hidden');
        });

    }

    /* LINK EDITOR EVENT HACKS */
    function _addLinkListeners(link) {

        $('#wp-link-submit').on('click', function(e) {

            e.preventDefault();

            var linkAtts = wpLink.getAttrs();
            link.val(linkAtts.href);

            // Change icon color and make delete link button visible
            var add_button = link.siblings('.rpr-source-add-link');
            var del_button = link.siblings('.rpr-source-del-link');

            del_button.removeClass('rpr-hidden');
            add_button.addClass('has-link');
            /**
             * Prevent the link from being added to an editor field
             * @link http://stackoverflow.com/questions/33156478/how-to-prevent-wordpress-built-in-browse-link-entering-the-data-in-wp-editor
             */
            var $frame = $('#content_ifr'),
                $added_links = $frame.contents().find('a');

            $added_links.each(function(){
                if ($(this).attr('href') === linkAtts.href) {
                    $(this).remove();
                }
            });

            _removeLinkListeners();
            return false;
        });

        $('#wp-link-cancel').on('click', function(e) {

            e.preventDefault();

            _removeLinkListeners();
            return false;
        });
    }

    function _removeLinkListeners() {
        if (_link_sideload) {
            if (typeof wpActiveEditor != 'undefined') {
                wpActiveEditor = undefined;
            }
        }

        wpLink.close();
        wpLink.textarea = $('html');//focus on document

        $('body').off('click', '#wp-link-submit');
        $('body').off('click', '#wp-link-cancel');
    }

    /* PUBLIC ACCESSOR METHODS */
    return {
        init: _init
    };

})(jQuery);

// Initialize.
jQuery(document).ready(function ($) {
    'use strict';
    add_source_link_btn.init();
});
