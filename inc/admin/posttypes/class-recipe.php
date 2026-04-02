<?php

namespace Recipepress\Inc\Admin\PostTypes;

use Recipepress as NS;
use Recipepress\Inc\Common\Abstracts\PostType;
use Recipepress\Inc\Core\Options;
use Recipepress\Inc\Libraries\Pluralizer\Pluralizer;
use Recipepress\Inc\Frontend;

/**
 * Handles the 'recipe' custom posttype
 *
 * @package    Recipepress
 *
 * @author     Kemory Grubb <kemory@wzymedia.com>
 */
class Recipe extends PostType {

	use NS\Inc\Common\Traits\Utilities;

	/**
	 * Custom post type slug.
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 * @var    string $cpt_slug The custom post type slug from the settings.
	 */
	public $cpt_slug;

	/**
	 * Custom post type name.
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 * @var    string $cpt_name The custom post type, should always be `rpr_recipe`.
	 */
	private $cpt_name;

	/**
	 * CPT Singular label.
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 * @var    string $singular The singular form of the CPT label
	 */
	private $singular;

	/**
	 * CPT Pluralized label.
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 * @var    string $plural The plural form of the CPT label
	 */
	private $plural;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 *
	 * @param string $plugin_name The ID of this plugin.
	 * @param string $version     The current version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		parent::__construct( $plugin_name, $version );

		$this->cpt_slug = Options::get_option( 'rpr_recipe_slug', 'recipe' );
		$this->cpt_name = 'rpr_recipe';
		$this->singular = Options::get_option( 'rpr_recipe_labels', array( 'singular' => 'Recipe', 'plural' => 'Recipes' ) )['singular']
			?: __( 'Recipe', 'recipepress-reloaded' );
		$this->plural   = Options::get_option( 'rpr_recipe_labels', array( 'singular' => 'Recipe', 'plural' => 'Recipes' ) )['plural']
			?: __( 'Recipes', 'recipepress-reloaded' );
	}

	/**
	 * Creates the recipe custom post type.
	 *
	 * @since  1.0.0
	 *
	 * @uses   register_post_type()
	 * @return void
	 */
	public function register_post_type() {

		$opts       = array();
		$cap_type   = 'post';
		$taxonomies = array();

		if ( Options::get_option( 'rpr_enable_categories' ) ) {
			$taxonomies[] = 'category';
		}
		if ( Options::get_option( 'rpr_enable_tags' ) ) {
			$taxonomies[] = 'post_tag';
		}

		$opts['can_export']            = true;
		$opts['capability_type']       = $cap_type;
		$opts['exclude_from_search']   = false;
		$opts['has_archive']           = $this->sanitize_input( $this->plural );
		$opts['hierarchical']          = false;
		$opts['map_meta_cap']          = true;
		$opts['menu_icon']             = 'dashicons-carrot';
		$opts['menu_position']         = 5;
		$opts['public']                = true;
		$opts['publicly_queryable']    = true;
		$opts['query_var']             = true;
		$opts['register_meta_box_cb']  = '';
		$opts['show_in_admin_bar']     = true;
		$opts['show_in_menu']          = true;
		$opts['show_in_nav_menu']      = true;
		$opts['show_ui']               = true;
		$opts['show_in_rest']          = true;
		$opts['rest_base']             = 'rpr/' . $this->sanitize_input( $this->plural );
		$opts['rest_controller_class'] = 'WP_REST_Posts_Controller';

		$opts['description'] = "The 'recipe' custom post type created by the Recipepress Reloaded plugin";

		$opts['supports'] = array(
			'title',
			'editor',
			'thumbnail',
			'excerpt',
			'featured',
			'author',
			'comments',
			'revisions',
			'publicize',
			'custom-fields'
		);

		$opts['taxonomies'] = $taxonomies;

		$opts['capabilities']['delete_others_posts']    = "delete_others_{$cap_type}s";
		$opts['capabilities']['delete_post']            = "delete_{$cap_type}";
		$opts['capabilities']['delete_posts']           = "delete_{$cap_type}s";
		$opts['capabilities']['delete_private_posts']   = "delete_private_{$cap_type}s";
		$opts['capabilities']['delete_published_posts'] = "delete_published_{$cap_type}s";
		$opts['capabilities']['edit_others_posts']      = "edit_others_{$cap_type}s";
		$opts['capabilities']['edit_post']              = "edit_{$cap_type}";
		$opts['capabilities']['edit_posts']             = "edit_{$cap_type}s";
		$opts['capabilities']['edit_private_posts']     = "edit_private_{$cap_type}s";
		$opts['capabilities']['edit_published_posts']   = "edit_published_{$cap_type}s";
		$opts['capabilities']['publish_posts']          = "publish_{$cap_type}s";
		$opts['capabilities']['read_post']              = "read_{$cap_type}";
		$opts['capabilities']['read_private_posts']     = "read_private_{$cap_type}s";

		// translators: Please ignore.
		$opts['labels']['add_new'] = sprintf( __( 'New %1$s', 'recipepress-reloaded' ), $this->singular );
		// translators: Please ignore.
		$opts['labels']['add_new_item'] = sprintf( __( 'Add New %1$s', 'recipepress-reloaded' ), $this->singular );
		$opts['labels']['all_items']    = sprintf( __( 'All %1$s', 'recipepress-reloaded' ), $this->plural );
		// translators: Please ignore.
		$opts['labels']['edit_item']      = sprintf( __( 'Edit %1$s', 'recipepress-reloaded' ), $this->singular );
		$opts['labels']['menu_name']      = $this->plural;
		$opts['labels']['name']           = $this->plural;
		$opts['labels']['name_admin_bar'] = $this->singular;
		// translators: Please ignore.
		$opts['labels']['new_item'] = sprintf( __( 'New %1$s', 'recipepress-reloaded' ), $this->singular );
		// translators: Please ignore.
		$opts['labels']['not_found'] = sprintf( __( 'No %1$s Found', 'recipepress-reloaded' ), $this->plural );
		// translators: Please ignore.
		$opts['labels']['not_found_in_trash'] = sprintf( __( 'No %1$s Found in Trash', 'recipepress-reloaded' ), $this->plural );
		// translators: Please ignore.
		$opts['labels']['parent_item_colon'] = sprintf( __( 'Parent %1$s:', 'recipepress-reloaded' ), $this->plural );
		// translators: Please ignore.
		$opts['labels']['search_items']  = sprintf( __( 'Search %1$s', 'recipepress-reloaded' ), $this->plural );
		$opts['labels']['singular_name'] = $this->singular;
		// translators: Please ignore.
		$opts['labels']['view_item']             = sprintf( __( 'View %1$s', 'recipepress-reloaded' ), $this->singular );
		$opts['labels']['featured_image']        = __( 'Recipe photo', 'recipepress-reloaded' );
		$opts['labels']['set_featured_image']    = __( 'Set featured photo', 'recipepress-reloaded' );
		$opts['labels']['remove_featured_image'] = __( 'Remove featured photo', 'recipepress-reloaded' );
		$opts['labels']['use_featured_image']    = __( 'Use as featured photo', 'recipepress-reloaded' );

		$opts['rewrite']['ep_mask']    = EP_PERMALINK;
		$opts['rewrite']['feeds']      = true;
		$opts['rewrite']['pages']      = true;
		$opts['rewrite']['slug']       = $this->sanitize_input( $this->singular );
		$opts['rewrite']['with_front'] = false;

		$opts = apply_filters( 'rpr/recipe_posttype/options', $opts );

		register_post_type( $this->cpt_name, $opts );
	}

	/**
	 * Creates the custom post type.
	 *
	 * @since  1.0.0
	 * @uses   register_post_type()
	 * @return void
	 */
	public function create_custom_taxonomy() {

		__return_false(); // Handled by the Admin\Taxonomies classes.
	}

	/**
	 * Add the `rpr_recipe` custom post type to the WP query.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Query $query The default query object.
	 *
	 * @return void
	 */
	public function custom_post_type_query( $query ) {

		// Don't change query on an admin page.
		if ( is_admin() ) {
			return;
		}

		// Check on all public pages.
		if ( $query->is_main_query() ) {
			// Recipe archive page.
			if ( is_post_type_archive( $this->cpt_name ) ) {
				// Set post type to only recipes.
				$query->set( 'post_type', $this->cpt_name );

				return;
			}

			// Add 'rpr_recipe' CPT to homepage if set in options.
			if ( Options::get_option( 'rpr_recipes_on_homepage' ) ) {
				if ( is_home() || $query->is_home() ) {
					$this->add_post_type_to_query( $query );
				}
			}
			// Every other page.
			if ( is_category() || is_tag() || is_author() || is_date() ) {
				$this->add_post_type_to_query( $query );

				return;
			}
		}
	}

	/**
	 * Change the query and add recipes to query object
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Query $query The default WP_Query object.
	 *
	 * @return void
	 */
	protected function add_post_type_to_query( $query ) {

		// Add the custom post type to query.
		$post_type = $query->get( 'post_type' );

		if ( is_array( $post_type ) && ! array_key_exists( $this->cpt_name, $post_type ) ) {
			$post_type[] = $this->cpt_name;
		} else {
			$post_type = array( 'post', $post_type, $this->cpt_name );
		}

		$query->set( 'post_type', $post_type );
	}

	/**
	 * Get the rendered content of a recipe and forward it to the theme as `the_content()`.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content The default WP content.
	 *
	 * @return string $content
	 */
	public function get_the_post_type_content( $content ) {

		if ( ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		// Only render specifically if we have a recipe.
		if ( get_post_type() === $this->cpt_name ) {

			// Remove the filter.
			remove_filter( 'the_content', array( $this, 'get_the_post_type_content' ) );

			$recipe               = get_post();
			$GLOBALS['recipe_id'] = $recipe->ID;
			$archive_display      = Options::get_option( 'rpr_recipes_archive', 'archive_display_full' );

			if ( 'archive_display_full' === $archive_display || is_single() ) {
				$content = $this->render_post_type_content( $recipe );
			} else {
				$content = $this->render_post_type_excerpt( $recipe );
			}

			// Add the filter again.
			add_filter( 'the_content', array( $this, 'get_the_post_type_content' ), 10 );
		}

		// Return the rendered content.
		return $content;
	}

	/**
	 * Do the actual rendering using the 'recipe.php' file provided by the layout
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Post $recipe The WP post/recipe object.
	 *
	 * @return string $content
	 */
	public function render_post_type_content( $recipe ) {

		// Get the layout's include path.
		$include_file = $this->get_the_layout( 'recipe' ) . 'recipe.php';

		if ( ! file_exists( $include_file ) ) {
			// If the layout does not provide a recipe template file, use the default one.
			$include_file = NS\PLUGIN_DIR . 'inc/frontend/templates/rpr_default/recipe.php';
		}

		// Start rendering.
		ob_start();

		// Include the full recipe template file.
		include $include_file;

		// Return the rendered content.
		return ob_get_clean();
	}

	/**
	 * Do the actual rendering using the excerpt.php file provided by the layout
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Post $recipe The WP post object.
	 *
	 * @return string $content
	 */
	public function render_post_type_excerpt( $recipe ) {

		// Get the layout's include path.
		$include_file = $this->get_the_layout( 'excerpt' ) . 'excerpt.php';

		if ( ! file_exists( $include_file ) ) {
			// If the layout does not provide a recipe template file, use the default one.
			$include_file = NS\PLUGIN_DIR . 'inc/frontend/templates/rpr_default/excerpt.php';
		}

		// Start rendering.
		ob_start();

		// Include the full recipe template file.
		include $include_file;

		// Return the rendered content.
		return ob_get_clean();
	}

	/**
	 * Adds the review CPT to the RSS Feed
	 *
	 * @since 1.0.0
	 *
	 * @param array $query The current WP query array.
	 *
	 * @return array $query
	 */
	public function add_post_type_to_rss_feed( array $query ) {

		if ( isset( $query['feed'] ) && ! isset( $query['post_type'] ) && Options::get_option( 'rpr_recipes_in_rss' ) ) {
			$query['post_type'] = array( 'post', $this->cpt_name );
		}

		return $query;
	}

	/**
	 * Recipe update messages.
	 *
	 * @since 1.0.0
	 *
	 * @see   /wp-admin/edit-form-advanced.php
	 *
	 * @param array $messages Existing post update messages.
	 *
	 * @return array Amended post update messages with new recipe update messages.
	 */
	public function updated_messages( $messages ) {

		$post             = get_post();
		$post_type        = (string) get_post_type( $post );
		$post_type_object = get_post_type_object( $post_type );

		$messages['rpr_recipe'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Recipe updated.', 'recipepress-reloaded' ),
			2  => __( 'Custom field updated.', 'recipepress-reloaded' ),
			3  => __( 'Custom field deleted.', 'recipepress-reloaded' ),
			4  => __( 'Recipe updated.', 'recipepress-reloaded' ),
			// translators: %s: date and time of the revision.
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Recipe restored to revision from %s', 'recipepress-reloaded' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Recipe published.', 'recipepress-reloaded' ),
			7  => __( 'Recipe saved.', 'recipepress-reloaded' ),
			8  => __( 'Recipe submitted.', 'recipepress-reloaded' ),
			9  => sprintf(
				__( 'Recipe scheduled for: <strong>%1$s</strong>.', 'recipepress-reloaded' ),
				// translators: Publish box date format, see http://php.net/date.
				date_i18n( __( 'M j, Y @ G:i', 'recipepress-reloaded' ), strtotime( $post->post_date ) )
			),
			10 => __( 'Recipe draft updated.', 'recipepress-reloaded' ),
		);

		if ( $post_type_object && $post_type_object->publicly_queryable && 'rpr_recipe' === $post_type ) {
			$permalink = get_permalink( $post->ID );

			$view_link                  = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View recipe', 'recipepress-reloaded' ) );
			$messages[ $post_type ][1] .= $view_link;
			$messages[ $post_type ][6] .= $view_link;
			$messages[ $post_type ][9] .= $view_link;

			$preview_permalink          = add_query_arg( 'preview', 'true', $permalink );
			$preview_link               = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview recipe', 'recipepress-reloaded' ) );
			$messages[ $post_type ][8]  .= $preview_link;
			$messages[ $post_type ][10] .= $preview_link;
		}

		return $messages;
	}

	/**
	 * Adds recipes to the 'At a Glance' Dashboard widget
	 *
	 * @since 1.0.0
	 *
	 * @param array $items The array of 'At a Glance' items.
	 *
	 * @return array
	 */
	public function add_recipes_to_glance_items( array $items ) {

		$text = '';
		$num_recipes = wp_count_posts( 'rpr_recipe' );

		if ( $num_recipes ) {
			$published = (int) $num_recipes->publish;
			$post_type = get_post_type_object( 'rpr_recipe' );

			if ( $post_type ) {
				$text = _n( '%s ' . $post_type->labels->singular_name, '%s ' . $post_type->labels->name, $published, 'recipepress-reloaded' );
				$text = sprintf( $text, number_format_i18n( $published ) );
			}

			if ( $post_type && current_user_can( $post_type->cap->edit_posts ) ) {
				$items[] = sprintf( '<a class="%1$s-count" href="edit.php?post_type=%1$s">%2$s</a>', 'rpr_recipe', $text ) . "\n";
			} else {
				$items[] = sprintf( '<span class="%1$s-count">%2$s</span>', 'rpr_recipe', $text ) . "\n";
			}
		}

		return $items;
	}

	/**
	 * Adds recipe ratings to the 'At a Glance' Dashboard widget
	 *
	 * @since 2.1.0
	 *
	 * @param array $items The array of 'At a Glance' items.
	 *
	 * @return array
	 */
	public function add_ratings_to_glance_items( array $items ) {
		global $wpdb;
		$num_ratings = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->comments WHERE comment_karma > 0" );

		$text = _n( '%s ' . 'Rating', '%s ' . 'Ratings', $num_ratings, 'recipepress-reloaded' );
		$text = sprintf( $text, number_format_i18n( $num_ratings ) );

		if ( current_user_can( 'edit_posts' ) ) {
			$items[] = sprintf( '<a class="%1$s-ratings" href="edit-comments.php?post_type=%1$s">%2$s</a>', 'rpr_recipe', $text ) . "\n";
		} else {
			$items[] = sprintf( '<span class="%1$s-ratings">%2$s</span>', 'rpr_recipe', $text ) . "\n";
		}

		return $items;
	}

	/**
	 * Adds recipes to the 'Recent Activity' Dashboard widget
	 *
	 * @since 1.0.0
	 *
	 * @param array $query_args The recent activity query arguments.
	 *
	 * @return array
	 */
	public function add_to_dashboard_recent_posts_widget( $query_args ) {

		return array_merge( $query_args, array( 'post_type' => array( 'post', 'rpr_recipe' ) ) );
	}

	/**
	 * Function to display any errors in the backend.
	 *
	 * @since 1.0.0
	 *
	 * return void
	 */
	public function admin_notice_handler() {

        $screen = \function_exists( 'get_current_screen' ) ? \get_current_screen() : null;
		$errors = get_option( 'rpr_admin_errors' );

		if ( $screen && 'rpr_recipe' === $screen->id && $errors ) {
			echo '<div class="error"><p>' . esc_html( $errors ) . '</p></div>';
		}

		// Reset the error option for the next error.
		update_option( 'rpr_admin_errors', false );
	}

	/**
	 * Adds the recipe CPT to the rewrite rules for date archive.
	 *
	 * @since 1.0.0
	 *
	 * @see   goo.gl/RYqinL
	 *
	 * @param \WP_Rewrite $wp_rewrite The WP_Rewrite class.
	 *
	 * @return \WP_Rewrite
	 */
	public function date_archives_rewrite_rules( $wp_rewrite ) {

		$rules             = $this->generate_date_archives( 'rpr_recipe', $wp_rewrite );
		$wp_rewrite->rules = array_merge( $rules, $wp_rewrite->rules );

		return $wp_rewrite;
	}

	/**
	 * Generates the rewrite rules for the date archives.
	 *
	 * @since 1.0.0
	 *
	 * @param string      $cpt        The custom post type.
	 * @param \WP_Rewrite $wp_rewrite The WP_Rewrite class.
	 *
	 * @return array
	 */
	public function generate_date_archives( $cpt, $wp_rewrite ) {

		$rules     = array();
		$post_type = get_post_type_object( $cpt );

		if ( null === $post_type ) { // If our custom post type is not present fail gracefully.
			return $rules;
		}

		$slug_archive = $post_type->has_archive;

		if ( false === $slug_archive ) {
			return $rules;
		}

		if ( true === $slug_archive ) {
			$slug_archive = $post_type->name;
		}

		$dates = array(
			array(
				'rule' => '([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})',
				'vars' => array( 'year', 'monthnum', 'day' ),
			),
			array(
				'rule' => '([0-9]{4})/([0-9]{1,2})',
				'vars' => array( 'year', 'monthnum' ),
			),
			array(
				'rule' => '([0-9]{4})',
				'vars' => array( 'year' ),
			),
		);

		foreach ( $dates as $data ) {

			$query = 'index.php?post_type=' . $cpt;
			$rule  = Pluralizer::pluralize( $slug_archive ) . '/' . $data['rule'];

			$i = 1;
			foreach ( $data['vars'] as $var ) {
				$query .= '&' . $var . '=' . $wp_rewrite->preg_index( $i );
				$i ++;
			}

			$rules[ $rule . '/?$' ]                               = $query;
			$rules[ $rule . '/feed/(feed|rdf|rss|rss2|atom)/?$' ] = $query . '&feed=' . $wp_rewrite->preg_index( $i );
			$rules[ $rule . '/(feed|rdf|rss|rss2|atom)/?$' ]      = $query . '&feed=' . $wp_rewrite->preg_index( $i );
			$rules[ $rule . '/page/([0-9]{1,})/?$' ]              = $query . '&paged=' . $wp_rewrite->preg_index( $i );
		}

		return $rules;
	}

}
