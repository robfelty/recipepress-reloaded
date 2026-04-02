<?php

namespace Recipepress\Inc\Common\Abstracts;

use Recipepress as NS;
use Recipepress\Inc\Core\Options;

/**
 * The abstract post type class.
 *
 * @package Recipepress
 *
 * @author  Kemory Grubb <kemory@wzymedia.com>
 */
abstract class PostType {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	protected $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	protected $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 * @param   string $plugin_name     The ID of this plugin.
	 * @param   string $version         The current version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Creates the custom post type.
	 *
	 * @since   1.0.0
	 *
	 * @uses    register_post_type()
	 * @return  void
	 */
	abstract public function register_post_type();

	/**
	 * Creates the custom taxonomy.
	 *
	 * @since   1.0.0
	 *
	 * @uses    register_taxonomy()
	 * @return  void
	 */
	abstract public function create_custom_taxonomy();

	/**
	 * The custom post type query.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Query $query The default query object.
	 * @return  void
	 */
	abstract public function custom_post_type_query( $query );

	/**
	 * Change the WP query and custom post type to query object
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Query $query The default WP_Query object.
	 * @return void
	 */
	abstract protected function add_post_type_to_query( $query );

	/**
	 * Get the rendered content of the post type and forward it to the theme as the_content()
	 *
	 * @since 1.0.0
	 *
	 * @param string $content   The default WP content.
	 * @return string $content
	 */
	abstract public function get_the_post_type_content( $content );

	/**
	 * Do the actual rendering of the post type content using the `recipe.php`
	 * file provided by the layout
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Post $recipe   The WP post/recipe object.
	 * @return string $content
	 */
	abstract public function render_post_type_content( $recipe );

	/**
	 * Do the actual rendering of the post type excerpt using the review.php file
	 * provided by the layout
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Post $recipe   The WP post/recipe object.
	 * @return string $content
	 */
	abstract public function render_post_type_excerpt( $recipe );

	/**
	 * Adds the custom post type to the RSS Feed.
	 *
	 * @since 1.0.0
	 * @param array $query  The current WP query array.
	 * @return array $query
	 */
	abstract public function add_post_type_to_rss_feed( array $query );

	/**
	 * Get the path to the layout file depending on the layout options.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $display The review layout we are working with.
	 *
	 * @return string
	 */
	public function get_the_layout( $display ) {

		// Get the layout chosen.
		$layout = apply_filters( 'rpr/recipe/template', Options::get_option( 'rpr_recipe_template', 'rpr_default' ) );

		// Get global template from theme.
		$include_path = get_stylesheet_directory() . '/recipepress/' . $layout . '/';

		if ( is_dir( $include_path ) && file_exists( $include_path . $display . '.php' ) ) {
			return $include_path;
		}

		// Get local template from this plugin.
		$include_path = NS\PLUGIN_DIR . 'inc/frontend/templates/' . $layout . '/';

		return $include_path;
	}

}
