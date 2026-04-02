<?php
/**
 * Define background processing chores
 *
 * @link       https://wzymedia.com
 *
 * @since      1.0.0
 *
 * @package    Recipepress
 */

namespace Recipepress\Inc\Core;

use Recipepress\Inc\Common\Tasks\Tags_To_Keywords;
use Recipepress\Inc\Common\Tasks\Update_Taxonomy_Slug;
use Recipepress\Inc\Common\Tasks\Update_Ingredients_Sorting;
use Recipepress\Inc\Common\Tasks\Update_Recipe_Ratings;
use Recipepress\Inc\Common\Tasks\Add_Key_To_Recipe;

/**
 * Handles the background tasks the plugin may need to run
 *
 * @since      1.0.0
 *
 * @package    Recipepress
 * @author     wzyMedia <wzy@outlook.com>
 */
class Background_Tasks {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Admin update messages
	 *
	 * @since    1.0.0
	 *
	 * @access public
	 * @var    array $messages An array of admin error messages.
	 */
	public $messages;

	/**
	 * The Tags_To_Keywords class
	 *
	 * @since    1.0.0
	 *
	 * @access   public
	 * @var      Tags_To_Keywords The class instance.
	 */
	public $tags_to_keywords;

	/**
	 * The Update_Taxonomy_Slug class
	 *
	 * @since    1.0.0
	 *
	 * @access   public
	 * @var      Update_Taxonomy_Slug The class instance.
	 */
	public $update_taxonomy_slug;

	/**
	 * The Update_Ingredients_Sorting class
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 * @var    Update_Ingredients_Sorting The class instance.
	 */
	public $update_ingredients;

	/**
	 * The Update_Recipe_Ratings class
	 *
	 * @since  1.9.0
	 *
	 * @access public
	 * @var    Update_Recipe_Ratings The class instance.
	 */
	public $update_ratings;

    /**
     * Add a `key` value to ingredients and instructions
     *
     * @since 2.0.0
     *
     * @access public
     * @var    Add_Key_To_Recipe The class instance.
     */
    public $add_key_to_recipe;

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

		$this->messages = array();

		$this->tags_to_keywords     = new Tags_To_Keywords();
		$this->update_taxonomy_slug = new Update_Taxonomy_Slug();
		$this->update_ingredients   = new Update_Ingredients_Sorting();
		$this->update_ratings       = new Update_Recipe_Ratings();
		$this->add_key_to_recipe    = new Add_Key_To_Recipe();
	}

	/**
	 * Function to display admin update notices.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function update_notice_handler() {

		$screen = function_exists( 'get_current_screen' ) ? \get_current_screen() : null;

		foreach ( $this->messages as $message ) {
			foreach ( $message as $key => $value ) {
				if ( $screen && ( 'dashboard' === $screen->id || 'rpr_recipe' === $screen->post_type || 'toplevel_page_recipepress-reloaded' === $screen->id ) ) {
                    $out = '<div class="rpr notice notice-warning is-dismissible">';
					$out .= '<p class="rpr-update-notice">';
					$out .= esc_html( $value );
					$out .= '</p>';
					$out .= '<button class="rpr-update-button" data-update-notice="' . esc_attr( $key ) . '">';
					$out .= __( 'Update Recipes', 'recipepress-reloaded' );
					$out .= '</button>';
					$out .= '</div>';

					echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
		}

	}

	/**
	 * Load background tasks, if needed
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function load_background_tasks() {

		/*if ( $this->tags_to_keywords->is_update_needed() ) {
			$this->messages[] = $this->tags_to_keywords->update_message();
		}*/

		if ( $this->update_taxonomy_slug->is_update_needed() ) {
			$this->messages[] = $this->update_taxonomy_slug->update_message();
		}

		if ( $this->update_ingredients->is_update_needed() ) {
			$this->messages[] = $this->update_ingredients->update_message();
		}

		if ( $this->update_ratings->is_update_needed() ) {
			$this->messages[] = $this->update_ratings->update_message();
		}

        if ( $this->add_key_to_recipe->is_update_needed() ) {
            $this->messages[] = $this->add_key_to_recipe->update_message();
        }
	}

	/**
	 * Run our background tasks
	 */
	public function run_background_tasks() {

		// phpcs:ignore
		if ( ! wp_verify_nonce( $_POST['update_task_nonce'], 'update-task-nonce' ) ) {

			wp_send_json_error( new \WP_Error( '000', 'Nonce check failed.' ), 403 );
		}

		if ( isset( $_POST['update_task_target'] )
			&& 'rpr_tags_to_keywords_background_process' === $_POST['update_task_target'] ) {

			$recipes = $this->tags_to_keywords->items_to_process();
			foreach ( $recipes as $recipe_id ) {
				$this->tags_to_keywords->push_to_queue( (int) $recipe_id[0] );
			}
			$this->tags_to_keywords->save()->dispatch();
		}

		if ( isset( $_POST['update_task_target'] )
			&& 'rpr_update_taxonomy_slug' === $_POST['update_task_target'] ) {

			$taxonomies = $this->update_taxonomy_slug->items_to_process();
			foreach ( $taxonomies as $taxonomy ) {
				$this->update_taxonomy_slug->push_to_queue( $taxonomy );
			}
			$this->update_taxonomy_slug->save()->dispatch();
		}

		if ( isset( $_POST['update_task_target'] )
			&& 'rpr_update_ingredients' === $_POST['update_task_target'] ) {

			$recipes = array_values( $this->update_ingredients->items_to_process() );
			foreach ( $recipes as $recipe ) {
				$this->update_ingredients->push_to_queue( $recipe );
			}
			$this->update_ingredients->save()->dispatch();
		}

		if ( isset( $_POST['update_task_target'] )
		     && 'rpr_update_recipe_ratings' === $_POST['update_task_target'] ) {

			$recipes = array_values( $this->update_ratings->items_to_process() );

			if ( empty( $recipes ) ) {
                update_option( 'rpr_update_recipe_ratings', array( 'needs_update' => false ) );
                $this->update_ratings->cancel_process();
            }

			foreach ( $recipes as $recipe ) {
				$this->update_ratings->push_to_queue( $recipe );
			}

			$this->update_ratings->save()->dispatch();
		}

        if ( isset( $_POST['update_task_target'] )
            && 'rpr_add_key_to_recipe' === $_POST['update_task_target'] ) {

            $recipes = array_values( $this->add_key_to_recipe->items_to_process() );

            if ( empty( $recipes ) ) {
                update_option( 'rpr_add_key_to_recipe', array( 'needs_update' => false ) );
                $this->add_key_to_recipe->cancel_process();
            }

            foreach ( $recipes as $recipe ) {
                $this->add_key_to_recipe->push_to_queue( $recipe );
            }

            $this->add_key_to_recipe->save()->dispatch();
        }
	}

}
