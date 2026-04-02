<?php

use Recipepress as NS;
use Recipepress\Inc\Common\Abstracts\Extension;

/**
 * Class RPR_Posts_To_Reviews
 */
class RPR_Posts_To_Recipes extends Extension {

	/**
	 * RPR_Posts_To_Reviews constructor.
	 */
	public function __construct() {
		$this->id             = 'rpr_posts_to_recipes';
		$this->image          = plugin_dir_url( __FILE__ ) . 'assets/images/rpr-posts-to-recipes.png';
		$this->title          = __( 'Convert Posts to Recipes', 'recipepress-reloaded' );
		$this->desc           = __( 'Converts WordPress post content to recipes and recipe content to WordPress post contents.', 'recipepress-reloaded' );
		$this->settings       = false;
		$this->enable         = false;
		$this->settings_label = __( 'Settings', 'recipepress-reloaded' );
	}

	/**
	 * All methods that we want to be called by the Extension class goes here.
	 */
	public function load() {
	    if ( $this->enable ) {
		    $this->add_filters();
		    $this->add_actions();
        }
	}

	/**
	 * WordPress' filters are called here.
	 */
	private function add_filters() {
		add_filter( 'post_row_actions', array( $this, 'add_link' ), 10, 2 );
		add_filter( 'bulk_actions-edit-post', array( $this, 'post_bulk_actions' ) );
		add_filter( 'bulk_actions-edit-rpr_recipe', array( $this, 'recipe_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-post', array( $this, 'handle_post_bulk_actions' ), 10, 3 );
		add_filter( 'handle_bulk_actions-edit-rpr_recipe', array( $this, 'handle_post_bulk_actions' ), 10, 3 );
	}

	/**
	 * WordPress' actions are called here.
	 */
	private function add_actions() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_notices', array( $this, 'bulk_action_notices' ) );
		add_action( 'rpr/extensions/settings_page/footer', array( $this, 'render_settings_page' ) );
		add_action( 'wp_ajax_convert_post_recipe', array( $this, 'convert_post_recipe' ) );
		add_action( 'admin_print_footer_scripts', array( $this, 'admin_script' ) );
	}

	/**
	 * Adds a link to the WP admin posts table
	 *
	 * @since 2.0.0
	 *
	 * @param array $actions An array of row action links
	 * @param WP_Post $post  The post object
	 *
	 * @return array
	 */
	public function add_link( $actions, $post ) {

		$post_id = $post->ID;
		$nonce   = wp_create_nonce( $this->id );

		// Check for the default type.
		// You can check if the current user has some custom rights.
		if ( ( 'post' === $post->post_type ) && current_user_can( 'edit_post', $post->ID ) ) {

			// Add the new 'Convert' quick link.
			$actions = array_merge( $actions, array(
				'convert' => sprintf( '<a href="#" class="convert-to-recipe" data-post-id="%1$s" data-nonce="%4$s" data-post-type="%5$s" title="%3$s">%2$s</a>',
					$post_id,
					__( 'Convert', 'recipepress-reloaded' ),
					__( 'Convert to Recipe', 'recipepress-reloaded' ),
					$nonce,
					$post->post_type
				)
			) );
		}

		if ( ( 'rpr_recipe' === $post->post_type ) && current_user_can( 'edit_post', $post->ID ) ) {

			// Add the new 'Convert' quick link.
			$actions = array_merge( $actions, array(
				'convert' => sprintf( '<a href="#" class="convert-to-post" data-post-id="%1$s" data-nonce="%4$s" data-post-type="%5$s" title="%3$s">%2$s</a>',
					$post_id,
					__( 'Convert', 'recipepress-reloaded' ),
					__( 'Convert to Post', 'recipepress-reloaded' ),
					$nonce,
					$post->post_type
				)
			) );
		}

		return $actions;
	}

	/**
	 * Handles the AJAX request
	 *
	 * @since 2.0.0
	 *
	 * @uses check_admin_referer()
	 * @uses set_post_type()
	 * @uses wp_send_json_success()
	 * @uses wp_send_json_error()
	 *
	 * @return void
	 */
	public function convert_post_recipe() {

		check_admin_referer( $this->id, 'nonce' );

		$post_id   = ! empty( $_POST['postID'] ) ? (int) $_POST['postID'] : 0;
		$post_type = ! empty( $_POST['postType'] ) ? sanitize_text_field( $_POST['postType'] ) : null;

		if ( $post_id && 'post' === $post_type ) {
			$result = set_post_type( $post_id, 'rpr_recipe' );

			if ( $result ) {
				wp_send_json_success();
			}
		}

		if ( $post_id && 'rpr_recipe' === $post_type ) {
			$result = set_post_type( $post_id, 'post' );

			if ( $result ) {
				wp_send_json_success();
			}
		}

		wp_send_json_error();
	}

	/**
	 * Adds our script on the WP admin pages for posts and recipes
	 *
	 * @since 2.0.0
	 *
	 * @uses \get_current_screen()
	 *
	 * @return void
	 */
	public function admin_script() {

		$screen = function_exists( 'get_current_screen' ) ? \get_current_screen() : null;

		if ( $screen && ( 'edit-rpr_recipe' === $screen->id || 'edit-post' === $screen->id ) ) { ?>
			<script>
                window.jQuery(document).ready(function ($) {

                    $('.convert-to-recipe, .convert-to-post').on('click', function (e) {
                        e.preventDefault();

                        const postID = $(this).data('post-id');
                        const postType = $(this).data('post-type');
                        const nonce = $(this).data('nonce');

                        $.ajax({
                            method: 'POST',
                            url: window.ajaxurl,
                            data: {
                                action: 'convert_post_recipe',
                                nonce,
                                postID,
                                postType,
                            },
                        }).done(function (res) {
                            if (res.success === true) {
                                $('tr#post-' + postID).hide();
                            }
                        }).fail(function (res) {
                            console.log(res);
                        });

                    });

                });
			</script>
		<?php }
	}

	/**
	 * Adds the 'Convert to Recipe' option in the
	 * bulk actions dropdown on the WP admin posts table
	 *
	 * @since 2.0.0
	 *
	 * @param array $bulk_array A list of items in the bulk actions dropdown
	 *
	 * @return array
	 */
	public function post_bulk_actions( $bulk_array ) {

		$bulk_array['convert_to_recipe'] = __( 'Convert to Recipe', 'recipepress-reloaded' );

		return $bulk_array;
	}

	/**
	 * Adds the 'Convert to Post' option in the
	 * bulk actions dropdown on the WP admin posts table
	 *
	 * @since 2.0.0
	 *
	 * @param array $bulk_array A list of items in the bulk actions dropdown
	 *
	 * @return array
	 */
	public function recipe_bulk_actions( $bulk_array ) {

		$bulk_array['convert_to_post'] = __( 'Convert to Post', 'recipepress-reloaded' );

		return $bulk_array;
	}

	/**
	 * Handles converting or selection of posts to recipes
	 * and vice-versa
	 *
	 * @since 2.0.0
	 *
	 * @uses set_post_type()
	 *
	 * @param string $redirect
	 * @param string $do_action
	 * @param array  $object_ids
	 *
	 * @return string
	 */
	public function handle_post_bulk_actions( $redirect, $do_action, $object_ids ) {

		if ( 'convert_to_recipe' === $do_action ) {

			foreach ( $object_ids as $post_id ) {
				set_post_type( $post_id, 'rpr_recipe' );
			}

			$redirect = add_query_arg(
				'converted_to_recipe',
				count( $object_ids ),
				$redirect
			);
		}

		if ( 'convert_to_post' === $do_action ) {

			foreach ( $object_ids as $post_id ) {
				set_post_type( $post_id, 'post' );
			}

			$redirect = add_query_arg(
				'converted_to_post',
				count( $object_ids ),
				$redirect
			);
		}

		return $redirect;
	}

	/**
	 * Adds a updated message to the WP admin screen
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function bulk_action_notices() {

		$out = '';
		$converted_to_recipes = ! empty( $_REQUEST['converted_to_recipe'] ) ? (int) $_REQUEST['converted_to_recipe'] : null;
		$converted_to_posts   = ! empty( $_REQUEST['converted_to_post'] ) ? (int) $_REQUEST['converted_to_post'] : null;

		if ( $converted_to_recipes ) {
			$out .= '<div id="message" class="updated fade notice is-dismissible"><p>';
			$out .= sprintf(
				_n( '%s post converted to a recipe.', '%s posts converted to recipes.', $converted_to_recipes, 'recipepress-reloaded' ),
				number_format_i18n( $converted_to_recipes )
			);
			$out .= '</p></div>';
		}

		if ( $converted_to_posts ) {
			$out .= '<div id="message" class="updated fade notice is-dismissible"><p>';
			$out .= sprintf(
				_n( '%s recipe converted to a post.', '%s recipes converted to posts.', $converted_to_posts, 'recipepress-reloaded' ),
				number_format_i18n( $converted_to_posts )
			);
			$out .= '</p></div>';
		}

		echo $out;
	}

	/**
	 * Registers the settings to be stored to the WP Options table.
	 */
	public function register_settings() {
		register_setting(
		        'rpr-posts-to-recipes',
                'rpr_posts_to_recipes_options',
                array(
			        'sanitize_callback' => array( $this, 'sanitize_settings'
                )
		) );
	}

	/**
	 * The hidden markup the is rendered by the Thickbox modal window.
	 */
	public function render_settings_page() {

	}


	/**
	 * Looks to see if the specified setting exists, returns default if not.
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	protected function get_setting( $key, $default = '' ) {

		if ( empty( $key ) ) {
			return $default;
		}

		$settings = get_option( 'rpr_posts_to_recipes_options', array() );

		return ! empty( $settings[ $key ] ) ? $settings[ $key ] : $default;
	}

	/**
	 * Sanitize the settings being saved by this extension.
	 *
	 * @param array $settings The settings array for the extension.
	 *
	 * @return array
	 */
	public function sanitize_settings( array $settings ) {
		foreach ( $settings as $key => $value ) {
			$settings[ $key ] = sanitize_text_field( $value );
		}
		return $settings;
	}

}

