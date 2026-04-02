<?php
/**
 * Registers the stylesheet for the default template.
 *
 * @uses wp_enqueue_style
 * @return void
 */

use const Recipepress\PLUGIN_VERSION;
use Recipepress\Inc\Frontend\Template;
use Recipepress\Inc\Core\Options;

add_action( 'wp_enqueue_scripts', 'rpr_2column_styles' );
add_action( 'wp_enqueue_scripts', 'rpr_2column_scripts' );

add_action( 'wp_head', 'rpr_add_json_to_head' );

/**
 * Loads a CSS file attached to this template, for convenience.
 */
function rpr_2column_styles() {

	wp_enqueue_style( 'rpr-2column-template-style', plugin_dir_url( __FILE__ ) . 'assets/rpr-2column.css', array(), PLUGIN_VERSION, 'all' );

	if ( Options::get_option( 'rpr_recipe_template_print_btn' ) ) {
		wp_enqueue_style( 'rpr-2column-print-style', plugin_dir_url( __FILE__ ) . 'assets/print.css', array(), PLUGIN_VERSION, 'print' );
	}
}

/**
 * Loads a JS file attached to this template, for convenience.
 */
function rpr_2column_scripts() {

	wp_enqueue_script( 'rpr-2column-template-script', plugin_dir_url( __FILE__ ) . 'assets/rpr-2column.js', array( 'jquery' ), PLUGIN_VERSION, true );

	if ( Options::get_option( 'rpr_recipe_template_print_btn' ) ) {
		wp_localize_script(
			'rpr-2column-template-script',
			'print_options',
			array(
				'print_area'    => Options::get_option( 'rpr_recipe_template_print_area' ),
				'no_print_area' => Options::get_option( 'rpr_recipe_template_no_print_area' ),
				'print_css'     => plugin_dir_url( __FILE__ ) . 'assets/print.css',
			)
		);
	}
}


/**
 * Get our schema data from the Template class and echo it for convenience.
 */
function recipe_schema() {

	if ( ! is_singular( 'rpr_recipe' ) ) {
		return null;
	}

	$recipe_id = ( isset( $GLOBALS['recipe_id'] ) && '' !== $GLOBALS['recipe_id'] ) ? $GLOBALS['recipe_id'] : get_the_ID();
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
	    <script  id="rpr-recipe-schema" type="application/ld+json"><?php recipe_schema(); ?></script>
	<?php
}
