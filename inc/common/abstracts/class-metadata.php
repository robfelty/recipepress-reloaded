<?php

namespace Recipepress\Inc\Common\Abstracts;

use Recipepress as NS;

/**
 * The abstract metadata class.
 *
 * @package    Recipepress
 *
 * @author     Kemory Grubb <kemory@wzymedia.com>
 */
abstract class Metadata {

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
	 * The post type
	 *
	 * @since    2.1.0
	 *
	 * @access   public
	 * @var      string $post_type
	 */
	public $post_type;

	/**
	 * The ID of the associated metabox.
	 *
	 * @since    1.0.0
	 *
	 * @access   public
	 * @var      string $metabox_id
	 */
	public $metabox_id;

	/**
	 * The meta key of the data stored in `wp_postmeta`
	 *
	 * @since   2.1.0
	 *
	 * @access   public
	 * @var      string $meta_key
	 */
	public $meta_key;

	/**
	 * The directory of the class implementing this abstract.
	 *
	 * @since    1.0.0
	 *
	 * @access   public
	 * @var      string $dir
	 */
	public $dir;

	/**
	 * Should we enqueue a JS file for this metabox?
	 *
	 * @since    1.0.0
	 *
	 * @access   public
	 * @var      bool $js
	 */
	public $js;

	/**
	 * Should we enqueue a CSS file for this metabox?
	 *
	 * @since    1.0.0
	 *
	 * @access   public
	 * @var      bool $css
	 */
	public $css;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $plugin_name The ID of this plugin.
	 * @param   string $version     The current version of this plugin.
	 * @param   string $metabox_id  The ID of the associated metabox.
	 * @param   string $meta_key    The meta key of the data stored in `wp_postmeta`
	 * @param   string $dir         The directory of the class implementing this abstract.
	 * @param   bool   $js          Should enqueue a JS file for this metabox.
	 * @param   bool   $css         Should enqueue a CSS file for this metabox.
	 */
	public function __construct( $plugin_name, $version, $metabox_id, $meta_key, $dir, $js = false, $css = false ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->post_type   = 'rpr_recipe';
		$this->metabox_id  = $metabox_id;
		$this->meta_key    = $meta_key;
		$this->dir         = $dir;
		$this->js          = $js;
		$this->css         = $css;
	}

	/**
	 * Enqueues the required scripts and styles for the metabox
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_assets() {

        $screen = function_exists( 'get_current_screen' ) ? \get_current_screen() : null;
		$name   = str_replace( '_', '-', $this->metabox_id );

		if ( $screen && 'rpr_recipe' === $screen->id ) {
			if ( $this->js ) {
				wp_enqueue_script( $name, NS\ADMIN_ASSET_URL . 'js/' . $name . '.js', array( $this->plugin_name, 'jquery' ), $this->version, false );
			}
			if ( $this->css ) {
				wp_enqueue_style( $name, NS\ADMIN_ASSET_URL . 'css/' . $name . '.css', array( $this->plugin_name ), $this->version, 'all' );
			}
		}
	}

	/**
	 * Add a metabox to the WP post edit screen
	 *
	 * @since 1.0.0
	 *
	 * @uses  add_meta_box
	 *
	 * @return bool
	 */
	abstract public function add_metabox();

	/**
	 * Register this meta information
	 *
	 * This is used in the WP REST APIs `meta` field
	 *
	 * @since 2.1.0
	 *
	 * @return void
	 */
	abstract public function register_meta();

	/**
	 * Should we display this metabox?
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	abstract protected function display_metabox();

	/**
	 * Render the contents of our metabox.
	 *
	 * This method is looking for a PHP file named after our metabox ID,
	 * inside the views folder of our implementing class.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Post $recipe The current post/recipe object.
	 * @return void
	 */
	public function render_metabox( $recipe ) {

		$view_file = $this->dir . '/views/' . str_replace( '_', '-', $this->metabox_id ) . '.php';

		if ( ! is_readable( $view_file ) ) {
			// translators: please ignore.
			printf( __( '<code>Unable to read file: %s</code>', 'recipepress-reloaded' ), esc_html( $view_file ) ); // phpcs:ignore
			return;
		}

		include $view_file;
	}

	/**
	 * Creates the nonce fields of our metabox.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function create_nonce() {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wp_nonce_field( $this->post_type, $this->metabox_id, true, false );
	}

	/**
	 * Checks the nonce of our metabox.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data An array of values sent from the _POST array.
	 *
	 * @return bool
	 */
	protected function check_nonce( $data ) {

		return isset( $data[ $this->metabox_id ] ) && wp_verify_nonce( $data[ $this->metabox_id ], $this->post_type );
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
	 * @return void
	 */
	abstract public function save_metabox_metadata( $recipe_id, $data, $recipe );

}
