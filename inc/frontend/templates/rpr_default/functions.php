<?php
/**
 * Registers the stylesheet for the default template.
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
add_action( 'wp_enqueue_scripts', 'rpr_default_styles' );
add_action( 'wp_enqueue_scripts', 'rpr_default_scripts' );

// If we're on a single recipe post, add the JSON-LD data to the HTML <head> tag.
// Otherwise, it will be added to the <body> via the 'recipe.php' file.
add_action( 'wp_head', 'rpr_add_json_to_head' );

/**
 * Loads a CSS file attached to this template, for convenience.
 */
function rpr_default_styles() {

	wp_register_style( 'rpr-default-template-style', plugin_dir_url( __FILE__ ) . 'assets/rpr-default.css', array(), PLUGIN_VERSION, 'all' );

	if ( Options::get_option( 'rpr_recipe_template_print_btn' ) ) {
		wp_register_style( 'rpr-default-print-style', plugin_dir_url( __FILE__ ) . 'assets/print.css', array(), PLUGIN_VERSION, 'print' );
	}
}


/**
 * Loads a JS file attached to this template, for convenience.
 */
function rpr_default_scripts() {

	//wp_register_script( 'rpr-default-template-script', plugin_dir_url( __FILE__ ) . 'assets/rpr-default.js', array( 'jquery' ), PLUGIN_VERSION, true );
	wp_localize_script(
		'recipepress-reloaded',
		'print_options',
		array(
			'print_area'    => Options::get_option( 'rpr_recipe_template_print_area' ),
			'no_print_area' => Options::get_option( 'rpr_recipe_template_no_print_area' ),
			'print_css'     => plugin_dir_url( __FILE__ ) . 'assets/print.css',
		)
	);

	$script = "jQuery('.rpr-jump-to-recipe').on('click', function (e) {
                e.preventDefault();
                jQuery('html, body').stop().animate({scrollTop: jQuery('#ingredients').offset().top}, 1000, 'swing');
            });";
	$script .= "jQuery('a.rpr-print-recipe, .rpr-print-button').on('click', function(e) {
             e.preventDefault();

             const printClass = '.' + jQuery(this).data('print-area');

             jQuery(printClass).print({
                 globalStyles: false,
                 noPrintSelector: print_options.no_print_area,
                 stylesheet: print_options.print_css,
             });
             return false;
         });";

    wp_add_inline_script( 'recipepress-reloaded', $script );
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
	    <script id="rpr-recipe-schema" type="application/ld+json"><?php recipe_schema(); ?></script>
	<?php
}

function customizer_styles() {
	$options = get_option( 'rpr_template', array() );
	?>
	<style>
		.rpr-terms-container ul.rpr-term-list,
		.rpr-times-container ul.rpr-times,
		.rpr-nutrition-container ul.rpr-nutrition { border-color: <?php echo ! empty( $options['default']['color_1'] ) ? $options['default']['color_1'] : '#0a0a0a'; ?>; }
		.rpr-instruction-list .rpr-instruction::before { background-color: <?php echo ! empty( $options['default']['color_1'] ) ? $options['default']['color_1'] : '#0a0a0a'; ?>; }
		ul.rpr-ingredient-list li::marker { color: <?php echo ! empty( $options['default']['color_1'] ) ? $options['default']['color_1'] : '#0a0a0a'; ?>; }
        ul.rpr-equipment__list li::marker { color: <?php echo ! empty( $options['default']['color_1'] ) ? $options['default']['color_1'] : '#0a0a0a'; ?>; }
	</style>
<?php }

/**
 * Loads a JS file to handle live updating via the WP customizer.
 */
function customizer_script() {
	wp_enqueue_script(
		'rpr-default-customizer', plugin_dir_url( __FILE__ ) . 'assets/rpr-default-customizer.js',	array( 'jquery', 'customize-preview' ),	PLUGIN_VERSION, true
	);
}

/**
 * @param WP_Customize_Manager $wp_customizer
 */
function register_customizer( $wp_customizer ) {
	$wp_customizer->add_section(
		'rpr_default_template_customizer_section',
		array(
			'title'           => __( 'Recipe Template', 'recipepress-reloaded' ),
			'priority'        => 9999,
			// 'active_callback' => static function() {return is_singular( 'rpr_recipe' ); },
		)
	);

	$wp_customizer->add_setting(
		'rpr_template[default][color_1]', array(
			'default'           => '#000',
			'sanitize_callback' => 'sanitize_hex_color',
			'type'              => 'option',
			'transport'         => 'postMessage',
			'capability'        => 'manage_options',
		)
	);
	$wp_customizer->add_setting(
		'rpr_template[default][jump]', array(
			'default'           => '1',
			'type'              => 'option',
			'transport'         => 'refresh',
			'capability'        => 'manage_options',
		)
	);
	$wp_customizer->add_setting(
		'rpr_template[default][print]', array(
			'default'           => '1',
			'type'              => 'option',
			'transport'         => 'refresh',
			'capability'        => 'manage_options',
		)
	);
	$wp_customizer->add_control(
		new WP_Customize_Color_Control(
			$wp_customizer,
			'rpr-template-color-1',
			array(
				'label'    => __( 'Accent Color', 'recipepress-reloaded' ),
				'section'  => 'rpr_default_template_customizer_section',
				'settings' => 'rpr_template[default][color_1]',
			)
		)
	);
	$wp_customizer->add_control(
		new WP_Customize_Control(
			$wp_customizer,
			'rpr-template-jump',
			array(
				'type'     => 'checkbox',
				'label'    => __( 'Jump to Recipe button', 'recipepress-reloaded' ),
				'section'  => 'rpr_default_template_customizer_section',
				'settings' => 'rpr_template[default][jump]',
			)
		)
	);
	$wp_customizer->add_control(
		new WP_Customize_Control(
			$wp_customizer,
			'rpr-template-print',
			array(
				'type'     => 'checkbox',
				'label'    => __( 'Print Recipe button', 'recipepress-reloaded' ),
				'section'  => 'rpr_default_template_customizer_section',
				'settings' => 'rpr_template[default][print]',
			)
		)
	);
}
