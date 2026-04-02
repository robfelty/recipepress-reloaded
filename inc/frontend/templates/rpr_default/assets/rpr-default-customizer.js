/**
 * This file adds some LIVE to the Theme Customizer live preview. To leverage
 * this, set your custom settings to 'postMessage' and then add your handling
 * here. Your javascript should grab settings from customizer controls, and
 * then make any necessary changes to the page using jQuery.
 */
(function($) {

  wp.customize('rpr_template[default][color_1]', function(setting) {
    setting.bind(function(newValue) {
      $('.rpr-terms-container ul.rpr-term-list, .rpr-times-container ul.rpr-times, .rpr-nutrition-container ul.rpr-nutrition').css('border-color', newValue);
      $('.rpr-instruction-list .rpr-instruction::before').css('background-color', newValue);
      $('ul.rpr-ingredient-list').css('color', newValue);
    } );
  });

})(window.jQuery);
