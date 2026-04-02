<?php
/**
 * Adds a carousel of related recipes
 *
 * @package Recipepress
 */

use Recipepress as NS;
use Recipepress\Inc\Common\Abstracts\Extension;
use Recipepress\Inc\Common\Utilities\Icons;
use Recipepress\Inc\Frontend\Rating;

/**
 * Class RPR_Favorites
 *
 * This class is a part of the plugin's "extension" feature.
 *
 * @since 1.0.0
 *
 * @author Kemory Grubb
 */
class RPR_Favorites extends Extension {

	/**
	 * RPR_Favorites constructor.
	 *
	 * @since 1.0.0
	 *
	 * @var string $id       The internal ID of the extension. Must match class name.
	 * @var string $image    An image used as an icon on the extensions page - 500x500.
	 * @var string $title    The title of the extension as displayed on the extensions page.
	 * @var string $desc     The description of the extension as displayed on the extensions page.
	 * @var string $settings Does the extension use a settings page.
	 */
	public function __construct() {
		$this->id             = 'rpr_favorites';
		$this->image          = plugin_dir_url( __FILE__ ) . 'assets/images/icon.png';
		$this->title          = __( 'Favorite Recipes', 'recipepress-reloaded' );
		$this->desc           = __( 'A user favorite recipes cataloging feature, where visitor can save and view their favorite recipes', 'recipepress-reloaded' );
		$this->settings       = true;
		$this->settings_label = __( 'Settings', 'recipepress-reloaded' );
	}

	/**
	 * The post ID of the current recipe we are attaching to.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function the_recipe_id() {
		return ! empty( $GLOBALS['recipe_id'] )  ? (int) $GLOBALS['recipe_id'] : get_the_ID();
	}

	/**
	 * All methods that we want to be called by the class goes here.
	 *
	 * @since 1.0.0
	 *
	 * return void
	 */
	public function load() {

		if ( $this->enable ) {
			$this->add_filters();
			$this->add_actions();
			$this->add_shortcodes();
		}
	}

	/**
	 * Add WordPress shortcodes to be registered here.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function add_shortcodes() {
		add_shortcode( 'rpr-favorites', array( $this, 'display_shortcode' ) );
	}

	/**
	 * Add WordPress filters to be called here.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function add_filters() {

	}

	/**
	 * Add WordPress actions to be called here.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function add_actions() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ), 99 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 99 );
		add_action( 'rpr/extensions/settings_page/footer', array( $this, 'render_settings_page' ) );
		add_action( 'wp_footer', array( $this, 'render_favorite_button' ) );
	}

	/**
	 * Frontend assets for the share buttons.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_assets() {
        global $post;
		$recipe_id = $this->the_recipe_id();

		// If we are on a recipe
        if ( is_singular( 'rpr_recipe' ) ) {
            wp_enqueue_script( 'rpr-favorites-button-js',
                NS\EXT_URL . 'rpr-favorites/assets/js/rpr-favorites-button.js',
                array( 'jquery' ), $this->version, true );

            $recipe_info = array(
                'id'             => $recipe_id,
                'title'          => $post->post_title,
                'url'            => get_the_permalink( $recipe_id ),
                'description'    => wp_strip_all_tags(get_the_excerpt( $recipe_id ) ),
                'thumbnail'      => get_the_post_thumbnail_url( $recipe_id, 'thumbnail' ),
                'featured_image' => get_the_post_thumbnail_url( $recipe_id, 'full' ),
                'favorites_page' => $this->get_setting( 'favorites_page_url' ),
                'published_date' => get_the_date( 'c', $recipe_id ),
                'site_title'     => get_bloginfo( 'name' ),
                'rating'         => ( new Rating( 'recipepress-reloaded', '2.0.0' ) )->rating_info( 'avg', $recipe_id ),
            );
            wp_add_inline_script( 'rpr-favorites-button-js',
                'window.rpr = window.rpr || {}; rpr.rprFavoriteRecipeVars = ' . wp_json_encode( $recipe_info ) );
            wp_register_style( 'rpr-favorites-button-css',
                NS\EXT_URL . 'rpr-favorites/assets/css/rpr-favorites-button.css',
                array(), $this->version );
        }

        // If we are on a page with our shortcode
        if ( ( $post instanceof \WP_Post )
            && has_shortcode( $post->post_content, 'rpr-favorites' )
            && is_singular( 'page' ) ) {
            wp_enqueue_script( 'rpr-favorites-list-js',
                NS\EXT_URL . 'rpr-favorites/assets/js/rpr-favorites-list.js',
                array( 'jquery' ), $this->version, true );
            wp_register_style( 'rpr-favorites-list-css',
                NS\EXT_URL . 'rpr-favorites/assets/css/rpr-favorites-list.css',
                array(), $this->version );
            wp_add_inline_script( 'rpr-favorites-list-js',
                'window.rpr = window.rpr || {}; rpr.rprFavoriteRecipeVars = '
                . wp_json_encode( array( 'site_title' => get_bloginfo( 'name' ) ) ) );
        }

	}

	/**
	 * WP admin assets for the share buttons.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_admin_assets() {

	}

	/**
	 * The favorite recipes HTML markup we are adding via shortcode
	 */
	public function display_shortcode() {
		wp_enqueue_style( 'rpr-favorites-button-css' );
		wp_enqueue_style( 'rpr-favorites-list-css' );

		return '<div id="rpr-favorites-list"></div>';
	}

	/**
	 * Adds the favorite button to recipes
	 */
	public function render_favorite_button() {
		if ( is_singular( 'rpr_recipe' ) ) {
            wp_enqueue_style( 'rpr-favorites-button-css' );

			echo '<div id="rpr-favorites-button"></div>';
		}
	}

	/**
	 * Registers the settings to be stored to the WP Options table.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_settings() {

		register_setting(
			'rpr-favorites',
			'rpr_favorites_options',
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
			)
		);
	}

	/**
	 * The hidden markup that is rendered by the Thickbox modal window.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function render_settings_page() {
		include __DIR__ . '/includes/settings-page.php';
	}

	/**
	 * Looks to see if the specified setting exists, returns default if not.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key     The key we are checking for.
	 * @param mixed  $default A default value to return.
	 *
	 * @return mixed
	 */
	public function get_setting( $key, $default = '' ) {

		if ( empty( $key ) ) {
			return $default;
		}

		$settings = get_option( 'rpr_favorites_options', array() );

		return  ! empty( $settings[ $key ] ) ? $settings[ $key ] : $default;
	}

	/**
	 * Sanitize the settings being saved by this extension.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings The settings array for the extension.
	 *
	 * @return array
	 */
	public function sanitize_settings( array $settings ) {

		add_settings_error(
			'rpr-favorites',
			'rpr-favorites-updated',
			'Favorite recipe settings updated.',
			'updated'
		);

		foreach ( $settings as $key => $value ) {
			$settings[ $key ] = sanitize_text_field( $value );
		}
		return $settings;
	}

	/**
	 * Get the recipes
	 *
	 * @since 1.0.0
	 *
	 * @uses \wp_send_json_success()
	 *
	 * @return void
	 */
	public function get_recipes() {

		$data    = array();
		$recipes = get_posts(
			array(
				'numberposts' => 10,
				'exclude'     => array( $this->the_recipe_id() ),
				'post_type'   => 'rpr_recipe',
			)
		);

		foreach ( $recipes as $key => $recipe ) {
			$data[ $key ]['ID']    = $recipe->ID;
			$data[ $key ]['title'] = $recipe->post_title;
			$data[ $key ]['url']   = get_the_permalink( $recipe->ID );
			$data[ $key ]['thumb'] = get_the_post_thumbnail_url( $recipe->ID, 'thumbnail' );
		}

		wp_send_json_success( $data );
	}

}
