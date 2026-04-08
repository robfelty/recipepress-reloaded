<?php


namespace Recipepress\Inc\Frontend;

defined( 'ABSPATH' ) || exit;

use Recipepress as NS;
use Recipepress\Inc\Admin\PostTypes\Recipe;
use Recipepress\Inc\Core\Options;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @link       https://wzymedia.com
 * @since      1.0.0
 *
 * @author    Kemory Grubb
 */
class Frontend {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The text domain of this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @var      string    $plugin_text_domain    The text domain of this plugin.
	 */
	private $plugin_text_domain;

	/**
	 * Instance of the Posttype\Recipe class.
	 *
	 * @since  1.0.0
	 *
	 * @var    Recipe $recipe
	 */
	public $recipe;

	/**
	 * Instance of the Rating class.
	 *
	 * @since  1.0.0
	 *
	 * @var    Rating $rating
	 */
	public $rating;

	/**
	 * Instance of the Template class.
	 *
	 * @since 1.0.0
	 *
	 * @var Template
	 */
	public $template;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since       1.0.0
	 * @param       string $plugin_name        The name of this plugin.
	 * @param       string $version            The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name        = $plugin_name;
		$this->version            = $version;

		$this->recipe   = new Recipe( $this->plugin_name, $this->version );
		$this->rating   = new Rating( $this->plugin_name, $this->version );
		$this->template = new Template( $this->plugin_name, $this->version );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Loader as all the hooks are defined
		 * in that particular class.
		 *
		 * The Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, NS\PUB_ASSET_URL . 'css/rpr-frontend.css', array(), $this->version, 'all' );

		// Load if Lightbox enabled, and we are on a recipe post.
		if ( Options::get_option( 'rpr_recipe_template_click_img' ) && is_singular( 'rpr_recipe' ) ) {
			wp_register_style( 'rpr-lightbox', NS\PUB_ASSET_URL . 'css/rpr-lightbox.css', array(), '2.10.0', 'all' );
		}

        $custom_styling = Options::get_option( 'rpr_recipe_custom_styling' );

        if ( $custom_styling ) {
            wp_add_inline_style( $this->plugin_name, $custom_styling );
        }
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Loader as all the hooks are defined
		 * in that particular class.
		 *
		 * The Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		global $post;
		$recipe_id = ( null !== $post ) ? $post->ID : 0;

		wp_enqueue_script( $this->plugin_name, NS\PUB_ASSET_URL . 'js/rpr-frontend.js', array( 'jquery' ), $this->version, true );
		wp_localize_script(
			$this->plugin_name,
			'rpr_public_vars',
			array(
				'ajax_url'      => admin_url( 'admin-ajax.php' ),
				'ratings_nonce' => wp_create_nonce( 'rpr-rating-nonce' ),
				'share_nonce'   => wp_create_nonce( 'rpr-share-nonce' ),
				'recipe_id'     => $recipe_id,
				'rpr_youtube_thumb_alt' => __( 'YouTube video thumbnail', 'recipepress-reloaded' )
			)
		);

		wp_register_script( 'rpr-frontend-controllers', NS\ASSETS_URL . 'public/js/rpr-frontend-controllers.js', array( 'jquery' ), $this->version, true );

		// Load if Lightbox enabled, and we are on a recipe post.
		if ( Options::get_option( 'rpr_recipe_template_click_img' ) && is_singular( 'rpr_recipe' ) ) {
			wp_register_script( 'rpr-lightbox', NS\PUB_ASSET_URL . 'js/rpr-lightbox.js', array( 'jquery', 'recipepress-reloaded' ), '2.10.0', true );
		}

		if ( Options::get_option( 'rpr_recipe_template_print_btn' ) && is_singular( 'rpr_recipe' ) ) {
			wp_enqueue_script( 'rpr-print', NS\PUB_ASSET_URL . 'js/rpr-print.js', array( 'jquery' ), '1.6.2', true );
		}
	}

	/**
	 * Removes the `rpr_ingredient` taxonomy terms from the
	 * WP post class
	 *
	 * @since 1.5.0
	 *
	 * @param string[] $classes   An array of post class names
	 * @param string[] $class     An array of additional class names added to the review
	 * @param int      $review_id The review ID
	 *
	 * @return string[]
	 */
	public function cleanup_post_class( $classes, $class, $review_id ) {

		foreach ( $classes as $k => $v ) {
			if ( false !== strpos( $v, 'rpr_ingredient' ) ) {
				unset( $classes[$k] );
			}
		}

		// Add the current recipe template to post class list.
		if ( is_singular( 'rpr_recipe' ) ) {
			$classes[] = Options::get_option( 'rpr_recipe_template' );
		}

		return $classes;
	}

	/**
	 * Add the defer tag to our scripts
	 *
	 * @since 1.8.0
	 *
	 * @param string $tag
	 * @param string $handle
	 * @param string $src
	 *
	 * @return string
	 */
	public function add_defer_script_tag( $tag, $handle, $src ) {

		if ( ( false !== stripos( $handle, 'rpr' ) ) && false === stripos( $tag, 'defer' ) ) {
			$tag = str_replace( '<script ', '<script defer ', $tag );
		}

		return $tag;
	}

	/**
	 * Catches and replaces YouTube oembeds with a methods that fetches
	 * the iframe on user interaction
	 *
	 * @since 1.9.0
	 *
	 * @hook `embed_oembed_html`
	 *
	 * @param string $html    The cached HTML result, stored in post meta.
	 * @param string $url     The attempted embed URL
	 * @param array  $attr    An array of shortcode attributes
	 * @param int    $post_ID The post ID
	 *
	 * @return string
	 */
	public function speedup_youtube_oembed( $html, $url, $attr, $post_ID ) {

		// https://stackoverflow.com/a/37704433/3513481
		if ( preg_match( '/^((?:https?:)?\/\/)?((?:www|m)\.)?((?:youtube\.com|youtu.be))(\/(?:[\w\-]+\?v=|embed\/|v\/)?)([\w\-]+)(\S+)?$/', $url, $matches ) ) {
			if ( ! is_admin() && Options::get_option( 'rpr_speedup_youtube_embeds' ) && is_singular( apply_filters( 'rpr/speedup_youtube/post_types', array( 'post', 'page', 'rpr_recipe' ) ) ) ) {
				return '<div class="rpr-youtube-player" data-id="' . $matches[5] . '"></div>';
			}
		}

		return $html;
	}

	/**
	 * Includes a 'functions' file to be used by the recipe template.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function include_functions_file() {

		// Get the layout chosen.
		$layout = apply_filters( 'rpr/recipe/template', Options::get_option( 'rpr_recipe_template', 'rpr_default' ) );

		$global_layout  = get_stylesheet_directory() . '/recipepress/' . $layout . '/functions.php';
		$local_layout   = NS\PLUGIN_DIR . 'inc/frontend/templates/' . $layout . '/functions.php';
		$default_layout = NS\PLUGIN_DIR . 'inc/frontend/templates/rpr_default/functions.php';

		if ( file_exists( $global_layout ) ) {
			// The layout provided by the theme.
			return include_once $global_layout;
		}

		if ( file_exists( $local_layout ) ) {
			// The layout provided by the plugin.
			return include_once $local_layout;
		}

		// Prevents a file reader error if switching from a theme with global layout.
		return include_once $default_layout;
	}

}
