<?php

namespace Recipepress\Inc\Core;

use Recipepress as NS;
use Recipepress\Inc\Admin;
use Recipepress\Inc\Admin\Rest\Recipes;
use Recipepress\Inc\Frontend;
use Recipepress\Inc\Admin\Settings\Callbacks;
use Recipepress\Inc\Admin\Settings\Sanitization;
use Recipepress\Inc\Admin\Settings\Settings;
use Recipepress\Inc\Admin\Settings\Metaboxes;
use Recipepress\Inc\Blocks\Blocks;

/**
 * The core plugin class.
 * Defines internationalization, admin-specific hooks, and public-facing site hooks.
 *
 * @link       https://wzymedia.com
 * @since      1.0.0
 *
 * @author     Kemory Grubb
 */
class Init {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @var      Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_base_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_basename;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * The plugin name.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string  $plugin_name The plugin name.
	 */
	protected $plugin_name;

	/**
	 * The text domain of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $plugin_text_domain;

	/**
	 * Initialize and define the core functionality of the plugin.
	 *
	 * @throws \Exception
	 */
	public function __construct() {

		$this->plugin_name        = NS\PLUGIN_NAME;
		$this->version            = NS\PLUGIN_VERSION;
		$this->plugin_basename    = NS\PLUGIN_BASENAME;
		$this->plugin_text_domain = NS\PLUGIN_TEXT_DOMAIN;

		$this->load_dependencies();
		$this->set_locale();

		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_block_hooks();

		// $this->define_rest_hooks();

		$this->define_asset_hooks();
		$this->define_rating_hooks();
		$this->load_extensions();
		$this->scheduled_tasks();
		$this->background_tasks();

		// $this->recipe_importers();

		$this->third_party_integrations();

		$this->define_shortcode_hooks();
		$this->define_widget_hooks();
	}

	/**
	 * Loads the following required dependencies for this plugin.
	 *
	 * - Loader - Orchestrates the hooks of the plugin.
	 * - Internationalization_I18n - Defines internationalization functionality.
	 * - Admin - Defines all hooks for the admin area.
	 * - Frontend - Defines all hooks for the public side of the site.
	 *
	 * @access    private
	 */
	private function load_dependencies() {
		$this->loader = new Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Internationalization_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @access    private
	 */
	private function set_locale() {

		$plugin_i18n = new Internationalization( $this->plugin_text_domain );

		$this->loader->add_action( 'init', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @access    private
	 * @throws \Exception
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Admin\Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init', $plugin_admin->recipe, 'register_post_type' );
		$this->loader->add_action( 'init', $plugin_admin->ingredient, 'register_taxonomy' );
		$this->loader->add_action( 'init', $plugin_admin->custom_taxonomies, 'register_taxonomy' );
		$this->loader->add_action( 'init', $plugin_admin->keywords, 'register_taxonomy' );
		$this->loader->add_action( 'init', $plugin_admin->equipment->taxonomy, 'register_taxonomy' );

		// Registers our custom metadata with the WP REST API
		$this->loader->add_action( 'init', $plugin_admin->ingredients, 'register_meta' );
		$this->loader->add_action( 'init', $plugin_admin->instructions, 'register_meta' );
		$this->loader->add_action( 'init', $plugin_admin->notes, 'register_meta' );

		// Flush permalink if the flag is set to `true`.
		$this->loader->add_action( 'init', $plugin_admin, 'plugin_settings_update_flush_rewrite' );

		// TODO: Move this to run when visting a specific URL
		// $this->loader->add_action( 'plugins_loaded', $plugin_admin, 'old_settings_new_settings' );

		// Creates date archive rewrite rules.
		$this->loader->add_action( 'generate_rewrite_rules', $plugin_admin->recipe, 'date_archives_rewrite_rules' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );

		// Welcome screen
		$this->loader->add_action( 'admin_init', $plugin_admin, 'welcome_screen_activation_redirect' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'welcome_screen_pages' );
		$this->loader->add_action( 'admin_head', $plugin_admin, 'welcome_screen_remove_menus' );

		// Display error messages.
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'admin_notice_handler' );

		// Plugin action links.
		$this->loader->add_filter( 'plugin_action_links_' . $this->plugin_basename, $plugin_admin, 'add_action_links' );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$settings_callback     = new Callbacks( $this->plugin_name, $this->get_version() );
		$settings_sanitization = new Sanitization( $this->plugin_name, $this->get_version() );
		$plugin_settings       = new Settings( $this->get_plugin_name(), $this->get_version(), $settings_callback, $settings_sanitization );
		$this->loader->add_action( 'admin_init', $plugin_settings, 'register_settings' );
		$this->loader->add_action( 'init' , $plugin_settings, 'set_settings' );

		// Flush permalinks after an important slug has been updated. Action fired in the Sanitization class.
		$this->loader->add_action( 'recipepress_settings_on_change_general_tab', $plugin_settings, 'flush_permalinks_on_update' );
		$this->loader->add_action( 'recipepress_settings_on_change_ingredient_tab', $plugin_settings, 'flush_permalinks_on_update' );
		$this->loader->add_action( 'recipepress_settings_on_change_taxonomy_tab', $plugin_settings, 'flush_permalinks_on_update' );

		$metaboxes = new Metaboxes( $this->get_plugin_name() );
		$this->loader->add_action( 'init' , $metaboxes, 'set_tabs' );
		$this->loader->add_action( 'load-toplevel_page_' . $this->get_plugin_name(), $metaboxes, 'add_meta_boxes' );

		$this->loader->add_action( 'save_post', $plugin_admin, 'save_recipe', 10, 2 );

		$this->loader->add_action( 'do_meta_boxes', $plugin_admin, 'remove_metaboxes' );
		$this->loader->add_action( 'do_meta_boxes', $plugin_admin->ingredients, 'add_metabox' );
		$this->loader->add_action( 'do_meta_boxes', $plugin_admin->instructions, 'add_metabox' );
		$this->loader->add_action( 'do_meta_boxes', $plugin_admin->notes, 'add_metabox' );
		$this->loader->add_action( 'do_meta_boxes', $plugin_admin->information, 'add_metabox' );
		$this->loader->add_action( 'do_meta_boxes', $plugin_admin->nutrition, 'add_metabox' );
		$this->loader->add_action( 'do_meta_boxes', $plugin_admin->video, 'add_metabox' );
		$this->loader->add_action( 'do_meta_boxes', $plugin_admin->source, 'add_metabox' );
		$this->loader->add_action( 'do_meta_boxes', $plugin_admin->equipment, 'add_metabox' );

        $this->loader->add_action( 'admin_footer', $plugin_admin->link, 'add_link_modal' );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin->ingredients, 'enqueue_assets' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin->instructions, 'enqueue_assets' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin->information, 'enqueue_assets' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin->nutrition, 'enqueue_assets' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin->video, 'enqueue_assets' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin->source, 'enqueue_assets' );

		// Handles adding and saving metadata to the ingredient taxonomy.
		$this->loader->add_action( 'rpr_ingredient_edit_form', $plugin_admin->ingredient, 'edit_rpr_ingredients_taxonomy_custom_fields' );
		$this->loader->add_action( 'rpr_ingredient_add_form_fields', $plugin_admin->ingredient, 'new_rpr_ingredients_taxonomy_custom_fields' );
		$this->loader->add_action( 'edited_rpr_ingredient', $plugin_admin->ingredient, 'save_rpr_ingredients_taxonomy_custom_fields' );
		$this->loader->add_action( 'create_rpr_ingredient', $plugin_admin->ingredient, 'save_rpr_ingredients_taxonomy_custom_fields' );

		// Handles setting a default term for each taxonomy
		$this->loader->add_action( 'admin_menu', $plugin_admin->custom_taxonomies, 'default_term_checkbox' );
		$this->loader->add_action( 'edited_term', $plugin_admin->custom_taxonomies, 'save_default_taxonomy', 10, 3 );
		$this->loader->add_filter( 'term_name', $plugin_admin->custom_taxonomies, 'default_term_marker', 99, 2 );

		// Add messages on the recipe editor screen.
		$this->loader->add_filter( 'post_updated_messages', $plugin_admin->recipe, 'updated_messages' );

		// Add recipes to 'At a Glance' widget.
		$this->loader->add_filter( 'dashboard_glance_items', $plugin_admin->recipe, 'add_recipes_to_glance_items' );
		$this->loader->add_filter( 'dashboard_glance_items', $plugin_admin->recipe, 'add_ratings_to_glance_items' );

		// Add recipes to Recent Activity widget.
		$this->loader->add_filter( 'dashboard_recent_posts_query_args', $plugin_admin->recipe, 'add_to_dashboard_recent_posts_widget' );

		// Display error messages.
		$this->loader->add_action( 'admin_notices', $plugin_admin->recipe, 'admin_notice_handler' );

		// Private AJAX actions.
		$this->loader->add_action( 'wp_ajax_fetch_video_data', $plugin_admin->video, 'fetch_video_data' );
		$this->loader->add_action( 'wp_ajax_reset_all_options', $plugin_admin, 'reset_all_options' );

        $this->loader->add_action( 'wp_ajax_latest_recipes_posts', $plugin_admin->link, 'latest_recipes_posts' );
        $this->loader->add_action( 'wp_ajax_search_recipes_posts', $plugin_admin->link, 'search_recipes_posts' );

		$this->loader->add_action( 'request', $plugin_admin->recipe, 'add_post_type_to_rss_feed' );

		// Adds sorting of recipes by the taxonomy in the posts' admin column.
		$this->loader->add_filter( 'manage_edit-rpr_recipe_sortable_columns', $plugin_admin, 'sort_admin_columns' );
		$this->loader->add_filter( 'posts_clauses', $plugin_admin, 'sort_admin_columns_by_taxonomy', 10, 2 );

		// Adds taxonomy filter drop-downs to the post admin column.
		$this->loader->add_filter( 'restrict_manage_posts', $plugin_admin, 'filter_post_type_by_taxonomy' );
		$this->loader->add_filter( 'parse_query', $plugin_admin, 'convert_id_to_term_in_query' );

		// Add links for social media profiles to user admin page.
		$this->loader->add_filter( 'user_contactmethods', $plugin_admin, 'user_social_profiles' );

		// Adds a new column to the WP admin users table to add recipe count per user.
		$this->loader->add_filter( 'manage_users_columns', $plugin_admin, 'modify_user_table' );
		$this->loader->add_filter( 'manage_users_custom_column', $plugin_admin, 'modify_user_table_row', 10, 3 );

		// Enable or disable Gutenberg support.
		$this->loader->add_filter( 'use_block_editor_for_post_type', $plugin_admin, 'enable_gutenberg_support', 10, 2 );

		// Additional plugin update message
		$this->loader->add_filter( 'in_plugin_update_message-recipepress-reloaded/recipe-press-reloaded.php', $plugin_admin, 'plugin_update_message', 10, 2 );

		// $this->loader->add_action( 'rpr_recipe_save_post', $plugin_admin->custom_taxonomies, 'save_default_taxonomy_terms', 10, 3 );

		// Removes items from the Gutenberg sidebar
		$this->loader->add_filter( 'rest_prepare_taxonomy', $plugin_admin->ingredient, 'remove_ingredients_gutenberg', 10, 3 );
		$this->loader->add_filter( 'rest_prepare_taxonomy', $plugin_admin->equipment, 'remove_equipment_gutenberg', 10, 3 );

        $this->loader->add_filter( 'post_row_actions', $plugin_admin, 'show_recipe_id', 10, 2 );

        $this->loader->add_filter( 'dashboard_recent_drafts_query_args', $plugin_admin, 'add_recipes_to_drafts_widget' );


        $this->loader->add_action( 'post_submitbox_misc_actions', $plugin_admin, 'add_total_recipe_word_count', 99 );
	}

	/**
	 * Register all the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @access    private
	 */
	private function define_public_hooks() {

		$plugin_public = new Frontend\Frontend( $this->get_plugin_name(), $this->get_version() );

        // Enqueue the scripts and stylesheets
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// Manipulate the query to include recipes to home page (if set).
		$this->loader->add_action( 'pre_get_posts', $plugin_public->recipe, 'custom_post_type_query' );

		// Get the rendered content of a recipe and forward it to the theme as `the_content()`.
		$this->loader->add_filter( 'the_content', $plugin_public->recipe, 'get_the_post_type_content' );

		// Enqueue the ratings scripts and stylesheets
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public->rating, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public->rating, 'enqueue_scripts' );

		$this->loader->add_action( 'wp_loaded', $plugin_public, 'include_functions_file' );
		$this->loader->add_filter( 'post_class', $plugin_public, 'cleanup_post_class', 99, 3 );

		// Add the `defer` tag to our scripts.
		$this->loader->add_filter( 'script_loader_tag', $plugin_public, 'add_defer_script_tag', 10, 3 );

		// Replace the oEmbed markup for YouTube videos.
		$this->loader->add_filter( 'embed_oembed_html', $plugin_public, 'speedup_youtube_oembed', 99, 4 );
	}

	/**
	 * Register hooks for the Gutenberg block editor integration.
	 *
	 * @access    private
	 */
	private function define_block_hooks() {
		$blocks = new Blocks( $this->get_plugin_name(), $this->get_version() );
		$blocks->register_hooks();
	}

	/**
	 * Register all the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @access    private
	 */
	private function define_rest_hooks() {

		$rest_recipes = new Recipes( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'rest_api_init', $rest_recipes, 'register_routes' );
	}

	/**
	 * Register all the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @access    private
	 */
	private function define_asset_hooks() {
		$asset_manager = new Assets( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $asset_manager, 'enqueue_all' );
		$this->loader->add_action( 'admin_enqueue_scripts', $asset_manager, 'enqueue_all' );
	}

	/**
	 * Register all the hooks related to the public recipe rating functionality
	 * of the plugin.
	 *
	 * @see https://wordpress.stackexchange.com/questions/82317/show-custom-comment-fields-when-editing-in-admin
	 *
	 * @access    private
	 */
	private function define_rating_hooks() {

		$rating = new Frontend\Rating( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'wp_enqueue_scripts', $rating, 'inline_styles' );

		// Create on comment submit.
		$this->loader->add_action( 'wp_insert_comment', $rating, 'add_comment_karma', 10, 2 );

		// Add rating for logged in and non-logged in users.
		$this->loader->add_action( 'comment_form_before_fields', $rating, 'comment_rating_form' );
		$this->loader->add_action( 'comment_form_logged_in_after', $rating, 'comment_rating_form' );

		// Display rating on a comment.
		$this->loader->add_filter( 'comment_text', $rating, 'display_comment_rating', 9 );

		// Add metabox with rating to comment edit page.
		$this->loader->add_filter( 'add_meta_boxes', $rating, 'add_metabox' );

		// Save the updated comment rating on the comment edit page.
		$this->loader->add_filter( 'comment_edit_redirect', $rating, 'save_comment_rating', 10, 2 );

		// Add a rating column and display the comment rating information.
		$this->loader->add_filter( 'manage_edit-comments_columns', $rating, 'add_comment_rating_column' );
		$this->loader->add_filter( 'manage_comments_custom_column', $rating, 'add_rating_to_column', 10, 2 );

		// Add a rating column and display the recipe rating information.
		$this->loader->add_filter( 'manage_rpr_recipe_posts_columns', $rating, 'add_rating_posts_columns' );
		$this->loader->add_action( 'manage_rpr_recipe_posts_custom_column', $rating, 'add_rating_posts_column', 10, 2 );
		$this->loader->add_action( 'manage_edit-rpr_recipe_sortable_columns', $rating, 'sort_by_recipe_rating' );
		$this->loader->add_action( 'pre_get_posts', $rating, 'order_by_recipe_rating' );

		// Display the ratings on recipes
		// $this->loader->add_action( 'the_post', $rating, 'filter_recipe_title', 10, 2 );
	}

	/**
	 * Get and load all the extra extensions
	 *
	 * @since  1.0.0
	 *
	 * @access private
	 */
	private function load_extensions() {

		$extensions = new Extensions( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_menu', $extensions, 'add_extensions_page' );
		$this->loader->add_action( 'admin_enqueue_scripts', $extensions, 'extension_admin_scripts' );
		$this->loader->add_action( 'wp_ajax_rpr_activate_extension_ajax', $extensions, 'rpr_activate_extension_ajax' );
		$this->loader->add_action( 'wp_ajax_rpr_deactivate_extension_ajax', $extensions, 'rpr_deactivate_extension_ajax' );
		$this->loader->add_action( 'init', $extensions, 'load_extensions', 99 );
	}

	/**
	 * Get and load all scheduled tasks
	 *
	 * @since  1.0.0
	 *
	 * @access private
	 */
	private function scheduled_tasks() {

		$schedules = new Schedules( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'cron_schedules', $schedules, 'create_weekly_cron' );
		$this->loader->add_action( 'wp_loaded', $schedules, 'schedule_api_ping' );
		$this->loader->add_action( 'rpr_ping_youtube_api', $schedules, 'ping_api' );
	}

	/**
	 * Get and load all background tasks
	 *
	 * @since  1.0.0
	 *
	 * @access private
	 */
	private function background_tasks() {

		$tasks = new Background_Tasks( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_loaded', $tasks, 'load_background_tasks' );
		$this->loader->add_action( 'wp_ajax_run_background_tasks', $tasks, 'run_background_tasks' );

		// Display update notices.
		$this->loader->add_action( 'admin_notices', $tasks, 'update_notice_handler' );
	}

	/**
	 * Get and load all recipe importers
	 *
	 * @since  2.2.0
	 *
	 * @access private
	 */
	private function recipe_importers() {

		$importers = new Importers( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_ajax_run_recipe_importers', $importers, 'run_recipe_importers' );
	}

	/**
	 * Register any required integrations with other plugins
	 *
	 * @since  2.1.0
	 *
	 * @access private
	 */
	private function third_party_integrations() {
		$integrations = new Integrations( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'wpseo_schema_graph_pieces', $integrations, 'add_metadata_wpseo_graph', 11, 2 );
	}

	/**
	 * Register our shortcodes.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function define_shortcode_hooks() {

		$shortcodes = new Shortcodes( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_shortcode( 'rpr-recipe', $shortcodes->recipe, 'the_shortcode' );
		$this->loader->add_shortcode( 'rpr-index', $shortcodes->index, 'the_shortcode' );
		$this->loader->add_shortcode( 'rpr-tax-list', $shortcodes->tax_list, 'the_shortcode' );
		$this->loader->add_shortcode( 'rpr-list', $shortcodes->rpr_list, 'the_shortcode' );
		$this->loader->add_shortcode( 'rpr-ingredients', $shortcodes->ingredients, 'the_shortcode' );
		$this->loader->add_shortcode( 'rpr-collection', $shortcodes->rpr_collection, 'the_shortcode' );
		$this->loader->add_shortcode( 'rpr-filterable', $shortcodes->rpr_filterable, 'the_shortcode' );
		$this->loader->add_shortcode( 'rpr-ratings', $shortcodes->ratings, 'the_shortcode' );

        $this->loader->add_action( 'wp_ajax_more_filtered_recipes', $shortcodes->rpr_filterable, 'more_filtered_recipes' );
        $this->loader->add_action( 'wp_ajax_nopriv_more_filtered_recipes', $shortcodes->rpr_filterable, 'more_filtered_recipes' );

		/** @deprecated */
		$this->loader->add_shortcode( 'rpr-recipe-index', $shortcodes->index, 'the_shortcode' );

		// Enqueues scripts used by our shortcodes.
		$this->loader->add_action( 'wp_enqueue_scripts', $shortcodes, 'add_shortcode_scripts' );

		// Adds the "Add Recipe" button above the classic post editor.
		$this->loader->add_action( 'media_buttons', $shortcodes, 'editor_add_recipe_button' );
		$this->loader->add_action( 'in_admin_footer', $shortcodes, 'add_recipe_button_modal' );
		$this->loader->add_action( 'admin_enqueue_scripts', $shortcodes, 'add_recipe_button_ajax_scripts' );
		$this->loader->add_action( 'wp_ajax_rpr_get_results', $shortcodes, 'process_add_recipe_button_ajax' );

		// Adds the "Add List" button above the classic post editor.
		$this->loader->add_action( 'media_buttons', $shortcodes->rpr_list, 'editor_add_button' );
		$this->loader->add_action( 'in_admin_footer', $shortcodes->rpr_list, 'editor_add_modal' );
		$this->loader->add_action( 'admin_enqueue_scripts', $shortcodes->rpr_list, 'add_button_scripts' );
		$this->loader->add_action( 'wp_ajax_rpr_get_list', $shortcodes->rpr_list, 'process_button_ajax' );
	}

	/**
	 * Register our shortcodes.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function define_widget_hooks() {

		$widget = new Widgets( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'widgets_init', $widget->calendar, 'register_widget' );
		$this->loader->add_action( 'widgets_init', $widget->tag_cloud, 'register_widget' );
		$this->loader->add_action( 'widgets_init', $widget->tax_list, 'register_widget' );
		$this->loader->add_action( 'widgets_init', $widget->author_profile, 'register_widget' );
		$this->loader->add_action( 'widgets_init', $widget->recipe_filter, 'register_widget' );
		$this->loader->add_action( 'widgets_init', $widget->recent_rating, 'register_widget' );
		$this->loader->add_action( 'widgets_init', $widget->recent_recipes, 'register_widget' ); // TODO: Finish up.
		$this->loader->add_filter( 'query_vars', $widget->recipe_filter, 'add_query_vars' );
		$this->loader->add_filter( 'pre_get_posts', $widget->recipe_filter, 'custom_recipe_archive' );

        // Purge RPR calendar widget cache.
        $this->loader->add_action( 'save_post', $widget, 'delete_calendar_cache' );
        $this->loader->add_action( 'delete_post', $widget, 'delete_calendar_cache' );
        $this->loader->add_action( 'update_option_start_of_week', $widget, 'delete_calendar_cache' );
        $this->loader->add_action( 'update_option_gmt_offset', $widget, 'delete_calendar_cache' );
	}

	/**
	 * Run the loader to execute all the hooks with WordPress.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Retrieve the text domain of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The text domain of the plugin.
	 */
	public function get_plugin_text_domain() {
		return $this->plugin_text_domain;
	}

}
