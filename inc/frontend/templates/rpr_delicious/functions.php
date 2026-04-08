<?php

defined( 'ABSPATH' ) || exit;
/**
 * Registers the stylesheet for the delicious template.
 *
 * @package Recipepress
 *
 * @uses wp_enqueue_style
 * @return void
 */

use const Recipepress\PLUGIN_VERSION;
use Recipepress\Inc\Frontend\Template;
use Recipepress\Inc\Core\Options;

// WP Customizer hooks
add_action( 'customize_register', 'register_customizer' );
add_action( 'customize_preview_init', 'customizer_script' );
add_action( 'wp_head', 'customizer_styles' );

// Enqueue our template specific script and style.
add_action( 'wp_enqueue_scripts', 'rpr_delicious_styles' );
add_action( 'wp_enqueue_scripts', 'rpr_delicious_scripts' );

// If we're on a single recipe post, add the JSON-LD data to the HTML <head> tag.
// Otherwise, it will be added to the <body> via the 'recipe.php' file.
add_action( 'wp_head', 'rpr_add_json_to_head' );

/**
 * Loads a CSS file attached to this template, for convenience.
 */
function rpr_delicious_styles() {

	wp_register_style( 'rpr-delicious-template-style', plugin_dir_url( __FILE__ ) . 'assets/rpr-delicious.css', array(), PLUGIN_VERSION, 'all' );

	if ( Options::get_option( 'rpr_recipe_template_print_btn' ) ) {
		wp_register_style( 'rpr-delicious-print-style', plugin_dir_url( __FILE__ ) . 'assets/rpr-delicious-print.css', array(), PLUGIN_VERSION, 'print' );
	}
}


/**
 * Loads a JS file attached to this template, for convenience.
 */
function rpr_delicious_scripts() {
	wp_register_script( 'rpr-delicious-template-script', plugin_dir_url( __FILE__ ) . 'assets/rpr-delicious.js', array( 'jquery' ), PLUGIN_VERSION, true );
	wp_localize_script(
		'rpr-delicious-template-script',
		'print_options',
		array(
			'print_area'    => Options::get_option( 'rpr_recipe_template_print_area' ),
			'no_print_area' => Options::get_option( 'rpr_recipe_template_no_print_area' ),
			'print_css'     => plugin_dir_url( __FILE__ ) . 'assets/rpr-delicious-print.css',
		)
	);
}

/**
 * Loads a JS file to handle live updating via the WP customizer.
 */
function customizer_script() {
	wp_enqueue_script(
		'rpr-delicious-customizer', plugin_dir_url( __FILE__ ) . 'assets/rpr-delicious-customizer.js',	array( 'jquery', 'customize-preview' ),	PLUGIN_VERSION, true );
}

/**
 * Get our schema data from the Template class and echo it for convenience.
 */
function recipe_schema() {

	if ( ! is_singular( 'rpr_recipe' ) ) {
		return null;
	}

	$recipe_id = ! empty( $GLOBALS['recipe_id'] ) ? (int) $GLOBALS['recipe_id'] : get_the_ID();
	echo wp_json_encode( ( new Template( '1.0.0', 'recipepress-reloaded' ) )->get_the_rpr_recipe_schema( $recipe_id ) );
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
<?php }

function customizer_styles() {
	$options = get_option( 'rpr_template', array() );
	?>
	<style type="text/css">
		.rpr.recipe-container { border-color: <?php echo ! empty( $options['delicious']['color_1'] ) ? $options['delicious']['color_1'] : ''; ?>; }
		.rpr.thumbnail-container { border-color: <?php echo ! empty( $options['delicious']['color_1'] ) ? $options['delicious']['color_1'] : ''; ?>; }
		.rpr.ig-share-container { background-color: <?php echo ! empty( $options['delicious']['color_1'] ) ? $options['delicious']['color_1'] : ''; ?>; }
		.rpr.meta-container { background-color: <?php echo ! empty( $options['delicious']['color_1'] ) ? $options['delicious']['color_1'] : ''; ?>; }

		.rpr.meta-container { color: <?php echo ! empty( $options['delicious']['color_2'] ) ? $options['delicious']['color_2'] : ''; ?>; }
		.rpr.meta-container a { color: <?php echo ! empty( $options['delicious']['color_2'] ) ? $options['delicious']['color_2'] : ''; ?>; }
		.rpr.ig-share-container { color: <?php echo ! empty( $options['delicious']['color_2'] ) ? $options['delicious']['color_2'] : ''; ?>; }
		.rpr.ig-share-container a { color: <?php echo ! empty( $options['delicious']['color_2'] ) ? $options['delicious']['color_2'] : ''; ?>; }
		.rpr.ig-share-container .rpr-icon svg { fill: <?php echo ! empty( $options['delicious']['color_2'] ) ? $options['delicious']['color_2'] : ''; ?>; }
	</style>
<?php }

/**
 * @param WP_Customize_Manager $wp_customizer
 */
function register_customizer( $wp_customizer ) {
	$wp_customizer->add_section(
		'rpr_delicious_template_customizer_section',
		array(
			'title'           => __( 'Recipe Template', 'recipepress-reloaded' ),
			'priority'        => 9999,
			'active_callback' => static function() {return is_singular( 'rpr_recipe' ); },
		)
	);

	$wp_customizer->add_setting(
		'rpr_template[delicious][color_1]', array(
			'default'           => '#f2f2f2',
			'sanitize_callback' => 'sanitize_hex_color',
			'type'              => 'option',
			'transport'         => 'postMessage',
			'capability'        => 'manage_options',
		)
	);
	$wp_customizer->add_setting(
		'rpr_template[delicious][color_2]', array(
			'default'           => '#0a0a0a',
			'sanitize_callback' => 'sanitize_hex_color',
			'type'              => 'option',
			'transport'         => 'postMessage',
			'capability'        => 'manage_options',
		)
	);
	$wp_customizer->add_setting(
		'rpr_template[delicious][excerpt]', array(
			'default'           => '1',
			'type'              => 'option',
			'transport'         => 'refresh',
			'capability'        => 'manage_options',
		)
	);
	$wp_customizer->add_setting(
		'rpr_template[delicious][share_enable]', array(
			'default'           => '1',
			'type'              => 'option',
			'transport'         => 'refresh',
			'capability'        => 'manage_options',
		)
	);
	$wp_customizer->add_setting(
		'rpr_template[delicious][share_heading]', array(
			'default'           => __( 'Did You Make This Recipe?', 'recipepress-reloaded' ),
			'sanitize_callback' => 'sanitize_text_field',
			'type'              => 'option',
			'transport'         => 'postMessage',
			'capability'        => 'manage_options',
		)
	);
	$wp_customizer->add_setting(
		'rpr_template[delicious][share_body]', array(
			'default'           => __( 'Tag us on Instagram with a photo your recipe and how it turned out', 'recipepress-reloaded' ),
			'sanitize_callback' => 'wp_kses_post',
			'type'              => 'option',
			'transport'         => 'postMessage',
			'capability'        => 'manage_options',
		)
	);

	$wp_customizer->add_control(
		new WP_Customize_Color_Control(
			$wp_customizer,
			'rpr-template-color-1',
			array(
				'label'    => __( 'Accent Color 1', 'recipepress-reloaded' ),
				'section'  => 'rpr_delicious_template_customizer_section',
				'settings' => 'rpr_template[delicious][color_1]',
			)
		)
	);
	$wp_customizer->add_control(
		new WP_Customize_Color_Control(
			$wp_customizer,
			'rpr-template-color-2',
			array(
				'label'    => __( 'Accent Color 2', 'recipepress-reloaded' ),
				'section'  => 'rpr_delicious_template_customizer_section',
				'settings' => 'rpr_template[delicious][color_2]',
			)
		)
	);
	$wp_customizer->add_control(
		new WP_Customize_Control(
			$wp_customizer,
			'rpr-template-excerpt',
			array(
				'type'     => 'checkbox',
				'label'    => __( 'Display excerpt', 'recipepress-reloaded' ),
				'section'  => 'rpr_delicious_template_customizer_section',
				'settings' => 'rpr_template[delicious][excerpt]',
			)
		)
	);
	$wp_customizer->add_control(
		new WP_Customize_Control(
			$wp_customizer,
			'rpr-template-share',
			array(
				'type'     => 'checkbox',
				'label'    => __( 'Share message', 'recipepress-reloaded' ),
				'section'  => 'rpr_delicious_template_customizer_section',
				'settings' => 'rpr_template[delicious][share_enable]',
			)
		)
	);
	$wp_customizer->add_control(
		new WP_Customize_Control(
			$wp_customizer,
			'rpr-template-heading',
			array(
				'type'     => 'text',
				'label'    => __( 'Heading', 'recipepress-reloaded' ),
				'section'  => 'rpr_delicious_template_customizer_section',
				'settings' => 'rpr_template[delicious][share_heading]',
			)
		)
	);
	$wp_customizer->add_control(
		new WP_Customize_Control(
			$wp_customizer,
			'rpr-template-body',
			array(
				'type'     => 'textarea',
				'label'    => __( 'Message', 'recipepress-reloaded' ),
				'section'  => 'rpr_delicious_template_customizer_section',
				'settings' => 'rpr_template[delicious][share_body]',
			)
		)
	);

}
