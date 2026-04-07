<?php

namespace Recipepress\Inc\Core;

use Recipepress as NS;
use Recipepress\Inc\Admin\PostTypes\Recipe;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 *
 * @author     Kemory Grubb
 **/
class Activator {

	/**
	 * Activate the plugin.
	 *
	 * If the user's PHP version is below 5.6 we are deactivating the plugin with
	 * a message explaining the action.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		$min_php = '5.6.0';

		// Check PHP Version and deactivate & die if it doesn't meet minimum requirements.
		if ( version_compare( PHP_VERSION, $min_php, '<' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			// phpcs:ignore
			wp_die( sprintf( __( 'This plugin requires a minimum PHP Version of %s.', 'recipepress-reloaded' ), $min_php ) );
		}

		// set_transient( '_rpr_welcome_screen_activation_redirect', true, 30 ); TODO: Enable after finishing welcome screen

		/** @see https://developer.wordpress.org/reference/functions/register_post_type/#flushing-rewrite-on-activation */
		( new Recipe( NS\PLUGIN_NAME, NS\PLUGIN_VERSION ) )->register_post_type();

		self::default_settings();

		flush_rewrite_rules();
	}

	/**
	 * Sets the plugin defaults on first install
	 *
	 * @since 1.0.0
	 *
	 * @uses \get_option()
	 * @uses \add_option()
	 *
	 * @param bool $return Return only the values.
	 *
	 * @return array|void
	 */
	public static function default_settings( $return = null ) {

		$defaults = array();

		// Plugin version.
		$defaults['rpr_version'] = '1.0.0';

		// General tab.
		$defaults['rpr_recipe_labels']['singular'] = __( 'Recipe', 'recipepress-reloaded' );
		$defaults['rpr_recipe_labels']['plural']   = __( 'Recipes', 'recipepress-reloaded' );
		$defaults['rpr_recipes_on_homepage']       = '1';
		$defaults['rpr_recipes_archive']           = 'archive_display_excerpt';
		$defaults['rpr_recipes_in_rss']            = '1';

		// Ingredients tab.
		$defaults['rpr_ingredient_labels']['singular'] = __( 'Ingredient', 'recipepress-reloaded' );
		$defaults['rpr_ingredient_labels']['plural']   = __( 'Ingredients', 'recipepress-reloaded' );
		$defaults['rpr_ingredient_links']              = '2';
		$defaults['rpr_ingredient_separator']          = '1';
		$defaults['rpr_ingredient_pluralization']      = '';

		// Taxonomy tab.
		$defaults['rpr_enable_categories']  = '1';
		$defaults['rpr_show_categories']    = '';
		$defaults['rpr_enable_tags']        = '1';
		$defaults['rpr_show_tags']          = '';
		$defaults['rpr_taxonomy_selection'] = __( 'Course,Cuisine,Season,Difficulty', 'recipepress-reloaded' );

		$defaults['rpr_course_labels']['singular'] = __( 'Course', 'recipepress-reloaded' );
		$defaults['rpr_course_labels']['plural']   = __( 'Courses', 'recipepress-reloaded' );
		$defaults['rpr_course_hierarchical']       = '1';
		$defaults['rpr_course_show']               = '1';
		$defaults['rpr_course_filter']             = '1';
		$defaults['rpr_course_show_front']         = '1';

		$defaults['rpr_cuisine_labels']['singular'] = __( 'Cuisine', 'recipepress-reloaded' );
		$defaults['rpr_cuisine_labels']['plural']   = __( 'Cuisines', 'recipepress-reloaded' );
		$defaults['rpr_cuisine_hierarchical']       = '';
		$defaults['rpr_cuisine_show']               = '1';
		$defaults['rpr_cuisine_filter']             = '1';
		$defaults['rpr_cuisine_show_front']         = '1';

		$defaults['rpr_season_labels']['singular'] = __( 'Season', 'recipepress-reloaded' );
		$defaults['rpr_season_labels']['plural']   = __( 'Seasons', 'recipepress-reloaded' );
		$defaults['rpr_season_hierarchical']       = '';
		$defaults['rpr_season_show']               = '1';
		$defaults['rpr_season_filter']             = '1';
		$defaults['rpr_season_show_front']         = '1';

		$defaults['rpr_difficulty_labels']['singular'] = __( 'Difficulty', 'recipepress-reloaded' );
		$defaults['rpr_difficulty_labels']['plural']   = __( 'Difficulties', 'recipepress-reloaded' );
		$defaults['rpr_difficulty_hierarchical']       = '';
		$defaults['rpr_difficulty_show']               = '1';
		$defaults['rpr_difficulty_filter']             = '1';
		$defaults['rpr_difficulty_show_front']         = '';

		// Metadata tab.
		$defaults['rpr_use_source_meta']      = '';
		$defaults['rpr_use_nutritional_meta'] = '1';
		$defaults['rpr_diet_selection']       = '';

		// Units tab.
		$defaults['rpr_use_ingredient_unit_list'] = '';
		$defaults['rpr_ingredient_unit_list']     = 'teaspoon,tablespoon,fluidounce,cup,pint,quart,gallon,milliliter,liter,milligram,gram,kilogram,ounce,pound,package,slice,can,dash,pinch,drop,clove,whole,stalk,piece,cube,cut,diced,chopped,minced,grated,peeled,halved,sliced,juiceof,zestof,pinchof';
		$defaults['rpr_use_serving_unit_list']    = '';
		$defaults['rpr_serving_unit_list']        = 'serving,slice,piece,pieces,wedge,cup,spoonful,tablespoon,teaspoon,pinch,dash,bowl,plate,loaf,loaves,bun,baguette,muffin,cookie,patty,square,cube,sphere,roll,link,drumstick,breast,thigh,drum,cutlet,fillet,steak,roast,branch,sprig,leaf,head,clove,bulb,stick,package';

		// Components tab.
		$defaults['rpr_comment_rating']         = '1';
		$defaults['rpr_comment_rating_label']   = __( 'Rate this recipe', 'recipepress-reloaded' );
		$defaults['rpr_comment_rating_color']   = 'rgba(255, 235, 59, 1)';
		$defaults['rpr_tag_cloud_widget']       = '1';
		$defaults['rpr_taxonomy_list_widget']   = '1';
		$defaults['rpr_recipe_calendar_widget'] = '1';

		// Appearance tab.
		$defaults['rpr_recipe_template']                = 'rpr_default';
		$defaults['rpr_recipe_template_click_img']      = '';
		$defaults['rpr_recipe_template_inst_image']     = 'right';
		$defaults['rpr_recipe_template_use_icons']      = '';
		$defaults['rpr_recipe_template_print_area']     = '#rpr-recipe';
		$defaults['rpr_recipe_template_no_print_area']  = '.no-print';
		$defaults['rpr_recipe_template_recipe_jump']    = '';
		$defaults['rpr_recipe_template_jump_btn_text']  = __( 'Jump to Recipe', 'recipepress-reloaded' );
		$defaults['rpr_recipe_template_print_btn']      = '';
		$defaults['rpr_recipe_template_print_btn_text'] = __( 'Print this Recipe', 'recipepress-reloaded' );

		$defaults['rpr_recipe_custom_styling']         = '';
		$defaults['rpr_excerpt_read_more']             = __( 'Read More', 'recipepress-reloaded' );

		// Advanced tab.
		$defaults['rpr_youtube_api_key'] = '';
		$defaults['rpr_ping_youtube']    = '';

		if ( $return ) {
			return $defaults;
		}

		if ( false === get_option( 'recipepress_settings' ) ) {
			add_option( 'recipepress_settings', $defaults );
		}
	}

}
