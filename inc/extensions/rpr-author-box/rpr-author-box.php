<?php
/**
 * Adds an author box below recipes
 *
 * @package Recipepress
 */

use Recipepress as NS;
use Recipepress\Inc\Common\Abstracts\Extension;

/**
 * Class RPR_Author_Box
 *
 * This class is a part of the plugin's "extension" feature.
 *
 * @since 1.0.0
 *
 * @author Kemory Grubb
 */
class RPR_Author_Box extends Extension {

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
		$this->id             = 'rpr_author_box';
		$this->image          = plugin_dir_url( __FILE__ ) . 'assets/images/icon.png';
		$this->title          = __( 'Author Box', 'recipepress-reloaded' );
		$this->desc           = __( 'A simple, lightweight author box that is displayed below recipes', 'recipepress-reloaded' );
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
		return ( $GLOBALS['recipe_id'] && '' !== $GLOBALS['recipe_id'] ) ? $GLOBALS['recipe_id'] : get_the_ID();
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
		add_action( 'template_redirect', array( $this, 'add_author_box_to_recipes' ) );
	}

	/**
	 * The share buttons HTML markup we are adding to book reviews
	 */
	public function add_author_box_to_recipes() {

		if ( is_singular( 'rpr_recipe' ) ) {

			add_action(
				'rpr/template/end',
				function () {
					$post_type = 'recipes';
					include __DIR__ . '/includes/author-box.php';
				}
			);
		}

		if ( is_singular( 'post' ) ) {

			add_action(
				'rpr_author_box_output',
				function () {
					$post_type = 'posts';
					include __DIR__ . '/includes/author-box.php';
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

		if ( is_single() ) {
			wp_enqueue_style( 'rpr-author-box-styles', NS\EXT_URL . 'rpr-author-box/assets/css/rpr-author-box.css', array(), $this->version );

			/*$css_file = file_get_contents( NS\EXT_URL . 'rpr-author-box/assets/css/rpr-author-box.css' );

			if ( false !== $css_file ) {
				wp_add_inline_style( NS\PLUGIN_NAME, esc_html( $css_file ) );
			}*/
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
	 * Registers the settings to be stored to the WP Options table.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_settings() {

		register_setting(
			'rpr-author-box',
			'rpr_author_box_options',
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

		$settings = get_option( 'rpr_author_box_options', array() );

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
			'rpr-author-box',
			'rpr-author-box-updated',
			'Author box settings updated.',
			'updated'
		);

		foreach ( $settings as $key => $value ) {
			$settings[ $key ] = sanitize_text_field( $value );
		}
		return $settings;
	}

}
