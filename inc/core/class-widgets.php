<?php
/**
 * Define the plugin's widget's functionalities
 *
 * @since   1.0.0
 *
 * @package Recipepress
 */

namespace Recipepress\Inc\Core;


use Recipepress\Inc\Frontend\Widgets\Recipe_Calendar as Calendar;
use Recipepress\Inc\Frontend\Widgets\Recipe_Recent_Ratings;
use Recipepress\Inc\Frontend\Widgets\Recipe_Tag_Cloud as Tag_Cloud;
use Recipepress\Inc\Frontend\Widgets\Recipe_Taxonomy_List as Tax_List;
use Recipepress\Inc\Frontend\Widgets\Recipe_Archive_Filter as Recipe_Filter;
use Recipepress\Inc\Frontend\Widgets\Recipe_Author_Profile as Author_Profile;
use Recipepress\Inc\Frontend\Widgets\Recipe_Recent as Recent_Recipes;
/**
 * Define the plugin's widgets functionality
 *
 * @since   1.0.0
 *
 * @package Recipepress
 * @author  wzyMedia <wzy@outlook.com>
 */
class Widgets {
	/**
	 * The ID of this plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since  1.0.0
	 *
	 * @access private
	 * @var    string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * The Recipe_Archive_Filter class
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 * @var    Recipe_Filter $recipe_filter An instance of the object.
	 */
	public $recipe_filter;

	/**
	 * The Recipe_Author_Profile class
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 * @var    Author_Profile $author_profile An instance of the object.
	 */
	public $author_profile;

	/**
	 * @var Recipe_Recent_Ratings
	 */
	public $recent_rating;

	/**
	 * @var Tax_List
	 */
	public $tax_list;

	/**
	 * @var Tag_Cloud
	 */
	public $tag_cloud;

	/**
	 * @var Calendar
	 */
	public $calendar;

	/**
	 * @var Recent_Recipes
	 */
	public $recent_recipes;

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->calendar       = new Calendar();
		$this->tag_cloud      = new Tag_Cloud();
		$this->tax_list       = new Tax_List();
		$this->recipe_filter  = new Recipe_Filter();
		$this->author_profile = new Author_Profile();
		$this->recent_rating  = new Recipe_Recent_Ratings();
		$this->recent_recipes = new Recent_Recipes();
	}

    /**
     * Purge the RPR Calendar's cache
     *
     * @since 1.11.0
     *
     * @see Recipe_Calendar::get_rpr_calendar()
     *
     * @return void
     */
    public function delete_calendar_cache() {
        wp_cache_delete( 'rpr_calendar', 'calendar' );
	}
}
