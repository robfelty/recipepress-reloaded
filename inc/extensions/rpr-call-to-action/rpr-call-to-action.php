<?php

use Recipepress\Inc\Common\Abstracts\Extension;

/**
 * Class RPR_Call_To_Action
 *
 * This class is a part of the plugin's "extension" feature.
 *
 * @since 1.0.0
 *
 * @author    Kemory Grubb
 */
class RPR_Call_To_Action extends Extension {

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
		$this->id             = 'rpr_call_to_action';
		$this->image          = plugin_dir_url( __FILE__ ) . 'assets/images/icon.png';
		$this->title          = __( 'Call to Action', 'recipepress-reloaded' );
		$this->desc           = __( 'A simple Call to Action box that is added to your recipes. ', 'recipepress-reloaded' );
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
		add_shortcode( 'rpr-cta', array( $this, 'display_shortcode' ) );
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
		add_action( 'template_redirect', array( $this, 'add_cta_to_recipes' ) );
	}

	/**
	 * The CTA markup we are adding to recipes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_cta_to_recipes() {

		if ( ! is_singular( 'rpr_recipe' ) ) {
			return;
		}

		if ( 1 === (int) $this->get_setting( 'cta_position' ) ) {
			add_action(
				'rpr/template/description/after',
				function () {

					$out  = '';
					$out .= '<div class="rpr-call-to-action no-print">';

					if ( $this->get_setting( 'cta_title' ) ) {
						$out .= '<div class="rpr-call-to-action-title">';
						$out .= '<h3>' . esc_html( $this->get_setting( 'cta_title' ) ) . '</h3>';
						$out .= '</div>';
					}

					if ( $this->get_setting( 'cta_text' ) ) {
						$out .= '<div class="rpr-call-to-action-text">';
						$out .= wpautop( $this->get_setting( 'cta_text' ) );
						$out .= '</div>';
					}

					$out .= '</div>';

					echo $out; // phpcs:ignore
				}
			);
		}

		if ( 2 === (int) $this->get_setting( 'cta_position' ) ) {
			add_action(
				'rpr/template/end',
				function () {

					$out  = '';
					$out .= '<div class="rpr-call-to-action no-print">';

					if ( $this->get_setting( 'cta_title' ) ) {
						$out .= '<div class="rpr-call-to-action-title">';
						$out .= '<h3>' . esc_html( $this->get_setting( 'cta_title' ) ) . '</h3>';
						$out .= '</div>';
					}

					if ( $this->get_setting( 'cta_text' ) ) {
						$out .= '<div class="rpr-call-to-action-text">';
						$out .= wpautop( $this->get_setting( 'cta_text' ) );
						$out .= '</div>';
					}

					$out .= '</div>';

					echo $out; // phpcs:ignore
				}
			);
		}

		if ( 4 === (int) $this->get_setting( 'cta_position' ) ) {
			return;
		}

	}

	/**
	 * Displays our share button's shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function display_shortcode() {

		if ( ! is_singular( 'rpr_recipe' ) ) {
			return '';
		}

		if ( 3 === (int) $this->get_setting( 'cta_position' ) ) {

			$out  = '';
			$out .= '<div class="rpr-call-to-action no-print">';

			if ( $this->get_setting( 'cta_title' ) ) {
				$out .= '<div class="rpr-call-to-action-title">';
				$out .= '<h3>' . esc_html( $this->get_setting( 'cta_title' ) ) . '</h3>';
				$out .= '</div>';
			}

			if ( $this->get_setting( 'cta_text' ) ) {
				$out .= '<div class="rpr-call-to-action-text">';
				$out .= wpautop( $this->get_setting( 'cta_text' ) );
				$out .= '</div>';
			}

			$out .= '</div>';

			return $out; // phpcs: ignore.
		}

		return '';
	}

	/**
	 * Frontend assets for the share buttons.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_assets() {

		$custom_css = '.rpr-call-to-action {
							padding: 1em;
							border-radius: 5px;
							border: 1px solid;
							border-color:' . esc_attr( $this->get_setting( 'cta_border_color', 'inherit' ) ) . ';
							background-color:' . esc_attr( $this->get_setting( 'cta_background_color', 'transparent' ) ) . ';
							color:' . esc_attr( $this->get_setting( 'cta_text_color', 'inherit' ) ) . ';
							margin: 2em 0;
						}
						.rpr-call-to-action.no-print p:last-child {
							margin: 0;
						}
						';
		wp_add_inline_style( 'recipepress-reloaded', esc_html( $custom_css ) );
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

		if ( $screen && 'rpr_recipe_page_rpr_extensions' === $screen->id ) {

			wp_enqueue_script(
				'rpr-cta-scripts',
				plugin_dir_url( __FILE__ ) . 'assets/js/rpr-cta-scripts.js',
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
			'rpr-call-to-action',
			'rpr_call_to_action_options',
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
	public function get_setting( $key, $default = null ) {

		if ( empty( $key ) ) {
			return $default;
		}

		$settings = get_option( 'rpr_call_to_action_options', array() );

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
			'rpr-call-to-action',
			'rpr-call-to-action-settings-updated',
			'Call to Action settings updated.',
			'updated'
		);

		foreach ( $settings as $key => $value ) {
			$settings[ $key ] = wp_kses_post( $value );
		}
		return $settings;
	}

}
