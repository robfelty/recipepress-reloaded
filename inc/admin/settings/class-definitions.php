<?php

namespace Recipepress\Inc\Admin\Settings;

use Recipepress as NS;
use Recipepress\Inc\Common\Utilities\Layouts;
use Recipepress\Inc\Core\Options;
use Recipepress\Inc\Libraries\Pluralizer\Pluralizer;

/**
 *
 * @link       https://wzymedia.com
 * @since      1.0.0
 *
 * @package    Recipepress
 */

/**
 * The Settings definition of the plugin.
 *
 * @since 1.0.0
 *
 * @package    Recipepress
 *
 * @author     Kemory Grubb <kemory@wzymedia.com>
 */
class Definitions {

	use NS\Inc\Common\Traits\Utilities;

	/**
	 * [apply_tab_slug_filters description]
	 *
	 * @param array $default_settings The default settings of the plugin.
	 *
	 * @return array
	 */
	public static function apply_tab_slug_filters( $default_settings ) {

		$extended_settings[] = array();
		$extended_tabs       = self::get_tabs();

		foreach ( $extended_tabs as $tab_slug => $tab_desc ) {

			$options = isset( $default_settings[ $tab_slug ] ) ? $default_settings[ $tab_slug ] : array();

			$extended_settings[ $tab_slug ] = apply_filters( 'recipepress_settings_' . $tab_slug, $options );
		}

		return $extended_settings;
	}

	/**
	 * Get the default tab slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function get_default_tab_slug() {

		return key( self::get_tabs() );
	}

	/**
	 * Retrieve settings tabs
	 *
	 * @since    1.0.0
	 * @return    array    $tabs    Settings tabs
	 */
	public static function get_tabs() {

		$tabs                   = array();
		$tabs['general_tab']    = __( 'General', 'recipepress-reloaded' );
		$tabs['ingredient_tab'] = __( 'Ingredients', 'recipepress-reloaded' );
		$tabs['taxonomy_tab']   = __( 'Taxonomies', 'recipepress-reloaded' );
		$tabs['metadata_tab']   = __( 'Metadata', 'recipepress-reloaded' );
		$tabs['units_tab']      = __( 'Units', 'recipepress-reloaded' );
		$tabs['components_tab'] = __( 'Components', 'recipepress-reloaded' );
		$tabs['appearance_tab'] = __( 'Appearance', 'recipepress-reloaded' );
		$tabs['advanced_tab']   = __( 'Advanced', 'recipepress-reloaded' );

		return apply_filters( 'recipepress_settings_tabs', $tabs );
	}

	/**
	 * 'Whitelisted' Plugin_Name settings, filters are provided for each settings
	 * section to allow extensions and other plugins to add their own settings
	 *
	 * @since 1.0.0
	 *
	 * @return mixed    $value    Value saved / $default if key if not exist
	 * @throws \Exception
	 */
	public static function get_settings() {

		$layouts    = new Layouts();
		$time_ago   = ( new self )->time_ago( Options::get_option( 'rpr_last_youtube_ping', '' ) );

		$settings = array(
			// General tab.
			'general_tab'    => array(
				'general_tab_header_0'    => array(
					'name' => '<strong>' . __( 'General Settings', 'recipepress-reloaded' ) . '</strong>',
					'type' => 'header',
				),
				'spacer_0'                => array(
					'name' => '',
					'type' => 'spacer',
				),
				'rpr_recipe_labels'         => array(
					'name'          => __( 'Labels', 'recipepress-reloaded' ),
					'singular_std'  => __( 'Recipe', 'recipepress-reloaded' ),
					'plural_std'    => __( 'Recipes', 'recipepress-reloaded' ),
					'singular_desc' => __( 'The singular form of the label', 'recipepress-reloaded' ),
					'plural_desc'   => __( 'The plural form of the label', 'recipepress-reloaded' ),
					'type'          => 'labels',
					'size' => '25',
				),
				'rpr_slug_instructions'   => array(
					'name' => __( '404s errors', 'recipepress-reloaded' ),
					'desc' => __( 'Please note. If you have set up everything correctly here but now WordPress is giving you an 404 (not found) error,
					 try flushing your permalink settings. Visit "Settings" -> "Permalinks" and click the "Save Changes" button without changing anything.', 'recipepress-reloaded' ),
					'type' => 'instruction',
				),
				'rpr_recipes_on_homepage' => array(
					'name' => __( 'Recipes on homepage', 'recipepress-reloaded' ),
					'desc' => __( 'Defines if recipes should be displayed on the homepage like "normal" posts.', 'recipepress-reloaded' ),
					'type' => 'checkbox',
				),
				'rpr_recipes_archive'     => array(
					'name'    => __( 'Archive page', 'recipepress-reloaded' ),
					'desc'    => __( 'Defines what to show of your recipes on the archive page.', 'recipepress-reloaded' ),
					'options' => array(
						'archive_display_full'    => __( 'The entire recipe', 'recipepress-reloaded' ),
						'archive_display_excerpt' => __( 'Only the excerpt', 'recipepress-reloaded' ),
					),
					'type'    => 'select',
				),
				'rpr_recipes_in_rss' => array(
					'name' => __( 'Recipes in RSS feed', 'recipepress-reloaded' ),
					'desc' => __( 'Should recipes be included in your RSS feed?', 'recipepress-reloaded' ),
					'type' => 'checkbox',
				),
				'rpr_recipes_in_gutenberg' => array(
					'name' => __( 'Use Gutenberg', 'recipepress-reloaded' ),
					'desc' => __( 'Enable to use the new Gutenberg editor. The "ClassicPress" plugin is needed for this option.', 'recipepress-reloaded' ),
					'type' => 'checkbox',
				),
				'spacer_1'                => array(
					'name' => '',
					'type' => 'spacer',
				),
			),

			// Ingredients tab.
			'ingredient_tab' => array(
				'ingredient_tab_header_0'      => array(
					'name' => '<strong>' . __( 'Ingredients', 'recipepress-reloaded' ) . '</strong>',
					'type' => 'header',
				),
				'spacer_0'                     => array(
					'name' => '',
					'type' => 'spacer',
				),
				'rpr_ingredient_labels'         => array(
					'name'          => __( 'Labels', 'recipepress-reloaded' ),
					'singular_std'  => __( 'Ingredient', 'recipepress-reloaded' ),
					'plural_std'    => __( 'Ingredients', 'recipepress-reloaded' ),
					'singular_desc' => __( 'The singular form of the label', 'recipepress-reloaded' ),
					'plural_desc'   => __( 'The plural form of the label', 'recipepress-reloaded' ),
					'type'          => 'labels',
					'size' => '20',
				),
				'rpr_ingredient_links'         => array(
					'name'    => __( 'Link target', 'recipepress-reloaded' ),
					'desc'    => __( 'Target of links in the ingredient list.', 'recipepress-reloaded' ),
					'options' => array(
						'0' => __( 'No ingredient links', 'recipepress-reloaded' ),
						'1' => __( 'Only link to ingredient archive page', 'recipepress-reloaded' ),
						'2' => __( 'Custom link if provided, otherwise archive page', 'recipepress-reloaded' ),
						'3' => __( 'Custom links if provided, otherwise no link', 'recipepress-reloaded' ),
					),
					'type'    => 'select',
				),
				'rpr_ingredient_separator'     => array(
					'name'    => __( 'Ingredient separator', 'recipepress-reloaded' ),
					'desc'    => __( 'Decide how to display remarks or comments on your ingredients.', 'recipepress-reloaded' ),
					'options' => array(
						'0' => __( 'None: 1 egg preferably free-range or organic', 'recipepress-reloaded' ),
						'1' => __( 'Brackets: 1 egg (preferably free-range or organic)', 'recipepress-reloaded' ),
						'2' => __( 'Comma: 1 egg, preferably free-range or organic', 'recipepress-reloaded' ),
					),
					'type'    => 'select',
				),
				'rpr_ingredient_pluralization' => array(
					'name' => __( 'Automatic pluralization', 'recipepress-reloaded' ),
					'desc' => __( 'Automatically create ingredient plurals if more than one is used. If active entering "2 onion" will be rendered as "2 onions
This only can handle regular plurals. For irregular plurals please enter the correct plural on the ingredients page.', 'recipepress-reloaded' ),
					'type' => 'checkbox',
				),
				'spacer_1'                     => array(
					'name' => '',
					'type' => 'spacer',
				),
			),

			// Taxonomies tab.
			'taxonomy_tab'   => array(
				'taxonomy_tab_header_0'  => array(
					'name' => '<strong>' . __( 'Categories and Tags', 'recipepress-reloaded' ) . '</strong>',
					'type' => 'header',
				),
				'spacer_0'               => array(
					'name' => '',
					'type' => 'spacer',
				),
				'rpr_enable_categories'  => array(
					'name' => __( 'Use WP Categories', 'recipepress-reloaded' ),
					'desc' => __( 'Categories are a built-in taxonomy of WordPress core. You can use them to organize your recipes as well.', 'recipepress-reloaded' ),
					'type' => 'checkbox',
				),
				'rpr_show_categories'  => array(
					'name' => __( 'Show Categories', 'recipepress-reloaded' ),
					'desc' => __( 'Do you want to show categories on the frontend of your recipes?', 'recipepress-reloaded' ),
					'type' => 'checkbox',
				),
				'rpr_enable_tags'        => array(
					'name' => __( 'Use WP Tags', 'recipepress-reloaded' ),
					'desc' => __( 'Tags are a built-in taxonomy of WordPress core. You can use them to organize your recipes as well.', 'recipepress-reloaded' ),
					'type' => 'checkbox',
				),
				'rpr_show_tags'        => array(
					'name' => __( 'Show Tags', 'recipepress-reloaded' ),
					'desc' => __( 'Do you want to show tags on the frontend of your recipes?', 'recipepress-reloaded' ),
					'type' => 'checkbox',
				),
				'spacer_1'               => array(
					'name' => '',
					'type' => 'spacer',
				),
				'taxonomy_tab_header_1'  => array(
					'name' => '<strong>' . __( 'Recipe Taxonomies', 'recipepress-reloaded' ) . '</strong>',
					'type' => 'header',
				),
				'spacer_2'               => array(
					'name' => '',
					'type' => 'spacer',
				),
				'rpr_taxonomy_selection' => array(
					'name' => __( 'Taxonomy Selection', 'recipepress-reloaded' ),
					'desc' => __( 'Create and delete your recipe taxonomies here.', 'recipepress-reloaded' ),
					'std'  => 'Course,Cuisine,Season,Difficulty',
					'type' => 'text',
					'size' => '75'
				),
				'spacer_3'               => array(
					'name' => '',
					'type' => 'spacer',
				),
			),

			// Metadata tab.
			'metadata_tab'   => array(
				'metadata_tab_header_0'    => array(
					'name' => '<strong>' . __( 'Recipe Metadata', 'recipepress-reloaded' ) . '</strong>',
					'type' => 'header',
				),
				'spacer_0'                 => array(
					'name' => '',
					'type' => 'spacer',
				),
				'rpr_use_source_meta'      => array(
					'name' => __( 'Use source field', 'recipepress-reloaded' ),
					'desc' => __( 'Check this to enable the use of a source field allowing you to enter restaurants, books or websites as source of your recipe.', 'recipepress-reloaded' ),
					'type' => 'checkbox',
				),
				'rpr_use_nutritional_meta' => array(
					'name' => __( 'Use nutrition field', 'recipepress-reloaded' ),
					'desc' => __( 'Check this to enable the use of nutritional information like calorific value, protein, fat and carbohydrates.', 'recipepress-reloaded' ),
					'type' => 'checkbox',
				),
				'rpr_additional_nutrition' => array(
					'name' => __( 'Additional Nutrition', 'recipepress-reloaded' ),
					'desc' => __( 'Add additional nutritional information to your recipe', 'recipepress-reloaded' ),
					'std' => '',
					'type' => 'text',
					'size' => '75'
				),
				'rpr_use_video_meta' => array(
					'name' => __( 'Use video field', 'recipepress-reloaded' ),
					'desc' => __( 'Check this to enable the video metadata in your recipe\'s JSON-LD schema', 'recipepress-reloaded' ),
					'type' => 'checkbox',
				),
				'spacer_1'                 => array(
					'name' => '',
					'type' => 'spacer',
				),
				'metadata_tab_header_1'    => array(
					'name' => '<strong>' . __( 'Recipe Diet', 'recipepress-reloaded' ) . '</strong>',
					'type' => 'header',
				),
				'rpr_diet_selection' => array(
					'name' => __( 'Diet Restriction', 'recipepress-reloaded' ),
					'desc' => __( 'A diet restricted to certain foods or preparations for cultural, religious, health or lifestyle reasons.', 'recipepress-reloaded' ),
					'std' => '',
					'type' => 'text',
					'size' => '75'
				),
			),

			// Units tab.
			'units_tab'      => array(
				'units_tab_header_0'           => array(
					'name' => '<strong>' . __( 'Recipe Ingredient Units', 'recipepress-reloaded' ) . '</strong>',
					'type' => 'header',
				),
				'spacer_0'                     => array(
					'name' => '',
					'type' => 'spacer',
				),
				'rpr_use_ingredient_unit_list' => array(
					'name' => __( 'Use ingredient unit list', 'recipepress-reloaded' ),
					'desc' => __( 'Check this to use a list of units for entering ingredients. You can define the list below. We recommend using a well defined list of units as this will make your recipes more consistent and readable.', 'recipepress-reloaded' ),
					'type' => 'checkbox',
				),
				'rpr_ingredient_unit_list'     => array(
					'name' => __( 'Ingredient Units List', 'recipepress-reloaded' ),
					'desc' => __( 'Unit list for ingredients. We recommend using a well defined list of units as this will make your recipes more consistent and readable.', 'recipepress-reloaded' ),
					'std'  => 'teaspoon,tablespoon,fluid ounce,gill,cup,pint,quart,gallon,milliliter,liter,deciliter,pound,ounce,milligram,gram,kilogram,inch,centimeter',
					'type' => 'text',
					'size' => '75'
				),
				'spacer_1'                     => array(
					'name' => '',
					'type' => 'spacer',
				),
				'rpr_use_serving_unit_list'    => array(
					'name' => __( 'Use serving size unit list', 'recipepress-reloaded' ),
					'desc' => __( 'Check this to use a list of units for entering serving sizes. You can define the list below. We recommend using a well defined list of units as this will make your recipes more consistent and readable.', 'recipepress-reloaded' ),
					'type' => 'checkbox',
				),
				'rpr_serving_unit_list'        => array(
					'name' => __( 'Serving Size Units List', 'recipepress-reloaded' ),
					'desc' => __( 'Unit list for serving sizes. We recommend using a well defined list of units as this will make your recipes more consistent and readable.', 'recipepress-reloaded' ),
					'std'  => 'loaf,loaves,serving,pieces',
					'type' => 'text',
					'size' => '75'
				),
				'spacer_2'                     => array(
					'name' => '',
					'type' => 'spacer',
				),
			),

			// Components tab.
			'components_tab'      => array(
				'components_tab_header_0'           => array(
					'name' => '<strong>' . __( 'Reader Comment Rating', 'recipepress-reloaded' ) . '</strong>',
					'type' => 'header',
				),
				'spacer_0'                     => array(
					'name' => '',
					'type' => 'spacer',
				),
				'rpr_comment_rating'    => array(
					'name' => __( 'Comment Ratings', 'recipepress-reloaded' ),
					'desc' => __( 'Enable reader submitted ratings in the comment form', 'recipepress-reloaded' ),
					'type' => 'checkbox',
				),
				'rpr_comment_rating_label'        => array(
					'name' => __( 'Rating Label', 'recipepress-reloaded' ),
					'desc' => __( 'Enter the label to show before the comment 5 star rating field', 'recipepress-reloaded' ),
					'std'  => 'Rate this recipe',
					'type' => 'text',
				),
				'rpr_comment_rating_color'        => array(
					'name' => __( 'Rating Star Color', 'recipepress-reloaded' ),
					'desc' => __( 'Background color for the reader comment 5 star rating', 'recipepress-reloaded' ),
					'std'  => 'rgba(255, 235, 59, 1)',
					'type' => 'color',
				),
				'spacer_1'                     => array(
					'name' => '',
					'type' => 'spacer',
				),
            /*'rpr_recipe_rating'    => array(
                    'name' => __( 'Recipe Rating', 'recipepress-reloaded' ),
                    'desc' => __( 'Display the average recipe rating in the recipe content area', 'recipepress-reloaded' ),
                    'type' => 'checkbox',
                ),
                'rpr_recipe_rating_location'     => array(
                    'name'    => __( 'Display Location', 'recipepress-reloaded' ),
                    'desc'    => __( 'Choose where to display the recipe rating stars', 'recipepress-reloaded' ),
                    'options' => array(
                        '0' => __( 'Before the recipe title', 'recipepress-reloaded' ),
                        '1' => __( 'After the recipe title', 'recipepress-reloaded' ),
                        '2' => __( 'Before the recipe description', 'recipepress-reloaded' ),
                        '3' => __( 'After the recipe description', 'recipepress-reloaded' ),
                    ),
                    'type'    => 'select',
                ),*/
				'components_tab_header_1'           => array(
					'name' => '<strong>' . __( 'Recipe Equipment', 'recipepress-reloaded' ) . '</strong>',
					'type' => 'header',
				),
				'spacer_2'                     => array(
					'name' => '',
					'type' => 'spacer',
				),
				'rpr_recipe_equipment'    => array(
					'name' => __( 'Enable Equipment', 'recipepress-reloaded' ),
					'desc' => __( 'Enable the recipe equipment feature', 'recipepress-reloaded' ),
					'type' => 'checkbox',
				),
				'spacer_3'                     => array(
					'name' => '',
					'type' => 'spacer',
				),
				'components_tab_header_2'           => array(
					'name' => '<strong>' . __( 'Recipe Widgets', 'recipepress-reloaded' ) . '</strong>',
					'type' => 'header',
				),
				'spacer_4'                     => array(
					'name' => '',
					'type' => 'spacer',
				),
				'rpr_tag_cloud_widget'    => array(
					'name' => __( 'Recipe Tag Cloud', 'recipepress-reloaded' ),
					'desc' => __( 'Enable the recipe tag cloud widget', 'recipepress-reloaded' ),
					'type' => 'checkbox',
				),
				'rpr_taxonomy_list_widget'    => array(
					'name' => __( 'Recipe Taxonomy List', 'recipepress-reloaded' ),
					'desc' => __( 'Enable the recipe taxonomy list widget', 'recipepress-reloaded' ),
					'type' => 'checkbox',
				),
				'rpr_recipe_calendar_widget'    => array(
					'name' => __( 'Recipe Calendar', 'recipepress-reloaded' ),
					'desc' => __( 'Enable the recipe calendar widget', 'recipepress-reloaded' ),
					'type' => 'checkbox',
				),
				'rpr_recipe_profile_widget'    => array(
					'name' => __( 'Recipe Author Profile', 'recipepress-reloaded' ),
					'desc' => __( 'Enable the recipe author profile widget', 'recipepress-reloaded' ),
					'type' => 'checkbox',
				),
				'rpr_recipe_archive_filter_widget'    => array(
					'name' => __( 'Recipe Archive Filter', 'recipepress-reloaded' ),
					'desc' => __( 'Enable the recipe archive page filter widget', 'recipepress-reloaded' ),
					'type' => 'checkbox',
				),
			),

			// Appearance tab.
			'appearance_tab' => array(
				'appearance_tab_header_0' => array(
					'name' => '<strong>' . __( 'Recipe Templates', 'recipepress-reloaded' ) . '</strong>',
					'type' => 'header',
				),
				'spacer_0'                => array(
					'name' => '',
					'type' => 'spacer',
				),
				'rpr_recipe_template'     => array(
					'name'    => __( 'Recipe template', 'recipepress-reloaded' ),
					'desc'    => __( 'Select how you want your recipes to look.', 'recipepress-reloaded' ),
					'options' => $layouts->layout_list(),
					'type'    => 'template',
				),
				'rpr_recipe_template_click_img'    => array(
					'name' => __( 'Clickable Images', 'recipepress-reloaded' ),
					'desc' => __( 'Best used in combination with a Lightbox plugin.', 'recipepress-reloaded' ),
					'type' => 'checkbox',
				),
				'rpr_recipe_template_inst_image'     => array(
					'name'    => __( 'Position of instruction images', 'recipepress-reloaded' ),
					'desc'    => __( 'Decide whether your instruction images should be display next to the instructions or below.', 'recipepress-reloaded' ),
					'options' => array(
						'right' => __( 'Right of instruction', 'recipepress-reloaded' ),
						'below' => __( 'Below the instruction', 'recipepress-reloaded' ),
						'hide'  => __( 'Hide the image', 'recipepress-reloaded' ),
					),
					'type'    => 'select',
				),
				'rpr_recipe_template_use_icons'    => array(
					'name' => __( 'Use icons', 'recipepress-reloaded' ),
					'desc' => __( 'Icons not only look nice. They also can save you space. With this setting activated this layout will display "35min" instead of "READY IN: 35min".', 'recipepress-reloaded' ),
					'type' => 'checkbox',
				),
				'rpr_recipe_template_print_area'     => array(
					'name' => __( 'Print area class', 'recipepress-reloaded' ),
					'desc' => __( 'Print links should only print an area of the page, usually a post. This is highly dependent on the WordPress theme you are using. Add here the class (prefixed by \'.\') or the id (prefixed by \'#\') of the printable area.', 'recipepress-reloaded' ),
					'std'  => '.rpr_recipe',
					'size'  => '50',
					'type' => 'text',
				),
				'rpr_recipe_template_no_print_area'     => array(
					'name' => __( 'Do not print area class', 'recipepress-reloaded' ),
					'desc' => __( 'Enter the class or ID of areas or elements that should not be printed. This is highly dependent on the WordPress theme you are using. Add here the class (prefixed by \'.\') or the id (prefixed by \'#\') of the area not to be printed. Separate multiple entries with commas (\',\').', 'recipepress-reloaded' ),
					'std'  => '.no-print',
					'size'  => '50',
					'type' => 'text',
				),
				'spacer_1'                => array(
					'name' => '',
					'type' => 'spacer',
				),
				'appearance_tab_header_1' => array(
					'name' => '<strong>' . __( 'Jump to Recipe', 'recipepress-reloaded' ) . '</strong>',
					'type' => 'header',
				),
				'spacer_2'                => array(
					'name' => '',
					'type' => 'spacer',
				),
				'rpr_recipe_template_recipe_jump'    => array(
					'name' => __( 'Enable recipe jump button', 'recipepress-reloaded' ),
					'desc' => __( 'Enable a "Jump to Recipe" button', 'recipepress-reloaded' ),
					'type' => 'checkbox',
				),
				'rpr_recipe_template_jump_btn_text'     => array(
					'name' => __( 'Button\'s text', 'recipepress-reloaded' ),
					'desc' => __( 'The "Jump to Recipe" button\'s text', 'recipepress-reloaded' ),
					'std'  => 'Jump to Recipe',
					'type' => 'text',
				),
				'spacer_3'                => array(
					'name' => '',
					'type' => 'spacer',
				),
				'appearance_tab_header_2' => array(
					'name' => '<strong>' . __( 'Print Recipe Button', 'recipepress-reloaded' ) . '</strong>',
					'type' => 'header',
				),
				'spacer_4'                => array(
					'name' => '',
					'type' => 'spacer',
				),
				'rpr_recipe_template_print_btn'    => array(
					'name' => __( 'Display print button', 'recipepress-reloaded' ),
					'desc' => __( 'Adds a print link to your recipes. It\'s recommended to use one of the numerous print plugins for wordpress to include a print link to ALL of your posts.', 'recipepress-reloaded' ),
					'type' => 'checkbox',
				),
				'rpr_recipe_template_print_btn_text'     => array(
					'name' => __( 'Button\'s text', 'recipepress-reloaded' ),
					'desc' => __( 'The "Print Recipe" button\'s text', 'recipepress-reloaded' ),
					'std'  => 'Print Recipe',
					'type' => 'text',
				),
				'spacer_5'                => array(
					'name' => '',
					'type' => 'spacer',
				),
				'appearance_tab_header_3' => array(
					'name' => '<strong>' . __( 'Recipe Custom Styling', 'recipepress-reloaded' ) . '</strong>',
					'type' => 'header',
				),
				'rpr_recipe_custom_styling'                   => array(
					'name' => __( 'Custom styles', 'rcno-reviews' ),
					'desc' => __( 'Add your custom CSS here to fine-tune the look of your recipe posts.', 'recipepress-reloaded' ),
					'type' => 'cssbox',
				),
				'spacer_6'                => array(
					'name' => '',
					'type' => 'spacer',
				),
			),

			// Advanced tab
			'advanced_tab' => array(
				'advanced_tab_header_0'           => array(
					'name' => '<strong>' . __( 'YouTube Video API', 'recipepress-reloaded' ) . '</strong>',
					'type' => 'header',
				),
				'spacer_0'                     => array(
					'name' => '',
					'type' => 'spacer',
				),
				'rpr_youtube_api_key'     => array(
					'name' => __( 'YouTube API Key', 'recipepress-reloaded' ),
					'desc' => __( 'Your YouTube Data API key (v3)', 'recipepress-reloaded' ),
					'std'  => '',
					'type' => 'password',
				),
				'rpr_ping_youtube'    => array(
					'name' => __( 'Ping YouTube weekly', 'recipepress-reloaded' ),
					'desc' => __( 'Enable this to keep your YouTube API key active.', 'recipepress-reloaded' ) . ( $time_ago ? " (last checked: {$time_ago})" : false ),
					'type' => 'checkbox',
				),
				'rpr_speedup_youtube_embeds'    => array(
					'name' => __( 'Speedup YouTube oEmbeds', 'recipepress-reloaded' ),
					'desc' => __( 'Fetches YouTube iframe only on user interaction.', 'recipepress-reloaded' ),
					'type' => 'checkbox',
				),
                'spacer_1'                     => array(
                    'name' => '',
                    'type' => 'spacer',
                ),
                'advanced_tab_header_1'           => array(
                    'name' => '<strong>' . __( 'Vimeo API', 'recipepress-reloaded' ) . '</strong>',
                    'type' => 'header',
                ),
                'spacer_2'                     => array(
                    'name' => '',
                    'type' => 'spacer',
                ),
                'rpr_vimeo_token'     => array(
                    'name' => __( 'Vimeo Token', 'recipepress-reloaded' ),
                    'desc' => __( 'Vimeo personal access token', 'recipepress-reloaded' ),
                    'std'  => '',
                    'type' => 'password',
                ),
				'spacer_4'                     => array(
					'name' => '',
					'type' => 'spacer',
				),
				'advanced_tab_header_2'           => array(
					'name' => '<strong>' . __( 'Yoast SEO', 'recipepress-reloaded' ) . '</strong>',
					'type' => 'header',
				),
				'spacer_5'                     => array(
					'name' => '',
					'type' => 'spacer',
				),
				'rpr_integrate_wpseo_metadata'     => array(
					'name' => __( 'Yoast SEO', 'recipepress-reloaded' ),
					'desc' => __( 'Integrate recipe JSON-LD metadata with Yoast SEO\'s schema output', 'recipepress-reloaded' ),
					'type' => 'checkbox',
				),

                /*'spacer_4'                     => array(
                    'name' => '',
                    'type' => 'spacer',
                ),
                'advanced_tab_header_2'           => array(
                    'name' => '<strong>' . __( 'Edamam Nutrition API', 'recipepress-reloaded' ) . '</strong>',
                    'type' => 'header',
                ),
                'spacer_5'                     => array(
                    'name' => '',
                    'type' => 'spacer',
                ),
                'rpr_edamam_app_id'     => array(
                    'name' => __( 'Edamam App ID', 'recipepress-reloaded' ),
                    'desc' => __( 'Your Edamam app ID', 'recipepress-reloaded' ),
                    'std'  => '',
                    'type' => 'password',
                ),
                'rpr_edamam_app_key'     => array(
                    'name' => __( 'Edamam App Key', 'recipepress-reloaded' ),
                    'desc' => __( 'Your Edamam app key', 'recipepress-reloaded' ),
                    'std'  => '',
                    'type' => 'password',
                ),*/
                'spacer_7'                     => array(
                    'name' => '',
                    'type' => 'spacer',
                ),
			),

		);

		// Adds each custom taxonomy's options via a loop.
		$custom_taxonomies = ( new self() )->get_custom_taxonomies();
		foreach ( $custom_taxonomies as $tax ) {
			foreach ( self::taxonomy_options( $tax['tax_settings'] ) as $key => $value ) {
				$settings['taxonomy_tab'][ strtolower( $key ) ] = $value;
			}
		}

		return self::apply_tab_slug_filters( $settings );
	}

	/**
	 * The taxonomy options.
	 *
	 * @param array $tax The taxonomy options.
	 *
	 * @return array
	 */
	public static function taxonomy_options( $tax ) {

		$opts = array(
			'rpr_' . ( new self )->sanitize_input( $tax['settings_key'] ) . '_header' => array(
				'name' => '<strong>' . ucfirst( $tax['labels']['singular'] ) . '</strong>',
				'type' => 'header',
			),
			'rpr_' . ( new self )->sanitize_input( $tax['settings_key'] ) . '_labels'   => array(
				'name'          => __( 'Labels', 'recipepress-reloaded' ),
				'singular_std'  => ! empty( $tax['labels']['singular'] ) ? $tax['labels']['singular'] : ucwords( $tax['settings_key'] ),
				'plural_std'    => ! empty( $tax['labels']['plural'] ) ? $tax['labels']['plural'] : Pluralizer::pluralize( ucwords( $tax['settings_key'] ) ),
				'singular_desc' => sprintf( __( 'The singular form of the <b>"%1$s"</b> taxonomy label', 'recipepress-reloaded' ), ucwords( $tax['settings_key'] ) ),
				'plural_desc'   => sprintf( __( 'The plural form of the <b>"%1$s"</b> taxonomy label', 'recipepress-reloaded' ), ucwords( $tax['settings_key'] ) ),
				'type'          => 'labels',
				'size'          => '25',
			),
			'rpr_' . ( new self )->sanitize_input( $tax['settings_key'] ) . '_hierarchical' => array(
				'name' => __( 'Hierarchical', 'recipepress-reloaded' ),
				'desc' => __( 'Is this custom taxonomy hierarchical?', 'recipepress-reloaded' ),
				'type' => 'checkbox',
			),
			'rpr_' . ( new self )->sanitize_input( $tax['settings_key'] ) . '_show'    => array(
				'name' => __( 'Show in table', 'recipepress-reloaded' ),
				'desc' => __( 'Show this custom taxonomy on the admin table', 'recipepress-reloaded' ),
				'type' => 'checkbox',
			),
			'rpr_' . ( new self )->sanitize_input( $tax['settings_key'] ) . '_filter'  => array(
				'name' => __( 'Show filter', 'recipepress-reloaded' ),
				'desc' => __( 'Show a drop-down filter for this taxonomy on the admin table', 'recipepress-reloaded' ),
				'type' => 'checkbox',
			),
			'rpr_' . ( new self )->sanitize_input( $tax['settings_key'] ) . '_show_front'  => array(
				'name' => __( 'Show on frontend', 'recipepress-reloaded' ),
				'desc' => __( 'Show this taxonomy on the frontend', 'recipepress-reloaded' ),
				'type' => 'checkbox',
			),
			'spacer' . ( new self )->sanitize_input( $tax['settings_key'] ) . '_0'         => array(
				'name' => '',
				'type' => 'spacer',
			),
		);

		return $opts;
	}
}
