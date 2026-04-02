/**
 * This file adds some LIVE to the Theme Customizer live preview. To leverage
 * this, set your custom settings to 'postMessage' and then add your handling
 * here. Your javascript should grab settings from customizer controls, and
 * then make any necessary changes to the page using jQuery.
 */
( function( $ ) {

  wp.customize('rpr_template[delicious][share_heading]', function(value) {
    value.bind(function(newval) {
      $('.rpr.ig-share-container h5').html(newval);
    });
  });

  wp.customize('rpr_template[delicious][share_body]', function(value) {
    value.bind(function(newval) {
      $('.rpr.ig-share-container p').html(newval);
    });
  });

  wp.customize( 'rpr_template[delicious][color_1]', function( value ) {
    value.bind( function( newval ) {
      $('.rpr.recipe-container').css('border-color', newval );
      $('.rpr.meta-container').css('background-color', newval );
      $('.rpr.thumbnail-container').css('border-color', newval );
      $('.rpr.ig-share-container').css('background-color', newval );
    } );
  } );

  wp.customize( 'rpr_template[delicious][color_2]', function( value ) {
    value.bind( function( newval ) {
      $('.rpr.meta-container').css('color', newval );
      $('.rpr.meta-container a').css('color', newval );
      $('.rpr.ig-share-container').css('color', newval );
      $('.rpr.ig-share-container a').css('color', newval );
      $('.rpr.ig-share-container .rpr-icon svg').css('fill', newval );
    } );
  } );

} )( jQuery );
