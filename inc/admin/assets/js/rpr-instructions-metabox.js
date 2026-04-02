var ins_table = (function($) {
  'use strict';

  /* PRIVATE METHODS
  -------------------------------------------------------------- */

//add event listeners
  function _init() {
    // Add image button
    $('.rpr-ins-image-set').on('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      var button = $(this);
      _add_instruction_image(button);
    });
    // Click on image
    $('.rpr_recipe_instructions_thumbnail').on('click', function(e) {
      e.preventDefault();
      var button = $(this).siblings().find('.rpr-ins-image-set');
      _add_instruction_image(button);
    });
    // Delete image button
    $('.rpr-ins-image-del').on('click', function(e) {
      e.preventDefault(e);
      e.stopPropagation();
      var button = $(this);
      _remove_instruction_image(button);
    });

    $('#rpr-ins-add-row-ins').on('click', function(e) {
      e.preventDefault();
      _add_instruction_row();
      _update_instruction_index();
    });
    // Add row button
    $('tbody .rpr-ins-add-row').on('click', function(e) {
      var addbutton = $(this);
      e.preventDefault();
      if (addbutton.parent().parent().attr('class') === 'rpr-ins-row') {
        _add_instruction_row(addbutton);
      } else {
        _add_instruction_heading(addbutton);
      }
      _update_instruction_index();
    });

    // Delete instruction row button
    $('.rpr-ins-remove-row').on('click', function(e) {
      var delbutton = $(this);
      e.preventDefault();
      _delete_instruction_row(delbutton);
      _update_instruction_index();
    });
    // Add ingredient row on tab
    $('#recipe-instructions .rpr-ins-instruction').unbind('keydown').last().bind('keydown', function(e) {
      var keyCode = e.keyCode || e.which;

      if (keyCode === 9) {
        e.preventDefault();
        _add_instruction_row();
        _update_instruction_index();
      }
    });

    // Add instruction heading
    $('#rpr-ins-add-row-grp').on('click', function(e) {
      e.preventDefault();
      _add_instruction_heading();
      _update_instruction_index();
    });
    $('#recipe-instructions tbody').sortable({
      opacity: 0.6,
      revert: true,
      cursor: 'move',
      handle: '.sort-handle',
      axis: 'y',
      containment: 'parent',
      update: function() {
        _update_instruction_index();
      },
    });
  }

  /*
   * Add a new empty row to the ingredient table
   */
  function _add_instruction_row(addbutton) {
    var last_row = $('#recipe-instructions tbody tr:last');
    var last_instruction = $('#recipe-instructions tr.rpr-ins-row:last');

    if (addbutton) {
      last_row = addbutton.parent().parent();
      last_instruction = last_row;
    }
    var clone_instruction = last_instruction.clone(true);

    clone_instruction.insertAfter(last_row).find('input').val('');
    clone_instruction.find('textarea').val('');
    clone_instruction.find('img.rpr_recipe_instructions_thumbnail').attr('src', '').hide();

    clone_instruction.find('.rpr-ins-instruction textarea').focus();
  }

  function _delete_instruction_row(delbutton) {
    delbutton.parents('tr').remove();
  }

  /**
   * Add ingredient heading
   */
  function _add_instruction_heading(addbutton) {
    var clone_from = $('#recipe-instructions tr.instruction-group-stub');
    var last_row = $('#recipe-instructions tbody tr:last');
    if (addbutton) {
      last_row = addbutton.parent().parent();
    }
    var clone_group = clone_from.clone(true);

    clone_group.insertAfter(last_row).
        removeClass('instruction-group-stub').
        removeClass('rpr-hidden').
        addClass('instruction-group').
        find('input').
        val('').
        focus();
  }

  /**
   * Recalculate all index numbers on the ingredient list
   * @returns {none}
   */
  function _update_instruction_index() {
    var rows = $('#recipe-instructions tbody').find('tr').not('.rpr-hidden');

    $(rows).each(function(rowIndex) {
      $(this).find('input, select, textarea').each(function() {
        var name = $(this).attr('name');
        name = name.replace(/\[(\d+)\]/, '[' + (rowIndex + 1) + ']');
        $(this).attr('name', name).attr('id', name);
      });

      $(this).find('.rpr-ins-sort input.instructions_sort').attr('value', rowIndex);
      $(this).find('.rpr-ins-del').show();
    });
  }

  /*
   * Insert an instruction step image using wp uploader
   * @param {type} button
   */
  function _add_instruction_image(button) {
    const image = button.children('.rpr_recipe_instructions_image');
    const preview = button.children('.rpr_recipe_instructions_thumbnail');

    if (typeof wp.media === 'function') {
      var custom_uploader = wp.media({
        title: 'Add instruction image', //ins_trnsl.ins_img_upload_title,
        button: {
          text: 'Add image',//ins_trnsl.ins_img_upload_text
        },
        multiple: false,
      }).on('select', function() {
        const attachment = custom_uploader.state().get('selection').first().toJSON();

        if (attachment.sizes.hasOwnProperty('medium')) {
          $(preview).attr('src', attachment.sizes.medium.url);
        } else {
          $(preview).attr('src', attachment.url);
        }

        $(image).val(attachment.id).trigger('change');
        $(preview).show();
        button.children('.rpr-ins-image-del').show();
      }).open();

    } else { //fallback
      post_id = button.attr('rel');

      tb_show(button.attr('value'), 'wp-admin/media-upload.php?post_id=' + post_id + '&type=image&TB_iframe=1');

      window.send_to_editor = function(html) {
        var img = $('img', html);
        var imgurl = img.attr('src');
        var classes = img.attr('class');
        var id = classes.replace(/(.*?)wp-image-/, '');
        image.val(id).trigger('change');
        preview.attr('src', imgurl);
        tb_remove();
      };
      $(preview).show();
      button.siblings('.rpr-ins-image-del').show();
    }

  }

  /*
   * Remove an existing instruction image
   * @param {type} button
   * @returns {undefined}
   */
  function _remove_instruction_image(button) {
    const image = button.siblings('.rpr_recipe_instructions_image');
    const preview = button.siblings('.rpr_recipe_instructions_thumbnail');
    const addBtn = button.siblings('.rpr-ins-image-set').not(button);

    image.val('');
    preview.attr('scr', '').hide();
    addBtn.show();
    button.hide();
  }

  /* PUBLIC ACCESSOR METHODS
  -------------------------------------------------------------- */
  return {
    init: _init,
  };

})(jQuery);

// Initialise
jQuery(document).ready(function($) {
  'use strict';
  ins_table.init();
});
