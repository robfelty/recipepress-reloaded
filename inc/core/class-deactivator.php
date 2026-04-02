<?php

namespace Recipepress\Inc\Core;

/**
 * Fired during plugin deactivation
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 *
 * @author     Kemory Grubb
 **/
class Deactivator {

	/**
	 * Deactivate the plugin.
	 *
	 * Deactivate and run other actions such as flushing rewrite rules.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		delete_transient( '_rpr_welcome_screen_activation_redirect' );

		// Clear this scheduled job on deactivation.
		wp_clear_scheduled_hook( 'rpr_ping_youtube_api' );

		flush_rewrite_rules();
	}

}
