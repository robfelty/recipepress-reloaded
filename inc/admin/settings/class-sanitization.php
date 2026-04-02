<?php

namespace Recipepress\Inc\Admin\Settings;

use Recipepress as NS;
use Recipepress\Inc\Libraries\CSSTidy\CSSTidy;

/**
 * Recipepress Sanitization Helper Class
 *
 * The callback functions of the settings page
 *
 * @package    Recipepress
 *
 * @author     Kemory Grubb <kemory@wzymedia.com>
 */
class Sanitization {

	/**
	 * The ID of this plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version number of this plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var string $version The version number of this plugin.
	 */
	private $version;

	/**
	 * The array of plugin settings.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var array $registered_settings The array of plugin settings.
	 */
	private $registered_settings;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 *
	 * @throws \Exception
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->registered_settings = Definitions::get_settings();

		add_filter( 'recipepress_settings_sanitize_text', array( $this, 'sanitize_text_field' ) );
		add_filter( 'recipepress_settings_sanitize_email', array( $this, 'sanitize_email_field' ) );
		add_filter( 'recipepress_settings_sanitize_checkbox', array( $this, 'sanitize_checkbox_field' ) );
		add_filter( 'recipepress_settings_sanitize_url', array( $this, 'sanitize_url_field' ) );
		add_filter( 'recipepress_settings_sanitize_cssbox', array( $this, 'sanitize_cssbox_field' ) );
		add_filter( 'recipepress_settings_sanitize_slug', array( $this, 'sanitize_slug_field' ) );
		add_filter( 'recipepress_settings_sanitize_labels', array( $this, 'sanitize_labels_field' ) );
	}

	/**
	 * Settings Sanitization
	 *
	 * Adds a settings error (for the updated message)
	 * At some point this will validate input.
	 *
	 * Note: All sanitized settings will be saved.
	 * Thus, no error messages will be produced.
	 *
	 * Filters in order:
	 * - recipepress_settings_sanitize_<tab_slug>
	 * - recipepress_settings_sanitize_<type>
	 * - recipepress_settings_sanitize
	 * - recipepress_settings_on_change_<tab_slug>
	 * - recipepress_settings_on_change_<field_key>
	 *
	 * @since 1.0.0
	 *
	 * @param array $input The value inputted in the field.
	 *
	 * @return string|array
	 */
	public function settings_sanitize( $input ) {

		if ( empty( $_POST['_wp_http_referer'] ) ) { // phpcs:ignore
			return $input;
		}

		wp_parse_str( $_POST['_wp_http_referer'], $referrer ); // phpcs:ignore
		$tab = isset( $referrer['tab'] ) ? $referrer['tab'] : Definitions::get_default_tab_slug();

		// Tab filter.
		$input = apply_filters( 'recipepress_settings_sanitize_' . $tab, $input );

		// Trigger action hook for general settings update for $tab.
		$this->do_settings_on_change_hook( $input, $tab );

		// Loop through each setting being saved and pass it through a sanitization filter.
		foreach ( $input as $key => $value ) {
			$new_value     = $value; // Set value of $value in $new_value.
			$input[ $key ] = $this->apply_type_filter( $input, $tab, $key );
			$input[ $key ] = $this->apply_general_filter( $input, $key );
			$this->do_settings_on_key_change_hook( $key, $new_value );
		}

		add_settings_error( $this->plugin_name . '-notices', $this->plugin_name, __( 'Settings updated.', 'recipepress-reloaded' ), 'updated' );

		return $this->get_output( $tab, $input );
	}

	/**
	 * Applies a filter to each of our input types.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $input An array of data passed from a settings page.
	 * @param string $tab   The tab we are getting the data from.
	 * @param string $key   The settings key.
	 *
	 * @return mixed
	 */
	private function apply_type_filter( $input, $tab, $key ) {

		// Get the setting type (checkbox, select, etc).
		$type = isset( $this->registered_settings[ $tab ][ $key ]['type'] ) ? $this->registered_settings[ $tab ][ $key ]['type'] : false;

		if ( false === $type ) {
			return $input[ $key ];
		}

		return apply_filters( 'recipepress_settings_sanitize_' . $type, $input[ $key ], $key );
	}

	/**
	 * Applies a filter to our general sanitize filter.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $input An array of data passed from a settings page.
	 * @param string $key   The settings key.
	 *
	 * @return mixed
	 */
	private function apply_general_filter( $input, $key ) {

		return apply_filters( 'recipepress_settings_sanitize', $input[ $key ], $key );
	}


	/**
	 * Creates a hook on saved setting being different from new setting.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key       The settings key.
	 * @param string $new_value The new setting value we are comparing against.
	 *
	 * @return void
	 */
	private function do_settings_on_key_change_hook( $key, $new_value ) {

		$old_plugin_settings = get_option( 'recipepress_settings' );
		// Checks if value is saved already in $old_plugin_settings.
		if ( isset( $old_plugin_settings[ $key ] ) && $old_plugin_settings[ $key ] !== $new_value ) {

			do_action( 'recipepress_settings_on_change_' . $key, $new_value, $old_plugin_settings[ $key ] );
		}
	}


	/**
	 * Tab specific on change hook (only if a value has changed)
	 *
	 * @since 1.0.0
	 *
	 * @param string $new_values The new setting value we are comparing against.
	 * @param string $tab        The current tab.
	 *
	 * @return void
	 */
	private function do_settings_on_change_hook( $new_values, $tab ) {

		$old_plugin_settings = get_option( 'recipepress_settings' );
		$changed             = false;

		foreach ( (array) $new_values as $key => $new_value ) {
			if ( isset( $old_plugin_settings[ $key ] ) && $old_plugin_settings[ $key ] !== $new_value ) {
				$changed = true;
			}
		}

		if ( $changed ) {
			do_action( 'recipepress_settings_on_change_' . $tab, $new_values, $old_plugin_settings );
		}
	}

	/**
	 * Checks that a value is not empty is zero
	 *
	 * @since 1.0.0
	 *
	 * @param string $var The value we are checking.
	 *
	 * @return bool
	 */
	private function not_empty_or_zero( $var ) {
		return ( ! empty( $var ) || 0 === (int) $var ); // phpcs:ignore
	}

	/**
	 * Loop through the whitelist and unset any that are empty for the tab being saved
	 *
	 * @param string $tab   The current settings tab.
	 * @param array  $input An array of settings.
	 *
	 * @return array
	 */
	private function get_output( $tab, $input ) {

		$old_plugin_settings = get_option( 'recipepress_settings' );

		if ( ! is_array( $old_plugin_settings ) ) {
			$old_plugin_settings = array();
		}

		// Remove empty elements.
		$input = array_filter( $input, array( $this, 'not_empty_or_zero' ) );
		foreach ( (array) $this->registered_settings[ $tab ] as $key => $_value ) {

			if ( ! isset( $input[ $key ] ) ) {
				$this->do_settings_on_key_change_hook( $key, null );
				if ( isset( $old_plugin_settings[ $key ] ) ) {
					unset( $old_plugin_settings[ $key ] );
				}
			}
		}

		// Overwrite the old values with new sanitized ones.
		return array_merge( $old_plugin_settings, $input );
	}

	/**
	 * Sanitize text fields
	 *
	 * @since 1.0.0
	 *
	 * @param array $input The field value.
	 *
	 * @return string $input Sanitized value
	 */
	public function sanitize_text_field( $input ) {

		return sanitize_text_field( $input );
	}

	/**
	 * Sanitize email fields
	 *
	 * @since 1.0.0
	 *
	 * @param array $input The field value.
	 *
	 * @return string $sanitizes_email Sanitized email, return empty string if not is_email()
	 */
	public function sanitize_email_field( $input ) {

		$sanitizes_email = sanitize_email( $input );

		if ( ! is_email( $sanitizes_email ) ) {
			$sanitizes_email = __return_empty_string();
		}

		return $sanitizes_email;
	}

	/**
	 * Sanitize checkbox fields
	 * From WordPress SEO by Yoast class-wpseo-options.php
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $input The field value.
	 *
	 * @return string $input '1' if true, empty string otherwise
	 */
	public function sanitize_checkbox_field( $input ) {

		$true = array(
			'1',
			'true',
			'True',
			'TRUE',
			'y',
			'Y',
			'yes',
			'Yes',
			'YES',
			'on',
			'On',
			'ON',
		);

		// String.
		if ( is_string( $input ) ) {

			$input = trim( $input );

			if ( in_array( $input, $true, true ) ) {
				return '1';
			}
		}

		// Boolean.
		if ( is_bool( $input ) && $input ) {
			return '1';
		}

		// Integer.
		if ( is_int( $input ) && 1 === $input ) {
			return '1';
		}

		// Float.
		if ( is_float( $input ) && ! is_nan( $input ) && (float) 1 === $input ) {
			return '1';
		}

		return __return_empty_string();
	}

	/**
	 * Sanitize a url for saving to the database
	 * Not to be confused with the old native WP function
	 * From WordPress SEO by Yoast class-wpseo-options.php
	 *
	 * @since 1.0.0
	 *
	 * @param string $input             The current setting value.
	 * @param array  $allowed_protocols The allow URL protocols.
	 *
	 * @return string URL that safe to use in database queries, redirects and HTTP requests.
	 */
	public function sanitize_url_field( $input, $allowed_protocols = array( 'http', 'https' ) ) {
		return esc_url_raw( sanitize_text_field( rawurldecode( $input ) ), $allowed_protocols );
	}

	/**
	 * Sanitize slug fields
	 *
	 * @since 1.9.0
	 *
	 * @param string $input The field value.
	 *
	 * @return string $input Sanitized value
	 */
	public function sanitize_slug_field( $input ) {

		return sanitize_title( $input );
	}

	/**
	 * Sanitize label fields
	 *
	 * @since 1.9.0
	 *
	 * @param array $input The field value.
	 *
	 * @return array $input Sanitized value
	 */
	public function sanitize_labels_field( $input ) {

		if ( is_array( $input) ) {
			return array_map( 'sanitize_text_field', $input );
		}

		return $input;
	}

	/**
	 * Sanitize user provided CSS using the CSSTidy library
	 *
	 * @see https://wordpress.stackexchange.com/questions/53970/sanitize-user-entered-css.
	 *
	 * @since    1.0.0
	 *
	 * @param    string $input The user provided option being saved.
	 *
	 * @return    string        Parsed and sanitized CSS string.
	 */
	public function sanitize_cssbox_field( $input ) {

		$csstidy = new CSSTidy();
		$csstidy->set_cfg( 'remove_bslash', false );
		$csstidy->set_cfg( 'compress_colors', false );
		$csstidy->set_cfg( 'compress_font-weight', false );
		$csstidy->set_cfg( 'discard_invalid_properties', true );
		$csstidy->set_cfg( 'merge_selectors', false );
		$csstidy->set_cfg( 'remove_last_;', false );
		$csstidy->set_cfg( 'css_level', 'CSS3.0' );
		$csstidy->set_cfg( 'template', NS\LIB_DIR . 'csstidy\inc\wordpress-standard.tpl' );
		$input = preg_replace( '/\\\\([0-9a-fA-F]{4})/', '\\\\\\\\$1', $input );
		$input = wp_kses_split( $input, array(), array() );
		$csstidy->parse( $input );
		$input = $csstidy->print->plain();

		return $input;
	}
}
