<?php
/**
 * Adds a carousel of related recipes
 *
 * @package Recipepress
 */

use Recipepress as NS;
use Recipepress\Inc\Common\Abstracts\Extension;

/**
 * Class RPR_Recipe_Carousel
 *
 * This class is a part of the plugin's "extension" feature.
 *
 * @since 1.0.0
 *
 * @author Kemory Grubb
 */
class RPR_Recipe_Carousel extends Extension {

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
		$this->id             = 'rpr_recipe_carousel';
		$this->image          = plugin_dir_url( __FILE__ ) . 'assets/images/icon.png';
		$this->title          = __( 'Recipe Carousel', 'recipepress-reloaded' );
		$this->desc           = __( 'A simple, lightweight author box that is displayed below recipes', 'recipepress-reloaded' );
		$this->settings       = true;
		$this->enable         = false;
		$this->settings_label = __( 'Settings', 'recipepress-reloaded' );
	}

	/**
	 * The post ID of the current recipe we are attaching to.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function the_review_id() {
		return ( isset( $GLOBALS['recipe_id'] ) && '' !== $GLOBALS['recipe_id'] ) ? $GLOBALS['recipe_id'] : 0;
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
		add_action( 'template_redirect', array( $this, 'add_author_carousel_to_recipes' ) );
		add_action( 'wp_ajax_nopriv_get_recipes', array( $this, 'get_recipes' ) );
		add_action( 'wp_ajax_get_recipes', array( $this, 'get_recipes' ) );
	}

	/**
	 * The recipe carousel HTML markup we are adding to recipes
	 */
	public function add_author_carousel_to_recipes() {

		if ( is_singular( 'rpr_recipe' ) || is_singular( 'post' ) ) {

			add_action(
				'rpr_recipe_carousel_output',
				static function () {
					include __DIR__ . '/includes/recipe-carousel.php';
				}
			);
		}

		return false;
	}

	/**
	 * Frontend assets for the share buttons.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_assets() {

		wp_enqueue_style( 'rpr-slick-css', NS\EXT_URL . 'rpr-recipe-carousel/assets/css/slick.css', array(), '1.9.0' );
		wp_enqueue_style( 'rpr-slick-theme', NS\EXT_URL . 'rpr-recipe-carousel/assets/css/slick-theme.css', array(), '1.9.0' );
		wp_enqueue_style( 'rpr-recipe-carousel-styles', NS\EXT_URL . 'rpr-recipe-carousel/assets/css/rpr-recipe-carousel.css', array(), $this->version );

		wp_enqueue_script( 'rpr-stoor-js', NS\EXT_URL . 'rpr-recipe-carousel/assets/js/stoor.umd.js', array( 'jquery' ), '1.2.1', true );
		wp_enqueue_script( 'rpr-slick-js', NS\EXT_URL . 'rpr-recipe-carousel/assets/js/slick.min.js', array( 'jquery' ), '1.9.0', true );
		wp_enqueue_script( 'rpr-recipe-carousel-js', NS\EXT_URL . 'rpr-recipe-carousel/assets/js/rpr-recipe-carousel.js', array( 'jquery' ), '1.9.0', true );

		$custom_css = '';
		wp_add_inline_style( 'rpr-recipe-carousel-styles', esc_html( $custom_css ) );
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
	 * Registers the settings to be stored to the WP Options table.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_settings() {

		register_setting(
			'rpr-recipe-carousel',
			'rpr_recipe_carousel_options',
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

		$settings = get_option( 'rpr_recipe_carousel_options', array() );

		return ( isset( $settings[ $key ] ) && ! empty( $settings[ $key ] ) ) ? $settings[ $key ] : $default;
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
			'rpr-recipe-carousel',
			'rpr-recipe-carousel-updated',
			'Recipe carousel settings updated.',
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
				'exclude'     => array( $this->the_review_id() ),
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
