<?php

namespace Recipepress\Inc\Admin\Settings;

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    Recipepress
 *
 * @author     Kemory Grubb <kemory@wzymedia.com>
 */
class Settings {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	public $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	public $version;

	/**
	 * The array of plugin settings.
	 *
	 * @since    1.0.0
	 *
	 * @access   private
	 * @var      array $registered_settings The array of plugin settings.
	 */
	public $registered_settings;

	/**
	 * The callback helper to render HTML elements for settings forms.
	 *
	 * @since    1.0.0
	 *
	 * @access   protected
	 * @var      Callbacks $callback Render HTML elements.
	 */
	public $callback;

	/**
	 * The sanitization helper to sanitize and validate settings.
	 *
	 * @since    1.0.0
	 *
	 * @access   protected
	 * @var      Sanitization $sanitization Sanitize and validate settings.
	 */
	public $sanitization;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param string       $plugin_name           The name of this plugin.
	 * @param string       $version               The version of this plugin.
	 * @param Callbacks    $settings_callback     The callback helper for rendering HTML markups.
	 * @param Sanitization $settings_sanitization The sanitization helper for sanitizing settings.
	 *
	 * @throws \Exception
	 */
	public function __construct( $plugin_name, $version, $settings_callback, $settings_sanitization ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->callback            = $settings_callback;
		$this->sanitization        = $settings_sanitization;
		$this->registered_settings = Definitions::get_settings();
	}

	/**
	 * A work-around to get i18n to work on the settings page
	 *
	 * @since 1.0.0
	 *
	 * @see https://github.com/wphuman/WordPress-Settings-Module/issues/9
	 *
	 * @throws \Exception
	 */
	public function set_settings(){
		$this->registered_settings = Definitions::get_settings();
	}

	/**
	 * Register all settings sections and fields.
	 *
	 * @since    1.0.0
	 *
	 * @return    void
	 */
	public function register_settings() {

		if ( false === get_option( 'recipepress_settings' ) ) {
			add_option( 'recipepress_settings', array() );
		}

		foreach ( $this->registered_settings as $tab => $settings ) {

			add_settings_section(
				'recipepress_settings_' . $tab,
				__return_null(),
				'__return_false',
				'recipepress_settings_' . $tab
			);

			foreach ( (array) $settings as $key => $option ) {

				$_name = isset( $option['name'] ) ? $option['name'] : $key;

				add_settings_field(
					'recipepress_settings[' . $key . ']',
					$_name,
					method_exists( $this->callback, $option['type'] . '_callback' ) ? array(
						$this->callback,
						$option['type'] . '_callback',
					) : array( $this->callback, 'missing_callback' ),
					'recipepress_settings_' . $tab,
					'recipepress_settings_' . $tab,
					array(
						'id'            => $key,
						'desc'          => ! empty( $option['desc'] ) ? $option['desc'] : '',
						'name'          => $_name,
						'section'       => $tab,
						'size'          => isset( $option['size'] ) ? $option['size'] : '25',
						'options'       => isset( $option['options'] ) ? $option['options'] : '',
						'std'           => isset( $option['std'] ) ? $option['std'] : '',
						'max'           => isset( $option['max'] ) ? $option['max'] : 999999,
						'min'           => isset( $option['min'] ) ? $option['min'] : 0,
						'step'          => isset( $option['step'] ) ? $option['step'] : 1,
						'class'         => $key . ' rpr-table-' . $option['type'],
						'accept'        => isset( $option['accept'] ) ? $option['accept'] : 'image/*',
						'pattern'       => isset( $option['pattern'] ) ? $option['pattern'] : '{2,}',
						'title'         => isset( $option['title'] ) ? $option['title'] : __( 'Please enter 2 or more lower-case characters.', 'recipepress-reloaded' ),
						'singular_desc' => isset( $option['singular_desc'] ) ? $option['singular_desc'] : __( 'The singular form of the label', 'recipepress-reloaded' ),
						'plural_desc'   => isset( $option['plural_desc'] ) ? $option['plural_desc'] : __( 'The plural form of the label', 'recipepress-reloaded' ),
						'singular_std'  => isset( $option['singular_std'] ) ? $option['singular_std'] : '',
						'plural_std'    => isset( $option['plural_std'] ) ? $option['plural_std'] : '',
					)
				);
			}
		}

		// Creates our settings in the options table.
		register_setting(
			'recipepress_settings',
			'recipepress_settings',
			array(
				$this->sanitization,
				'settings_sanitize',
			)
		);
	}

	/**
	 * Adds a flag to the options table used to
	 * check if we need to flush the rewrite rules
	 *
	 * Usually called by the Settings class after a setting that affects the
	 * rewrite rules has been called
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function flush_permalinks_on_update() {
		update_option( 'rpr_flush_rewrite_rules', true, true );
	}

	public static function plugin_js_options() {
		$diet_options = apply_filters(
			'rpr/settings/diet_options',
			array(
				array(
					'key'   => 'DiabeticDiet',
					'value' => 'Diabetic',
				),
				array(
					'key'   => 'GlutenFreeDiet',
					'value' => 'Gluten-Free',
				),
				array(
					'key'   => 'HalalDiet',
					'value' => 'Halal',
				),
				array(
					'key'   => 'HinduDiet',
					'value' => 'Hindu',
				),
				array(
					'key'   => 'KosherDiet',
					'value' => 'Kosher',
				),
				array(
					'key'   => 'LowCalorieDiet',
					'value' => 'Low Calorie',
				),
				array(
					'key'   => 'LowFatDiet',
					'value' => 'Low Fat',
				),
				array(
					'key'   => 'LowLactoseDiet',
					'value' => 'Low Lactose',
				),
				array(
					'key'   => 'LowSaltDiet',
					'value' => 'Low Salt',
				),
				array(
					'key'   => 'VeganDiet',
					'value' => 'Vegan',
				),
				array(
					'key'   => 'VegetarianDiet',
					'value' => 'Vegetarian',
				),
			)
		);

		wp_add_inline_script(
			'recipepress-reloaded',
			'window.rpr = window.rpr || {}; rpr.rprSettings = {}; rpr.rprSettings.dietOptions = ' . wp_json_encode( $diet_options )
		);
	}


}
