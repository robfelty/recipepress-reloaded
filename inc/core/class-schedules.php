<?php
/**
 * Define background tasks to be scheduled with WP Cron
 *
 * @link       https://wzymedia.com
 *
 * @since      1.0.0
 *
 * @package    Recipepress
 */

namespace Recipepress\Inc\Core;

/**
 * Handles the scheduled events to be handled by WP Cron
 *
 * @since      1.0.0
 *
 * @package    Recipepress
 * @author     wzyMedia <wzy@outlook.com>
 */
class Schedules {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Create a new Cron schedule
	 *
	 * @since 1.0.0
	 *
	 * @param array $schedules
	 *
	 * @return array
	 */
	public function create_weekly_cron( $schedules ) {

		// Adds once weekly to the existing schedules.
		$schedules['rpr_weekly'] = array(
			'interval' => 604800,
			'display' => __( 'Once Weekly', 'recipepress-reloaded' )
		);
		return $schedules;
	}

	/**
	 * Schedule our task with WP Cron
	 *
	 * @since 1.0.0
	 *
	 * @return bool | void
	 */
	public function schedule_api_ping() {

		// If the option is disabled don't schedule.
		if ( ! Options::get_option( 'rpr_ping_youtube' )) {
			return false;
		}

		// If its already scheduled, don't reschedule.
		if ( ! wp_next_scheduled( 'rpr_ping_youtube_api' ) ) {
			wp_schedule_event( time(), 'rpr_weekly', 'rpr_ping_youtube_api' );
		}
	}

	/**
	 * Pings the YouTube API
	 *
	 * The YouTube API key will be disabled by Google after a period of inactivity.
	 * This method pings the API once everyday to keep the key active.
	 *
	 * @since 1.0.0
	 *
	 * @return int|void
	 */
	public function ping_api() {

		$api_key  = Options::get_option( 'rpr_youtube_api_key' );
		$disabled = Options::get_option( 'rpr_ping_youtube' );

		// If no API key is set or the option is disabled, remove this job.
		if ( ! $api_key || ! $disabled ) {
			return wp_clear_scheduled_hook( 'rpr_ping_youtube_api' );
		}

		$url = 'https://www.googleapis.com/youtube/v3/videos?key=' . $api_key . '&part=snippet&id=dQw4w9WgXcQ';
		$res = wp_remote_retrieve_body( wp_remote_get( $url ) );

		if ( '' !== $res ) {
			Options::update_option( 'rpr_last_youtube_ping', date( 'Y-m-d H:i:s' )  );
		}
	}

}
