<?php

use Recipepress\Inc\Common\Abstracts\Extension;

/**
 * Class RPR_Duplicate_Recipe
 *
 * This class is a part of the plugin's "extension" feature.
 *
 * @since 1.0.0
 *
 * @author Kemory Grubb
 */
class RPR_Duplicate_Recipe extends Extension {

	/**
	 * Social_Media_Sharing constructor.
	 *
	 * @since 1.0.0
	 *
	 * @var string $id       The internal ID of the extension. Must match class name.
	 * @var string $image    A image used as an icon on the extensions page - 500x500.
	 * @var string $title    The title of the extension as displayed on the extensions page.
	 * @var string $desc     The description of the extension as displayed on the extensions page.
	 * @var string $settings Does the extension use a settings page.
	 */
	public function __construct() {
		$this->id             = 'rpr_duplicate_recipe';
		$this->image          = plugin_dir_url( __FILE__ ) . 'assets/images/icon.png';
		$this->title          = __( 'Duplicate Recipes', 'recipepress-reloaded' );
		$this->desc           = __( 'A little time saver utility to quickly duplicate an existing recipe to a new draft. Adds a new "Duplicate" post action to WP admin recipes table.',
								'recipepress-reloaded' );
		$this->settings       = false;
		$this->settings_label = __( 'Settings', 'recipepress-reloaded' );
	}

	/**
	 * All methods that we want to be called by the class goes here.
	 *
	 * @since 1.0.0
	 *
	 * return void
	 */
	public function load() {
		$this->add_actions();
	}

	/**
	 * Add WordPress actions to be called here.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function add_actions() {
		add_action( 'admin_action_rpr_duplicate_recipe', array( $this, 'rpr_duplicate_recipe' ) );
		add_filter( 'post_row_actions', array( $this, 'duplicate_recipe_link' ), 10, 2 );
	}

	/**
	 *
	 */
	public function rpr_duplicate_recipe() {

		global $wpdb;

		if ( ! ( isset( $_GET['rpr_recipe'] ) || isset( $_POST['rpr_recipe'] ) || ( isset( $_REQUEST['action'] ) && 'rpr_duplicate_recipe' === $_REQUEST['action'] ) ) ) {
			wp_die( 'No recipe to duplicate has been supplied!' );
		}

		// Nonce verification
		if ( ! isset( $_GET['rpr_duplicate_nonce'] ) || ! wp_verify_nonce( $_GET['rpr_duplicate_nonce'], basename( __FILE__ ) ) ) {
			wp_die( 'Security nonce check failed' );
		}

		// Get the original post id
		$post_id = ( isset( $_GET['rpr_recipe'] ) ? absint( $_GET['rpr_recipe'] ) : absint( $_POST['rpr_recipe'] ) );

		// Get all the original post data then
		$post = get_post( $post_id );

		/**
		 * if you don't want current user to be the new post author,
		 * then change next couple of lines to this: $new_post_author = $post->post_author;
		 */
		$current_user    = wp_get_current_user();
		$new_post_author = $current_user->ID;

		// Iif post data exists, create the post duplicate
		if ( $post ) {

			// New post data array
			$args = array(
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'post_author'    => $new_post_author,
				'post_content'   => $post->post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_name'      => $post->post_name,
				'post_parent'    => $post->post_parent,
				'post_password'  => $post->post_password,
				'post_status'    => 'draft',
				'post_title'     => $post->post_title,
				'post_type'      => $post->post_type,
				'to_ping'        => $post->to_ping,
				'menu_order'     => $post->menu_order,
			);

			// Insert the post by wp_insert_post() function
			$new_post_id = wp_insert_post( $args );

			// get all current post terms ad set them to the new post draft
			$taxonomies = get_object_taxonomies( $post->post_type ); // returns array of taxonomy names for post type, ex array("category", "post_tag");

			foreach ( $taxonomies as $taxonomy ) {
				$post_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );
				wp_set_object_terms( $new_post_id, $post_terms, $taxonomy );
			}

			// Duplicate all post meta just in two SQL queries
			$post_meta_infos = $wpdb->get_results( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id" );
			if ( 0 !== count( $post_meta_infos ) ) {
				$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";

				foreach ( $post_meta_infos as $meta_info ) {
					$meta_key = $meta_info->meta_key;
					if ( '_wp_old_slug' === $meta_key ) {
						continue;
					}
					$meta_value      = addslashes( $meta_info->meta_value );
					$sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
				}

				$sql_query .= implode( " UNION ALL ", $sql_query_sel );
				$wpdb->query( $sql_query );
			}


			// Finally, redirect to the edit recipe screen for the new draft
			wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
			exit;
		}

		wp_die( 'Recipe creation failed, could not find original recipe: ' . $post_id );
	}


	/**
	 * Create new link to duplicate recipes
	 *
	 * @param array $actions
	 * @param \WP_Post $recipe
	 *
	 * @return array
	 */
	public function duplicate_recipe_link( $actions, $recipe ) {

		if ( 'rpr_recipe' === $recipe->post_type && current_user_can( 'edit_posts' ) ) {
			$actions['rpr_duplicate'] = '<a href="' . wp_nonce_url( 'admin.php?action=rpr_duplicate_recipe&rpr_recipe='
										. $recipe->ID, basename( __FILE__ ), 'rpr_duplicate_nonce' )
										. '" title="' . __( 'Duplicate this recipe', 'recipepress-reloaded' )
										. '" rel="permalink">' . __( 'Duplicate', 'recipepress-reloaded' ) . '</a>';
		}

		return $actions;
	}

}
