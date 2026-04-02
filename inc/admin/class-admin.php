<?php

namespace Recipepress\Inc\Admin;

use Recipepress as NS;
use Recipepress\Inc\Admin\Settings\Definitions;

use Recipepress\Inc\Admin\PostTypes;
use Recipepress\Inc\Admin\Settings\Settings;
use Recipepress\Inc\Admin\Taxonomies;
use Recipepress\Inc\Admin\Metaboxes;
use Recipepress\Inc\Core\Options;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link       https://wzymedia.com
 *
 * @since      1.0.0
 *
 * @author    Kemory Grubb
 */
class Admin {

	use NS\Inc\Common\Traits\Utilities;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Instance of the Recipe class.
	 *
	 * @since 1.0.0
	 *
	 * @var PostTypes\Recipe $recipe
	 * @access public
	 */
	public $recipe;

	/**
	 * Instance of the Ingredient taxonomy class.
	 *
	 * @since 1.0.0
	 *
	 * @var Taxonomies\Ingredient $ingredient
	 * @access public
	 */
	public $ingredient;

	/**
	 * Instance of the Custom_Taxonomies taxonomy class.
	 *
	 * @since 1.0.0
	 *
	 * @var Taxonomies\Custom $custom_taxonomies
	 * @access public
	 */
	public $custom_taxonomies;

	/**
	 * Instance of the Keywords taxonomy class.
	 *
	 * @since 1.0.0
	 *
	 * @var Taxonomies\Keywords $keywords
	 * @access public
	 */
	public $keywords;

	/**
	 * Instance of the Ingredients metabox class.
	 *
	 * @since 1.0.0
	 *
	 * @var Metaboxes\Ingredients $ingredients
	 * @access public
	 */
	public $ingredients;

	/**
	 * Instance of the Instructions metabox class.
	 *
	 * @since 1.0.0
	 *
	 * @var Metaboxes\Instructions $instructions
	 * @access public
	 */
	public $instructions;

	/**
	 * Instance of the Notes metabox class.
	 *
	 * @since 1.0.0
	 *
	 * @var Metaboxes\Notes $notes
	 * @access public
	 */
	public $notes;

	/**
	 * Instance of the General_Information metabox class.
	 *
	 * @since 1.0.0
	 *
	 * @var Metaboxes\Information $information
	 * @access public
	 */
	public $information;

	/**
	 * Instance of the Nutrition metabox class.
	 *
	 * @since 1.0.0
	 *
	 * @var Metaboxes\Nutrition $nutrition
	 * @access public
	 */
	public $nutrition;

	/**
	 * Instance of the Video metabox class.
	 *
	 * @since 1.0.0
	 *
	 * @var Metaboxes\Video $video
	 * @access public
	 */
	public $video;

	/**
	 * Instance of the Source metabox class.
	 *
	 * @since 1.0.0
	 *
	 * @var Metaboxes\Source $source
	 * @access public
	 */
	public $source;

    /**
     * Instance of the Link modal class.
     *
     * @since 1.12.0
     *
     * @var Metaboxes\Link $link
     * @access public
     */
    public $link;

	/**
	 * Instance of the Equipment class.
	 *
	 * @since 2.0.0
	 *
	 * @var Metaboxes\Equipment $equipment
	 * @access public
	 */
	public $equipment;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since       1.0.0
	 *
	 * @param       string $plugin_name        The name of this plugin.
	 * @param       string $version            The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name         = $plugin_name;
		$this->version             = $version;

		$this->recipe              = new PostTypes\Recipe( $this->plugin_name, $this->version );

		$this->ingredient          = new Taxonomies\Ingredient( $this->plugin_name, $this->version );
		$this->custom_taxonomies   = new Taxonomies\Custom( $this->plugin_name, $this->version );
		$this->keywords            = new Taxonomies\Keywords( $this->plugin_name, $this->version );

		$this->ingredients         = new Metaboxes\Ingredients( $this->plugin_name, $this->version );
		$this->instructions        = new Metaboxes\Instructions( $this->plugin_name, $this->version );
		$this->notes               = new Metaboxes\Notes( $this->plugin_name, $this->version );
		$this->information         = new Metaboxes\Information( $this->plugin_name, $this->version );
		$this->nutrition           = new Metaboxes\Nutrition( $this->plugin_name, $this->version );
		$this->video               = new Metaboxes\Video( $this->plugin_name, $this->version );
		$this->source              = new Metaboxes\Source( $this->plugin_name, $this->version );
		$this->link                = new Metaboxes\Link( $this->plugin_name, $this->version );
		$this->equipment           = new Metaboxes\Equipment( $this->plugin_name, $this->version );
	}

	/**
	 * Flush the rewrite rules
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function flush_rewrites() {
		flush_rewrite_rules();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function enqueue_styles() {

        $screen = function_exists( 'get_current_screen' ) ? \get_current_screen() : null;

		if ( $screen && 'rpr_recipe' === $screen->post_type ) {
			wp_enqueue_style( 'rpr-minicolors-css', NS\ADMIN_ASSET_URL . 'css/libraries/minicolors.css', array(), '2.2.6', 'all' );
		}

		if ( $screen && 'widgets' === $screen->id ) {
			wp_enqueue_style( 'rpr-selectize', NS\ADMIN_ASSET_URL . 'css/libraries/selectize.default.css', array( $this->plugin_name ) );
		}

		wp_enqueue_style( $this->plugin_name, NS\ADMIN_ASSET_URL . 'css/rpr-admin.css', array(), $this->version, 'all' );

		// Fixes the styling of our metaboxes in the Gutenberg editor
        if ( $screen && 'rpr_recipe' === $screen->post_type && $screen->is_block_editor ) {
            $custom_gtg_style = '
                .edit-post-layout__metaboxes:not(:empty) {margin-top: 2rem;}
                .edit-post-layout__metaboxes:not(:empty) .edit-post-meta-boxes-area {margin: auto 40px;}
                .edit-post-visual-editor {flex-grow: 0; flex-shrink: 0;}
            ';
            wp_add_inline_style( $this->plugin_name, $custom_gtg_style );
        }
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since  1.0.0
	 *
	 * @param string $hook The current admin page hook.
	 *
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {

		global $post;
		$recipe_id = $post ? $post->ID : 0;
		$screen    = function_exists( 'get_current_screen' ) ? \get_current_screen() : null;

		// Add the media uploader.
		/*wp_enqueue_media(); // TODO: Limit this on non-RPR pages

		wp_enqueue_script( $this->plugin_name, NS\ADMIN_ASSET_URL . 'js/rpr-admin.js', array( 'jquery' ), $this->version, true );*/

		if ( $screen && ( 'rpr_recipe' === $screen->post_type || 'rpr_recipe' === $screen->id || 'edit-rpr_ingredient' === $screen->id || 'toplevel_page_recipepress-reloaded' === $screen->id ) ) {
			// Add the media uploader.
			wp_enqueue_media(); // TODO: Limit this on non-RPR pages

			wp_enqueue_script( $this->plugin_name, NS\ADMIN_ASSET_URL . 'js/rpr-admin.js', array( 'jquery' ), $this->version, true );

            // StimulusJS controllers
            wp_enqueue_script( 'rpr-admin-controllers', NS\ASSETS_URL . 'admin/js/rpr-admin-controllers.js', array( 'jquery' ), $this->version, true );

			Settings::plugin_js_options();
		}

		if ( $screen && 'widgets' === $screen->id ) {
			wp_enqueue_script( 'rpr-selectize', NS\ADMIN_ASSET_URL . 'js/libraries/selectize.min.js', array( 'jquery', 'jquery-ui-sortable', $this->plugin_name ) );
		}

		wp_localize_script(
			$this->plugin_name,
			'rpr_script_vars',
			array(
				'recipe_id'               => $recipe_id,
				'ajax_url'                => admin_url( 'admin-ajax.php' ),
				'rpr_video_nonce'         => wp_create_nonce( 'rpr-video-nonce' ),
				'rpr_options_reset_msg'   => __( 'Settings reset to default. Please refresh this page.', 'recipepress-reloaded' ),
				'rpr_options_reset_nonce' => wp_create_nonce( 'rpr-options-reset' ),
				'rpr_update_task_nonce'   => wp_create_nonce( 'update-task-nonce' ),
				'rpr_import_recipes_nonce'   => wp_create_nonce( 'import-recipes-nonce' ),
				'rpr_link_modal_nonce'    => wp_create_nonce( 'rpr-link-modal-nonce' ),
			)
		);

		// Enqueue assets needed on RPR admin pages.
		if ( 'toplevel_page_recipepress-reloaded' === $hook || 'rpr_recipe_page_rpr_extensions' === $hook ) {
			wp_enqueue_code_editor( array( 'type' => 'text/css' ) );
			wp_enqueue_script( 'rpr-minicolors-js', NS\ADMIN_ASSET_URL . 'js/libraries/minicolors.min.js', array( 'jquery' ), '2.2.6', true );
		}
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since       1.0.0
	 *
	 * @return      void
	 */
	public function add_plugin_admin_menu() {

		add_menu_page(
			'Recipepress Reloaded',
			__( 'Recipes', 'recipepress-reloaded' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_plugin_admin_page' )
		);

		add_submenu_page(
			'edit.php?post_type=rpr_recipe',
			'Recipepress Reloaded',
			__( 'Settings', 'recipepress-reloaded' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_plugin_admin_page' )
		);

		remove_menu_page( $this->plugin_name );
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @return void
	 */
	public function display_plugin_admin_page() {

		$tabs        = Definitions::get_tabs();
		$default_tab = Definitions::get_default_tab_slug();
		$active_tab  = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $tabs ) ? $_GET['tab'] : $default_tab; // phpcs:ignore

		include_once plugin_dir_path( __DIR__ ) . 'admin/settings/views/admin-display.php';
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since 1.0.0
	 *
	 * @param array $links The links below the entry on the plugin list field.
	 *
	 * @return array $links
	 */
	public function add_action_links( $links ) {

		$links['settings'] = '<a href="' . admin_url( 'admin.php?page=' . $this->plugin_name ) . '">' . __( 'Settings', 'recipepress-reloaded' ) . '</a>';

		return $links;
	}

	/**
	 * Function to display admin errors.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function admin_notice_handler() {

        $screen = function_exists( 'get_current_screen' ) ? \get_current_screen() : null;
		$errors = \get_option( 'rpr_admin_errors' );

		if ( $screen && 'rpr_recipe' === $screen->id && $errors ) {
			echo '<div class="error"><p>' . esc_html( $errors ) . '</p></div>';
		}

		// Reset the error option for the next error.
		update_option( 'rpr_admin_errors', false );
	}

	/**
	 * Save the recipe.
	 *
	 * Saves the recipe and its associated custom taxonomies and custom metadata.
	 *
	 * @since 1.0.0
	 *
	 * @param int      $recipe_id The current recipe's post ID.
	 * @param \WP_Post $recipe    The recipe post object.
	 *
	 * @return void|int
	 */
	public function save_recipe( $recipe_id, $recipe = null ) {

		$data = $_POST; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( $recipe && 'rpr_recipe' === $recipe->post_type ) {

			remove_action( 'save_post', array( $this, 'save_recipe' ) );

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $recipe_id;
			}

			if ( wp_is_post_revision( $recipe ) ) {
				return $recipe_id;
			}

			if ( wp_is_post_autosave( $recipe ) ) {
				return $recipe_id;
			}

			$errors = false;

			// Check user permissions.
			if ( ! current_user_can( 'edit_post', $recipe_id ) ) {
				$errors = __( 'There was an error saving the recipe. Insufficient user rights.', 'recipepress-reloaded' );
			}

			// If we have an error update the error_option and return.
			if ( $errors ) {
				update_option( 'rpr_admin_errors', $errors );

				return $recipe_id;
			}

			$this->ingredients->save_metabox_metadata( $recipe_id, $data, $recipe );
			$this->instructions->save_metabox_metadata( $recipe_id, $data, $recipe );
			$this->notes->save_metabox_metadata( $recipe_id, $data, $recipe );
			$this->information->save_metabox_metadata( $recipe_id, $data, $recipe );
			$this->nutrition->save_metabox_metadata( $recipe_id, $data, $recipe );
			$this->video->save_metabox_metadata( $recipe_id, $data, $recipe );
			$this->source->save_metabox_metadata( $recipe_id, $data, $recipe );
			$this->equipment->save_metabox_metadata( $recipe_id, $data, $recipe );

			add_action( 'save_post', array( $this, 'save_recipe' ) );

			do_action( 'rpr/save/recipe', $recipe_id, $recipe, $data );
		}

	}

	/**
	 * Enables the sorting and filtering of the admin columns based on custom taxonomies.
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns An array of the columns in the admin reviews page.
	 *
	 * @return array
	 */
	public function sort_admin_columns( $columns ) {

		$registered_taxonomies = get_object_taxonomies( 'rpr_recipe' );
		$registered_taxonomies = array_diff( $registered_taxonomies, array( 'category', 'post_tag' ) );

		foreach ( $registered_taxonomies as $taxonomy ) {
			$columns[ 'taxonomy-' . $taxonomy ] = 'taxonomy-' . $taxonomy;
		}

		return $columns;
	}

	/**
	 * Creates the custom query used to sort admin columns by our custom taxonomies.
	 *
	 * @since 1.0.0
	 *
	 * @param array     $clauses  An array of the SQL statement sent with each WP_Query.
	 * @param \WP_Query $wp_query The WP WP_Query object.
	 *
	 * @return array
	 */
	public function sort_admin_columns_by_taxonomy( $clauses, $wp_query ) {

		global $wpdb;

		$screen = function_exists( 'get_current_screen' ) ? \get_current_screen() : null;

		if ( ( $screen && 'edit-rpr_recipe' === $screen->id ) && is_post_type_archive( 'rpr_recipe' ) && is_blog_admin() ) {
			if ( isset( $wp_query->query['orderby'] ) && false !== strpos( (string) $wp_query->query['orderby'], 'taxonomy-rpr_' ) ) {

				$taxonomy = str_replace( 'taxonomy-', '', $wp_query->query['orderby'] );

				$clauses['join']   .= <<<SQL
									LEFT OUTER JOIN {$wpdb->term_relationships} ON {$wpdb->posts}.ID={$wpdb->term_relationships}.object_id
									LEFT OUTER JOIN {$wpdb->term_taxonomy} USING (term_taxonomy_id)
									LEFT OUTER JOIN {$wpdb->terms} USING (term_id)
SQL;
				$clauses['where']  .= "AND (taxonomy = '" . $taxonomy . "' OR taxonomy IS NULL)";
				$clauses['groupby'] = 'object_id';
				$clauses['orderby'] = "GROUP_CONCAT({$wpdb->terms}.name ORDER BY name ASC)";

				if ( 'ASC' === strtoupper( $wp_query->get( 'order' ) ) ) {
					$clauses['orderby'] .= 'ASC';
				} else {
					$clauses['orderby'] .= 'DESC';
				}
			}
		}

		return $clauses;
	}

	/**
	 * Adds a filter drop-down to recipes admin column
	 *
	 * Adds all taxonomy terms to drop-down list used to filter all recipes in
	 * admin column.
	 *
	 * @since 1.0.0
	 *
	 * @uses \wp_dropdown_categories()
	 *
	 * @return void
	 */
	public function filter_post_type_by_taxonomy() {

		global $typenow;
		$taxonomies = Options::get_option( 'rpr_taxonomy_selection' );
		$taxonomies = explode( ',', $taxonomies );

		if ( 'rpr_recipe' === $typenow ) {
			foreach ( $taxonomies as $taxonomy ) {
				$taxonomy = 'rpr_' . $this->sanitize_input( $taxonomy );
				if ( Options::get_option( $taxonomy . '_filter' ) ) {
					$selected      = isset( $_GET[ $taxonomy ] ) ? $_GET[ $taxonomy ] : ''; // phpcs:ignore
					$info_taxonomy = get_taxonomy( $taxonomy );
					wp_dropdown_categories(
						array(
							// translators: "All courses".
							'show_option_all' => sprintf( __( 'All %s', 'recipepress-reloaded' ), $info_taxonomy->label ),
							'taxonomy'        => $taxonomy,
							'name'            => $taxonomy,
							'orderby'         => 'name',
							'selected'        => $selected,
							'show_count'      => true,
							'hide_if_empty'   => true,  // The dropdown only displays if at least 1 recipe is using the taxonomy.
						)
					);
				}
			}
		}

	}

	/**
	 * Converts term IDs to terms?
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Query $query The global query object.
	 *
	 * @return void
	 */
	public function convert_id_to_term_in_query( $query ) {

		global $pagenow;
		$taxonomies = Options::get_option( 'rpr_taxonomy_selection' );
		$taxonomies = explode( ',', $taxonomies );

		foreach ( $taxonomies as $taxonomy ) {
			$q_vars   = &$query->query_vars;
			$taxonomy = 'rpr_' . $this->sanitize_input( $taxonomy );
			if ( 'edit.php' === $pagenow && isset( $q_vars['post_type'], $q_vars[ $taxonomy ] )
				&& 'rpr_recipe' === $q_vars['post_type']
				&& is_numeric( $q_vars[ $taxonomy ] )
				&& 0 !== $q_vars[ $taxonomy ] ) {
				$term = get_term_by( 'id', $q_vars[ $taxonomy ], $taxonomy );
				if ( $term ) {
					$q_vars[ $taxonomy ] = $term->slug;
				}
			}
		}
	}

	/**
	 * Add links for social media profiles to user admin page
	 *
	 * @since 1.0.0
	 *
	 * @param array $contact_methods Array of social media links.
	 *
	 * @return array
	 */
	public function user_social_profiles( $contact_methods ) {

        $contact_methods['rpr_twitter']   = __( 'Twitter URL', 'recipepress-reloaded' );
        $contact_methods['rpr_facebook']  = __( 'Facebook URL', 'recipepress-reloaded' );
        $contact_methods['rpr_yummly']    = __( 'Yummly URL', 'recipepress-reloaded' );
        $contact_methods['rpr_linkedin']  = __( 'Linkedin URL', 'recipepress-reloaded' );
        $contact_methods['rpr_pinterest'] = __( 'Pinterest URL', 'recipepress-reloaded' );
        $contact_methods['rpr_youtube']   = __( 'Youtube URL', 'recipepress-reloaded' );
        $contact_methods['rpr_instagram'] = __( 'Instagram URL', 'recipepress-reloaded' );

		return $contact_methods;
	}

	/**
	 * Adds new column to the WP admin users table
	 *
	 * @since 1.0.0
	 *
	 * @param array $column The columns of the users' admin column
	 *
	 * @return array
	 */
	public function modify_user_table( $column ) {

		$column['recipes'] = __( 'Recipes', 'recipepress-reloaded' );

		return $column;
	}

	/**
	 * Adds a count of published recipes for a user
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $val         The value of the table cell
	 * @param string $column_name The column name
	 * @param int    $user_id     The user ID
	 *
	 * @return int|string
	 */
	public function modify_user_table_row( $val, $column_name, $user_id ) {

		$count = 0;
		$num_recipes = count_user_posts( $user_id, 'rpr_recipe' );

		if ( $num_recipes ) {

			$number = number_format_i18n( $num_recipes );
			$post_type = get_post_type_object( 'rpr_recipe' );

			if ( $post_type && current_user_can( $post_type->cap->edit_posts ) ) {
				$count = sprintf( '<a class="%1$s-count" href="edit.php?post_type=%1$s&author=%2$s">%3$s</a>', 'rpr_recipe', $user_id, $number ) . "\n";
			} else {
				$count = sprintf( '<span class="%1$s-count">%2$s</span>', 'rpr_recipe', $number ) . "\n";
			}
		}

		if ( 'recipes' === $column_name ) {

			return $count;
		}

		return $val;
	}

	/**
	 * Removes unnecessary metaboxes from the recipe edit screen
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function remove_metaboxes(  ) {

		// Removes the "custom fields" metabox from the recipe edit screen
		if ( apply_filters( 'rpr_remove_custom_fields_metabox', true ) ) {
			remove_meta_box( 'postcustom', 'rpr_recipe', 'normal' );
		}

		// Removes the "editor" metabox from the recipe edit screen
		if ( apply_filters( 'rpr_remove_switch_editor_metabox', false ) ) {
			remove_meta_box( 'classic-editor-switch-editor', 'rpr_recipe', 'normal' );
		}
	}

	/**
	 * Moves old settings to new settings
	 *
	 * When updating from 0.11.0 to 1.0.0 the settings are empty
	 * because the activator is not run on updates.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function old_settings_new_settings() {

		$old_ver_num  = get_option( 'rpr_version' );
		$old_options  = get_option( 'rpr_options' );
		$new_options  = get_option( 'recipepress_settings', array() );

		if ( '1.0.0' !== $old_ver_num && empty( $new_options ) ) {

			// Set the general tab.
			$new_options['rpr_recipe_slug']         = $old_options['general']['slug'];
			$new_options['rpr_recipes_on_homepage'] = $old_options['general']['homepage_display'];
			$new_options['rpr_recipes_archive']     = $old_options['general']['archive_display'];

			// Set the taxonomies tab.
			$selection = array();
			foreach ( $old_options['tax_custom'] as $k => $v ) {
				$slug                                                  = $v['slug'];
				$new_options[ 'rpr_' . $slug . '_label' ]              = $v['tab_title'];
				$new_options[ 'rpr_' . $slug . '_labels' ]['singular'] = $v['tab_title'];
				$new_options[ 'rpr_' . $slug . '_labels' ]['plural']   = $v['tab_title'];
				$new_options[ 'rpr_' . $slug . '_slug' ]               = $v['slug'];
				$new_options[ 'rpr_' . $slug . '_hierarchical' ]       = $v['hierarchical'];
				$new_options[ 'rpr_' . $slug . '_show' ]               = $v['table'];
				$new_options[ 'rpr_' . $slug . '_filter' ]             = $v['filter'];
				$new_options[ 'rpr_' . $slug . '_show_front' ]         = '1';
				$selection[]                                           = $v['tab_title'];
			}
			$new_options['rpr_taxonomy_selection'] = implode( ',', $selection );
			$new_options['rpr_enable_categories']  = $old_options['tax_builtin']['category']['use'];
			$new_options['rpr_enable_tags']        = $old_options['tax_builtin']['post_tag']['use'];

			// Set the ingredients tab.
			$new_options['rpr_ingredient_label']         = $old_options['tax_builtin']['ingredients']['singular'];
			$new_options['rpr_ingredient_slug']          = $old_options['tax_builtin']['ingredients']['slug'];
			$new_options['rpr_ingredient_links']         = $old_options['tax_builtin']['ingredients']['link_target'];
			$new_options['rpr_ingredient_separator']     = $old_options['tax_builtin']['ingredients']['comment_sep'];
			$new_options['rpr_ingredient_pluralization'] = $old_options['tax_builtin']['ingredients']['auto_plural'];

			// Set the units tab.
			$new_options['rpr_use_ingredient_unit_list'] = $old_options['units']['use_ingredient_units'];
			$new_options['rpr_use_serving_unit_list']    = $old_options['units']['use_serving_units'];
			$new_options['rpr_ingredient_unit_list']     = implode( ',', $old_options['units']['ingredient_units'] );
			$new_options['rpr_serving_unit_list']        = implode( ',', $old_options['units']['serving_units'] );

			// Set the metadata tab.
			$new_options['rpr_use_source_meta']      = $old_options['metadata']['use_source'];
			$new_options['rpr_use_nutritional_meta'] = $old_options['metadata']['use_nutritional_data'];

			// Set the appearance tab.
			$template                                         = $old_options['layout_general']['layout'];
			$new_options['rpr_recipe_template']               = $template;
			$new_options['rpr_recipe_template_print_btn']     = $old_options['layout_general']['print_button_link'];
			$new_options['rpr_recipe_template_inst_image']    = $old_options['layout_general']['images_instr_pos'];
			$new_options['rpr_recipe_template_click_img']     = $old_options['layout_general']['print_button_link'];
			$new_options['rpr_recipe_template_use_icons']     = $old_options['layout'][ $template ]['icon_display'];
			$new_options['rpr_recipe_template_print_area']    = $old_options['layout'][ $template ]['printlink_class'];
			$new_options['rpr_recipe_template_no_print_area'] = $old_options['layout'][ $template ]['no_printlink_class'];

			update_option( 'recipepress_settings', $new_options );
		}

	}

	/**
	 * Displays an inline product update message
	 *
	 * @since 1.0.0
	 *
	 * @see https://wisdomplugin.com/add-inline-plugin-update-message/
	 *
	 * @param array $data     An array of plugin metadata
	 * @param array $response An array of metadata about the available plugin update
	 *
	 * @return void
	 */
	public function plugin_update_message( $data, $response ) {
		if ( isset( $data['upgrade_notice'] ) ) {
			printf(
				'<div class="update-message">%s</div>',
				wpautop( esc_html( $data['upgrade_notice'] ) )
			);
		} elseif ( '1.0.0' === $data['new_version']
				   && ( version_compare( $data['Version'], '1.0.0', '<=' ) ) ) {
			printf(
				'<div class="rpr update-message"><ul><li>%s</li></ul></div>',
				__( 'This is a major update of the Recipepress Reloaded plugin. Please backup your website before installing this update.', 'recipepress-reloaded' )
			);
		}
	}

	/**
	 * Redirect to welcome page on activation
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function welcome_screen_activation_redirect() {
		// Bail if no activation redirect
		if ( ! get_transient( '_rpr_welcome_screen_activation_redirect' ) ) {
			return;
		}

		// Delete the redirect transient
		delete_transient( '_rpr_welcome_screen_activation_redirect' );

		// Bail if activating from the network, or bulk
		if ( isset( $_GET['activate-multi'] ) || is_network_admin()  ) {
			return;
		}

		// Redirect to RPR about page
		wp_safe_redirect( add_query_arg( array( 'page' => 'rpr-welcome-screen' ), admin_url( 'index.php' ) ) );
	}

	/**
	 * Add a welcome page
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function welcome_screen_pages() {
		add_dashboard_page(
			'Welcome to Recipepress Reloaded',
			'Welcome',
			'read',
			'rpr-welcome-screen',
			array( $this, 'welcome_screen_content' )
		);
	}

	/**
	 * Get welcome page's content
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function welcome_screen_content() {
		include_once NS\ADMIN_DIR . 'views/rpr-welcome-screen.php';
	}

	/**
	 * Remove welcome page from menus
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function welcome_screen_remove_menus() {
		remove_submenu_page( 'index.php', 'rpr-welcome-screen' );
	}

	/**
	 * Add option to enable or disable Gutenberg support.
	 *
	 * @since 1.0.0
	 *
	 * @param bool   $use_block_editor
	 * @param string $post_type
	 *
	 * @return bool
	 */
	public function enable_gutenberg_support( $use_block_editor, $post_type ) {

		if ( 'rpr_recipe' === $post_type ) {

			return (bool) Options::get_option( 'rpr_recipes_in_gutenberg' );
		}

		return $use_block_editor;
	}

    /**
     * Display the recipe ID in the WP admin recipe table
     *
     * @param array    $actions
     * @param \WP_Post $recipe
     *
     * @return array
     */
    public function show_recipe_id( $actions, $recipe ) {

        if ( 'rpr_recipe' === $recipe->post_type && current_user_can( 'edit_posts' ) ) {
            $actions = array( 'rpr_id' => 'ID: ' .  $recipe->ID ) + $actions;
        }

        return $actions;
    }

	/**
	 * Flush permalinks by checking if updating a setting sets a
	 * `rpr_flush_rewrite_rules` flag to `true` in the `wp_options` table
	 *
	 * @since 1.0.0
	 *
	 * @see Settings::flush_permalinks_on_update()
	 *
	 * @return void
	 */
	public function plugin_settings_update_flush_rewrite() {
		if ( get_option( 'rpr_flush_rewrite_rules' ) ) {
			flush_rewrite_rules();
			update_option( 'rpr_flush_rewrite_rules', false, true );
		}
	}

    /**
     * Adds recipe to the list of drafts in the 'Recent Drafts' dashboard widget
     *
     * @since 2.0.0
     *
     * @param array $args The query arguments for the 'Recent Drafts' dashboard widget.
     *
     * @return array
     */
    public function add_recipes_to_drafts_widget( $args ) {
        $args['post_type'] = array( 'post', 'rpr_recipe' );

        return $args;
    }

	/**
	 * Adds a word count to the post publish section
	 *
	 * @since 2.1.0
	 *
	 * @param \WP_Post $post
	 *
	 * @return void
	 */
	public function add_total_recipe_word_count( $post ) {
		$out = '';

		if ( $post && 'rpr_recipe' === $post->post_type ) {
			$out .= '<div class="rpr-total-word-count" ';
			$out .= 'data-controller="rpr-wordcount" ';
			$out .= 'data-action="mouseover->rpr-wordcount#updateCounter" ';
			// $out .= 'data-action="heartbeat-tick@window->rpr-wordcount#updateCounter" ';
			$out .= 'style="padding:6px 10px 8px" ';
			$out .= '>';
			$out .= '<span class="dashicons dashicons-editor-spellcheck" style="margin:0 6px 0 0"></span>';
			$out .= __( 'Total word count', 'recipepress-reloaded');
			$out .= ': ';
			$out .= '<span data-rpr-wordcount-target="wordCount" style="font-weight:600">&hellip;</span>';
			$out .= '</div>';
		}

		echo $out;
	}


}
