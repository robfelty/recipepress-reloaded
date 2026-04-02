<?php
/**
 * Extends the Taxonomy abstract class to create new taxonomies
 *
 * @package Recipepress
 */

namespace Recipepress\Inc\Admin\Taxonomies;

use Recipepress as NS;
use Recipepress\Inc\Common\Abstracts\Taxonomy;
use Recipepress\Inc\Core\Options;

/**
 * Handles the recipe's custom taxonomies
 *
 * @package    Recipepress
 *
 * @author     Kemory Grubb <kemory@wzymedia.com>
 */
class Keywords extends Taxonomy {

	use NS\Inc\Common\Traits\Utilities;

	/**
	 * The custom post type internal name
	 *
	 * This is usually set to `rpr_recipe`
	 *
	 * @since    1.0.0
	 *
	 * @access   public
	 * @var      string $cpt_name The custom post type slug from the settings.
	 */
	public $cpt_name;

	/**
	 * The custom post type slug
	 *
	 * @since    1.0.0
	 *
	 * @access   public
	 * @var      string $cpt_slug The custom post type slug from the settings.
	 */
	public $cpt_slug;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_name The ID of this plugin.
	 * @param string $version     The current version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		parent::__construct( $plugin_name, $version );

		$this->cpt_name = 'rpr_recipe';
		$this->cpt_slug = Options::get_option( 'rpr_recipe_labels', array( 'singular' => 'Recipe', 'plural' => 'Recipes' ) )['plural']
			?: __( 'Recipes', 'recipepress-reloaded' );
	}

	/**
	 * Creates and register the custom taxonomies attached to our recipe.
	 *
	 * @since   1.0.0
	 *
	 * @uses    register_taxonomy()
	 * @return  void
	 */
	public function register_taxonomy() {

		$plural   = __( 'Keywords', 'recipepress-reloaded' );
		$single   = __( 'Keyword', 'recipepress-reloaded' );
		$tax_name = 'rpr_keywords';

		$opts['hierarchical']          = false;
		$opts['public']                = false;
		$opts['query_var']             = $tax_name;
		$opts['show_admin_column']     = false;
		$opts['show_tag_cloud']        = false;
		$opts['show_ui']               = true;
		$opts['show_in_menu']          = true;
		$opts['show_in_nav_menus']     = false;
		$opts['sort']                  = '';
		$opts['show_in_rest']          = true;
		$opts['rest_base']             = 'rpr/' . 'keywords';
		$opts['rest_controller_class'] = 'WP_REST_Terms_Controller';

		/**
		 * Note: If you want to ensure that your custom taxonomy behaves like a tag,
		 * you must add the option 'update_count_callback' => '_update_post_term_count'.
		 * Not doing so will result in multiple comma-separated items added at once being saved as a single value,
		 * not as separate values. This can cause undue stress when using get_the_term_list and other term display functions.
		 */
		$opts['update_count_callback'] = '_update_post_term_count';

		$opts['capabilities']['assign_terms'] = 'edit_posts';
		$opts['capabilities']['delete_terms'] = 'manage_categories';
		$opts['capabilities']['edit_terms']   = 'manage_categories';
		$opts['capabilities']['manage_terms'] = 'manage_categories';

		$opts['labels']['add_new_item']               = sprintf( __( 'Add New %1$s', 'recipepress-reloaded' ), $single );
		$opts['labels']['add_or_remove_items']        = sprintf( __( 'Add or remove %1$s', 'recipepress-reloaded' ), $plural );
		$opts['labels']['all_items']                  = $plural;
		$opts['labels']['choose_from_most_used']      = sprintf( __( 'Choose from most used %1$s', 'recipepress-reloaded' ), $plural );
		$opts['labels']['edit_item']                  = sprintf( __( 'Edit %1$s', 'recipepress-reloaded' ), $single );
		$opts['labels']['menu_name']                  = $plural;
		$opts['labels']['name']                       = $plural;
		$opts['labels']['new_item_name']              = sprintf( __( 'New %1$s Name', 'recipepress-reloaded' ), $single );
		$opts['labels']['not_found']                  = sprintf( __( 'No %1$s Found', 'recipepress-reloaded' ), $plural );
		$opts['labels']['parent_item']                = sprintf( __( 'Parent %1$s', 'recipepress-reloaded' ), $single );
		$opts['labels']['parent_item_colon']          = sprintf( __( 'Parent %1$s', 'recipepress-reloaded' ), $single );
		$opts['labels']['popular_items']              = sprintf( __( 'Popular %1$s', 'recipepress-reloaded' ), $plural );
		$opts['labels']['search_items']               = sprintf( __( 'Search %1$s', 'recipepress-reloaded' ), $plural );
		$opts['labels']['separate_items_with_commas'] = sprintf( __( 'Separate %1$s with commas', 'recipepress-reloaded' ), $plural );
		$opts['labels']['singular_name']              = $single;
		$opts['labels']['update_item']                = sprintf( __( 'Update %1$s', 'recipepress-reloaded' ), $single );
		$opts['labels']['view_item']                  = sprintf( __( 'View %1$s', 'recipepress-reloaded' ), $single );

		$opts['rewrite']['ep_mask']      = EP_NONE;
		$opts['rewrite']['hierarchical'] = false;
		$opts['rewrite']['slug']         = $this->sanitize_input( $this->cpt_slug ) . '/' . $this->sanitize_input( $plural );
		$opts['rewrite']['with_front']   = false;

		$opts = apply_filters( 'rpr/taxonomy/keywords/options', $opts );

		register_taxonomy( $tax_name, $this->cpt_name, $opts );
	}

}
