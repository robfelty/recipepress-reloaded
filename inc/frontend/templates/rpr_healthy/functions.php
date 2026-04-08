<?php

defined( 'ABSPATH' ) || exit;
/**
 * Registers the stylesheet for the Healthy template.
 *
 * @package Recipepress
 *
 * @uses wp_enqueue_style
 * @return void
 */

use const Recipepress\PLUGIN_VERSION;
use Recipepress\Inc\Frontend\Template;
use Recipepress\Inc\Core\Options;

// Enqueue our template specific script and style.
add_action( 'wp_enqueue_scripts', 'rpr_healthy_styles' );
add_action( 'wp_enqueue_scripts', 'rpr_healthy_scripts' );

// If we're on a single recipe post, add the JSON-LD data to the HTML <head> tag.
// Otherwise, it will be added to the <body> via the 'recipe.php' file.
add_action( 'wp_head', 'rpr_add_json_to_head' );

/**
 * Loads a CSS file attached to this template, for convenience.
 */
function rpr_healthy_styles() {
	wp_register_style( 'rpr-healthy-style', plugin_dir_url( __FILE__ ) . 'assets/rpr-healthy.css', array(), PLUGIN_VERSION, 'screen' );
	wp_register_style( 'rpr-healthy-print', plugin_dir_url( __FILE__ ) . 'assets/rpr-healthy-print.css', array(), PLUGIN_VERSION, 'print' );
}


/**
 * Loads a JS file attached to this template, for convenience.
 */
function rpr_healthy_scripts() {

	wp_register_script( 'rpr-healthy-script', plugin_dir_url( __FILE__ ) . 'assets/rpr-healthy.js', array( 'jquery' ), PLUGIN_VERSION, true );
	wp_localize_script(
		'rpr-healthy-script',
		'print_options',
		array(
			'print_area'    => Options::get_option( 'rpr_recipe_template_print_area' ),
			'no_print_area' => Options::get_option( 'rpr_recipe_template_no_print_area' ),
			'print_css'     => plugin_dir_url( __FILE__ ) . 'assets/rpr-healthy-print.css',
		)
	);
}

/**
 * Get our schema data from the Template class and echo it for convenience.
 */
function recipe_schema() {

	if ( ! is_singular( 'rpr_recipe' ) ) {
		return null;
	}

	$recipe_id = ! empty( $GLOBALS['recipe_id'] ) ? (int) $GLOBALS['recipe_id'] : get_the_ID();
	$template  = new Template( '1.0.0', 'recipepress-reloaded' );

	echo wp_json_encode( $template->get_the_rpr_recipe_schema( $recipe_id ) );
}


/**
 * Adds the schema data to a script tag to be inserted in the HTML <head> tag via the `wp_head` action.
 */
function rpr_add_json_to_head() {
        if ( Options::get_option( 'rpr_integrate_wpseo_metadata' ) ) {
            return null;
        }
    ?>
	    <script id="rpr-recipe-schema" type="application/ld+json"><?php recipe_schema(); ?></script>
	<?php
}
