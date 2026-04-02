<?php
/**
 * The class that handles getting our options
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Recipepress
 * @subpackage Recipepress/core
 */

namespace Recipepress\Inc\Core;

/**
 * The get_option functionality of the plugin.
 *
 * @package Recipepress\Inc\Core
 *
 * @author  Your Name <email@example.com>
 */
class Options {

	/**
	 * Get all options
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function get_options() {

		return get_option( 'recipepress_settings', array() );
	}



	/**
	 * Get an option
	 *
	 * Looks to see if the specified setting exists, returns default if not.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key    The settings key to retrieve.
	 * @param mixed  $default Return value if the key doesn't exists.
	 *
	 * @return mixed $value  Value saved / $default if key if not exist.
	 */
	public static function get_option( $key, $default = false ) {

		if ( empty( $key ) ) {
			return $default;
		}

		$plugin_options = get_option( 'recipepress_settings', array() );

		return isset( $plugin_options[ $key ] ) ? $plugin_options[ $key ] : $default;
	}

	/**
	 * Update an option.
	 *
	 * Updates the specified option.
	 * This is for developers to update options outside the settings page.
	 *
	 * WARNING: Hooks and filters will be triggered!!
	 *
	 * @since 1.0.0
	 *
	 * @param string $key   The settings key to retrieve.
	 * @param mixed  $value The value to store.
	 *
	 * @return bool true if the option was saved or false if not
	 */
	public static function update_option( $key, $value ) {

		if ( empty( $key ) ) {
			return false;
		}

		// Load the options.
		$plugin_options = get_option( 'recipepress_settings', array() );

		// Update the specified value in the array.
		$plugin_options[ $key ] = $value;

		// Save the options back to the DB.
		return update_option( 'recipepress_settings', $plugin_options );
	}

	/**
	 * Delete an option.
	 *
	 * Deletes the specified option.
	 * This is for developers to delete options outside the settings page.
	 *
	 * WARNING: Hooks and filters will be triggered!!
	 *
	 * @since 1.0.0
	 *
	 * @param   mixed $key    The key of the key, value pair to delete.
	 *
	 * @return bool true if the option was deleted or false if not
	 */
	public static function delete_option( $key ) {

		if ( empty( $key ) ) {
			return false;
		}

		// Load the options.
		$plugin_options = get_option( 'recipepress_settings', array() );

		// Delete the specified key.
		unset( $plugin_options[ $key ] );

		// Save the options back to the DB.
		return update_option( 'recipepress_settings', $plugin_options );
	}
}
