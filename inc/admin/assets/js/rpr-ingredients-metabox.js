var ingredient_metadata = (function($) {
  'use strict';

  /* PRIVATE METHODS */
  function _init() {
    // Add ingredient button.
    $('#rpr-ing-add-row-ing').on('click', function(e) {
      e.preventDefault();
      _add_ingredient_row();
      _update_ingredient_index();
    });
    // Add ingredient row on tab
    $('#recipe-ingredients .rpr-ing-note').
        unbind('keydown').
        last().
        bind('keydown', function(e) {
          var keyCode = e.keyCode || e.which;

          if (keyCode === 9) {
            e.preventDefault();
            _add_ingredient_row();
            _update_ingredient_index();
          }
        });
    // Add row button
    $('tbody .rpr-ing-add-row').on('click', function(e) {
      e.preventDefault();
      var addbutton = $(this);
      if (addbutton.parent().parent().hasClass('rpr-ing-row')) {
        _add_ingredient_row(addbutton);
      } else {
        _add_ingredient_heading(addbutton);
      }
      _update_ingredient_index();
    });
    // Delete ingredient button
    $('.rpr-ing-remove-row').on('click', function(e) {
      var delbutton = $(this);
      e.preventDefault();
      _delete_ingredient_row(delbutton);
      _update_ingredient_index();
      //_update_ingredient_delbuttons();
    });
    // Add ingredient heading
    $('#rpr-ing-add-row-grp').on('click', function(e) {
      e.preventDefault();
      _add_ingredient_heading();
      _update_ingredient_index();
      //_update_ingredient_delbuttons();
    });
    $('#rpr_ingredients_metabox tbody').sortable({
      opacity: 0.6,
      revert: true,
      cursor: 'move',
      handle: '.sort-handle',
      axis: 'y',
      containment: 'parent',
      update: function() {
        _update_ingredient_index();
      },
    });
  }

  /*
   * Add a new empty row to the ingredient table
   */
  function _add_ingredient_row(addbutton) {
    var last_row = $('#recipe-ingredients tbody tr:last');
    var last_ingredient = $('#recipe-ingredients tr.rpr-ing-row:last');

    if (addbutton) {
      last_row = addbutton.parent().parent();
      last_ingredient = last_row;
    }

    var clone_ingredient = last_ingredient.clone(true);

    clone_ingredient.insertAfter(last_row).find('input').val('');

    last_ingredient.find('input').attr('placeholder', '');
    clone_ingredient.find('.rpr-ing-del').show();

    clone_ingredient.find('.rpr-ing-amount input').focus();
  }

  function _delete_ingredient_row(delbutton) {
    delbutton.parents('tr').remove();
  }

  /**
   * Add ingredient heading
   */
  function _add_ingredient_heading(addbutton) {
    var clone_from = $('#recipe-ingredients tr.ingredient-group-stub');
    var last_row = $('#recipe-ingredients tbody tr:last');
    if (addbutton) {
      last_row = addbutton.parent().parent();
    }
    var clone_group = clone_from.clone(true);

    clone_group.insertAfter(last_row).
        removeClass('ingredient-group-stub').
        removeClass('rpr-hidden').
        addClass('ingredient-group').
        find('input').
        val('').
        focus();
  }

  /**
   * Recalculate all index numbers on the ingredient list
   * @returns {none}
   */
  function _update_ingredient_index() {
    var rows = $('#recipe-ingredients tbody').find('tr').not('.rpr-hidden');

    $(rows).each(function(rowIndex) {
      $(this).find('input, select, textarea').each(function() {
        var name = $(this).attr('name');
        name = name.replace(/\[(\d+)\]/, '[' + (rowIndex + 1) + ']');
        var id = $(this).attr('id');
        id = id.replace(/\_(\d+)/, '_' + (rowIndex + 1));
        $(this).attr('name', name);
        $(this).attr('id', id);
        if ($(this).attr('onfocus')) {
          var onf = $(this).attr('onfocus');
          onf = onf.replace(/\_(\d+)/, '_' + (rowIndex + 1));
          $(this).attr('onfocus', onf);
        }
      });

      $(this).
          find('.rpr-ing-sort input.ingredients_sort').
          attr('value', rowIndex);
      $(this).find('.rpr-ing-del').show();
    });
    //$( '#recipe-ingredients tbody').find( 'tr.rpr-ing-row:first .rpr-ing-del').hide();

  }

  function _update_ingredient_delbuttons() {
    $('#recipe-ingredients tbody').
        find('tr.rpr-ing-row:first .rpr-ing-del').
        hide();
  }

  /* PUBLIC ACCESSOR METHODS */
  return {
    init: _init,
  };

})(jQuery);

var add_link_btn = (function($) {
  'use strict';
  var _link_sideload = true; // used to track whether the link dialogue actually existed on this page, ie was
  // wp_editor invoked.

  /* PRIVATE METHODS */
  function _init() {
    $('.rpr-ing-add-link').on('click', function(e) {
      e.preventDefault();

      var button = $(this);
      var link_val_container = button.siblings('.rpr_recipe_ingredients_link');
      var link_target_container = button.siblings('.rpr_recipe_ingredients_target');

      _addLinkListeners(link_val_container, link_target_container);
      _link_sideload = false;

      // load existing data for editing
      wpLink.setDefaultValues = function() {
        $('#wp-link-url').val(link_val_container.val());
        // $('#wp-link-text').val(link_val_container.val());

        if (link_target_container.val()) {
          $('#wp-link-target').attr('checked', 'checked');
        }
      };

      if (typeof wpActiveEditor != 'undefined') {
        wpLink.open();
        wpLink.textarea = $(link_val_container);
      } else {
        window.wpActiveEditor = true;
        _link_sideload = true;
        wpLink.open();
        wpLink.textarea = $(link_val_container);
      }
      return false;
    });

    $('.rpr-ing-del-link').on('click', function(e) {
      e.preventDefault();

      var delbutton = $(this);
      var addbutton = delbutton.siblings('.rpr-ing-add-link');
      var link_val_container = delbutton.siblings('.rpr_recipe_ingredients_link');

      link_val_container.val('');
      addbutton.removeClass('has-link');
      delbutton.addClass('rpr-hidden');
    });

  }

  /* LINK EDITOR EVENT HACKS */
  function _addLinkListeners(link_val_container, link_target_container) {
    $('#wp-link-submit').on('click', function(e) {
      e.preventDefault();

      var linkAtts = wpLink.getAttrs();

      link_val_container.val(linkAtts.href);
      link_target_container.val(linkAtts.target);

      // change icon color and make delete link button visible
      var addbutton = link_val_container.siblings('.rpr-ing-add-link');
      var delbutton = link_val_container.siblings('.rpr-ing-del-link');

      delbutton.removeClass('rpr-hidden');
      addbutton.addClass('has-link');
      /**
       * Prevent the link from being added to an editor field
       * @link http://stackoverflow.com/questions/33156478/how-to-prevent-wordpress-built-in-browse-link-entering-the-data-in-wp-editor
       */
      var $frame = $('#content_ifr'),
          $added_links = $frame.contents().find('a');

      $added_links.each(function() {
        if ($(this).attr('href') === linkAtts.href) {
          $(this).remove();
        }
      });

      _removeLinkListeners();
      return false;
    });

    $('#wp-link-cancel').on('click', function(event) {
      _removeLinkListeners();
      return false;
    });
  }

  function _removeLinkListeners() {
    if (_link_sideload) {
      if (typeof wpActiveEditor != 'undefined') {
        window.wpActiveEditor = undefined;
      }
    }

    wpLink.close();
    wpLink.textarea = $('html');//focus on document

    $('#wp-link-submit').off('click');
    $('#wp-link-cancel').off('click');
  }

  /* PUBLIC ACCESSOR METHODS */
  return {
    init: _init,
  };

})(jQuery);

// Initialize
jQuery(document).ready(function($) {
  'use strict';
  ingredient_metadata.init();
  add_link_btn.init();
});
