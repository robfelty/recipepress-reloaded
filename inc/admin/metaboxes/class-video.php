<?php

namespace Recipepress\Inc\Admin\Metaboxes;

use Recipepress\Inc\Common\Abstracts\Metadata;
use Recipepress\Inc\Core\Options;

/**
 * Saving the recipe video meta information.
 *
 * @package    Recipepress
 *
 * @author     Kemory Grubb <kemory@wzymedia.com>
 */
class Video extends Metadata {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $plugin_name The ID of this plugin.
	 * @param   string $version     The current version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version, 'rpr_video_metabox', 'rpr_recipe_video_data', __DIR__ );
	}

	/**
	 * Add a metabox to the WP post edit screen
	 *
	 * @since 1.0.0
	 *
	 * @uses  add_meta_box
	 * @return bool
	 */
	public function add_metabox() {

		if ( ! $this->display_metabox() ) {
			return false;
		}

		add_meta_box(
			$this->metabox_id,
			__( 'Recipe video', 'recipepress-reloaded' ),
			array( $this, 'render_metabox' ),
			$this->post_type,
			'side',
			'high'
		);

		return true;
	}

	/**
	 * Should we display this metabox.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	protected function display_metabox() {

		return Options::get_option( 'rpr_use_video_meta' );
	}

	/**
	 * Check the presence of, sanitizes then saves the metabox's data.
	 *
	 * @since 1.0.0
	 *
	 * @uses  update_post_meta()
	 * @uses  wp_verify_nonce()
	 * @uses  sanitize_text_field()
	 *
	 * @param int      $recipe_id The post ID of the recipe post.
	 * @param array    $data      The data passed from the post custom metabox.
	 * @param \WP_Post $recipe    The recipe object this data is being saved to.
	 *
	 * @return bool|int
	 */
	public function save_metabox_metadata( $recipe_id, $data, $recipe ) {

		if ( ! $this->check_nonce( $data ) ) {
			return false;
		}

		$video_data = array();

		if ( isset( $data[ $this->meta_key ] ) ) {
			foreach ( $data[ $this->meta_key ] as $key => $value ) {
				if ( is_array( $value ) ) {
					$new_value = array();
					foreach ( $value as $_value ) {
						$new_value[] = sanitize_text_field( $_value );
					}
					$video_data[ $key ] = $new_value;
				} else {
					$video_data[ $key ] = sanitize_text_field( $value );
				}
			}
		}

		$old = get_post_meta( $recipe_id, $this->meta_key, true );
		$new = $video_data;

		if ( '' === $new['video_url'] && ! empty( $old['video_url'] ) ) {
			delete_post_meta( $recipe_id, $this->meta_key );
		} elseif ( $new !== $old ) {
			update_post_meta( $recipe_id, $this->meta_key, $new );
		}

		return $recipe_id;
	}

	/**
	 * Fetches the video data from YouTube's API.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function fetch_video_data() {

		check_ajax_referer( 'rpr-video-nonce', 'rpr_video_nonce' );

		$body = null;

		if ( empty( $_POST['action'] ) || 'fetch_video_data' !== $_POST['action'] ) {
			wp_send_json_error();
		}

		if ( isset( $_POST['video_service'] ) && 'YouTube' === $_POST['video_service'] ) {
            // The YT API key will be disabled by Google after a period of inactivity.
            // If this happens its best to create a new project and get a new key.
            $yt_api_key = Options::get_option( 'rpr_youtube_api_key' );
            $yt_url     = isset( $_POST['video_id'] ) ? 'https://www.googleapis.com/youtube/v3/videos?key='
                . $yt_api_key . '&part=snippet&part=statistics&id='
                . sanitize_text_field( wp_unslash( $_POST['video_id'] ) ) : '';
            $body       = wp_remote_retrieve_body( wp_remote_get( $yt_url ) );
        }

        if ( isset( $_POST['video_service'] ) && 'Vimeo' === $_POST['video_service'] ) {
            $v_url = isset( $_POST['video_id'] ) ? 'https://api.vimeo.com/videos/' . sanitize_text_field( wp_unslash( $_POST['video_id'] ) ) : '';
            $args = array(
                'headers'     => array(
                    'Authorization' => 'Bearer ' . Options::get_option( 'rpr_vimeo_token' ),
                ),
            );
            $body = wp_remote_retrieve_body( wp_remote_get( $v_url, $args ) );
        }

		if ( null === $body || is_wp_error( $body ) ) {
			wp_send_json_error();
		}

		wp_send_json_success( json_decode( $body, true ) );
	}

	/**
	 * Register this meta information
	 *
	 * This is used in the WP REST API's `meta` field
	 *
	 * @since 2.1.0
	 *
	 * @return void
	 */
	public function register_meta() {
		// TODO: Implement register_meta() method.
	}
}
