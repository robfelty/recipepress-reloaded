<?php

use Recipepress as NS;
use Recipepress\Inc\Common\Abstracts\Extension;
use Recipepress\Inc\Common\Entities\Share_Count;

/**
 * Class Social_Media_Sharing
 *
 * This class is a part of the plugin's "extension" feature.
 *
 * @since 1.0.0
 *
 * @author Kemory Grubb
 */
class RPR_Social_Media_Sharing extends Extension {

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
		$this->id             = 'rpr_social_media_sharing';
		$this->image          = plugin_dir_url( __FILE__ ) . 'assets/images/icon.png';
		$this->title          = __( 'Social Media Sharing', 'recipepress-reloaded' );
		$this->desc           = __( 'Simple, lightweight social sharing buttons for your recipes. Just select the platform you\'d like to share to, where the buttons should show up and save your settings.',
									'recipepress-reloaded' );
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
		return isset( $GLOBALS['recipe_id'] ) ? (int) $GLOBALS['recipe_id'] : get_the_ID();
	}

	/**
	 * All methods that we want to be called by the class goes here.
	 *
	 * @since 1.0.0
	 *
	 * return void
	 */
	public function load() {

		$this->add_filters();
		$this->add_actions();
		$this->add_shortcodes();
	}

	/**
	 * Add WordPress shortcodes to be registered here.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function add_shortcodes() {
		add_shortcode( 'rpr-sharing', array( $this, 'display_shortcode' ) );
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
		add_action( 'template_redirect', array( $this, 'add_share_buttons_to_recipe' ) );
		add_action( 'rpr_share_buttons_output', array( $this, 'rpr_share_buttons_output' ) );

		add_action( 'wp_ajax_nopriv_save_share_count', array( $this, 'save_share_count' ) );
		add_action( 'wp_ajax_save_share_count', array( $this, 'save_share_count' ) );
	}

	/**
	 * Increments the share counter
	 *
	 * @since 1.0.0
	 *
	 * @return void;
	 */
	public function save_share_count() {

		check_ajax_referer( 'rpr-share-nonce', 'share_nonce' );

		$recipe_id   = isset( $_POST['recipe_id'] ) ? (int) $_POST['recipe_id'] : 0;
		$social_site = isset( $_POST['social_site'] ) ? sanitize_text_field( $_POST['social_site'] ) : '';

		$shares = get_post_meta( $recipe_id, 'rpr_social_share_counts', true ) ?: new Share_Count();

		if ( property_exists( $shares, $social_site ) ) {
			++ $shares->$social_site;
		}

		update_post_meta( $recipe_id, 'rpr_social_share_counts', $shares );

		wp_die();
	}

	/**
	 * The share buttons HTML markup we are adding to recipes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_share_buttons_to_recipe() {
        $_this = &$this;

		if ( is_home() && ! $this->get_setting( 'display_on_homepage', false ) ) {
			return;
		}

		if ( 1 === (int) $this->get_setting( 'buttons_position' ) || 3 === (int) $this->get_setting( 'buttons_position' ) ) {
			add_action(
				'rpr/template/start',
				static function () use ( $_this ) {
					if ( ! is_singular( 'rpr_recipe' ) ) {
						return null;
					}
					include __DIR__ . '/includes/share-buttons.php';
				}
			);
		}

		if ( 2 === (int) $this->get_setting( 'buttons_position' ) || 3 === (int) $this->get_setting( 'buttons_position' ) ) {
			add_action(
				'rpr/template/end',
				static function () use ( $_this ) {
					if ( ! is_singular( 'rpr_recipe' ) ) {
						return null;
					}
					include __DIR__ . '/includes/share-buttons.php';
				}
			);
		}

	}

	/**
	 * Adds share buttons via `do_action`.
	 *
	 * @return void
	 */
	public function rpr_share_buttons_output() {
        $_this = &$this;

		include __DIR__ . '/includes/share-buttons.php';
	}

	/**
	 * Displays our share button's shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @return void|string
	 */
	public function display_shortcode() {

		if ( 4 === (int) $this->get_setting( 'buttons_position' ) ) {

			if ( ! is_singular( 'rpr_recipe' ) ) {
				return;
			}

			ob_start();

            $_this = &$this;

			include_once __DIR__ . '/includes/share-buttons.php';
			return ob_get_clean();
		}
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
			wp_enqueue_style( 'rpr-share-buttons-styles', plugin_dir_url( __FILE__ ) . 'assets/css/rpr-button-styles.css', array(), $this->version );

			$custom_css = '.rpr-share-buttons-list div { 
							background: ' . $this->get_setting( 'buttons_color' ) . '; 
							border-color: ' . $this->get_setting( 'buttons_color' ) . '; 
						}
						.rpr-share-buttons-list div a:hover {
							color: ' . $this->get_setting( 'buttons_color' ) . ' !important;
						}
						.rpr-share-buttons-list div:hover svg {
							fill: ' . $this->get_setting( 'buttons_color' ) . ' !important;
						}
						';
			wp_add_inline_style( 'rpr-share-buttons-styles', esc_html( $custom_css ) );

			if ( ! wp_script_is( 'rpr-print' ) ) {
				wp_enqueue_script( 'rpr-print', NS\PUB_ASSET_URL . 'js/rpr-print.js', array( 'jquery' ), '1.5.1', true );
			}

			/*$script = "function socialShare(elem) {
						jQuery.ajax({
							type: 'post',
							url: rpr_public_vars.ajax_url,
							data: {
								action: 'save_share_count',
								social_site: elem.parentNode.getAttribute('data-social'),
								share_nonce: rpr_public_vars.share_nonce,
								recipe_id: rpr_public_vars.recipe_id,
							}
						});
					}";

			wp_add_inline_script( 'recipepress-frontend', $script, 'before' );*/
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

        $screen = function_exists( 'get_current_screen' ) ? \get_current_screen() : null;

		if ( null !== $screen && 'rpr_recipe_page_rpr_extensions' === $screen->id ) {
			wp_enqueue_script(
				'rpr-share-buttons-scripts',
				plugin_dir_url( __FILE__ ) . 'assets/js/rpr-button-scripts.js',
				array( 'jquery', 'rpr-micromodal-script', 'rpr-selectize' ),
				'1.0.0',
				true
			);
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
			'rpr-social-media-sharing',
			'rpr_social_media_sharing_options',
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

		$settings = get_option( 'rpr_social_media_sharing_options', array() );

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
			'rpr-social-media-sharing',
			'rpr-social-media-sharing-settings-updated',
			'Social Media sharing settings updated.',
			'updated'
		);

		foreach ( $settings as $key => $value ) {
			$settings[ $key ] = sanitize_text_field( $value );
		}
		return $settings;
	}

	/**
	 * Show the sharing bar
	 */
	public function hello_world() {
		include_once __DIR__ . '/includes/share-buttons.php';
	}

}
