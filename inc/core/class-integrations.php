<?php
/**
 * Handles integrations with other plugins
 *
 * @link       https://wzymedia.com
 *
 * @since      2.1.0
 *
 * @package    Recipepress
 */

namespace Recipepress\Inc\Core;

use Recipepress\Inc\Frontend\Template;
use Recipepress\Inc\Integrations\Yoast;

/**
 * Handles integrations with other plugins
 *
 * @since      2.1.0
 *
 * @package    Recipepress
 *
 * @author     wzyMedia <wzy@outlook.com>
 */
class Integrations {

	/**
	 * The ID of this plugin.
	 *
	 * @since    2.1.0
	 *
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    2.1.0
	 *
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 2.1.0
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Adds Schema pieces to our output.
	 *
	 * @since 2.1.0
	 *
	 * @see   https://developer.yoast.com/features/schema/integration-guidelines
	 *
	 * @param array                 $pieces  Graph pieces to output.
	 * @param \WPSEO_Schema_Context $context Object with context variables.
	 *
	 * @return array Graph pieces to output.
	 */
	public function add_metadata_wpseo_graph( $pieces, $context ) {
		// If WPSEO is installed and above v14.0.
		// if ( ! is_plugin_active('wordpress-seo/wp-seo.php') && version_compare( WPSEO_VERSION, '14.0', '>' ) ) {
		if ( version_compare( WPSEO_VERSION, '14.0', '>' )
		     && Options::get_option( 'rpr_integrate_wpseo_metadata' )
		     && in_array( 'wordpress-seo/wp-seo.php', (array) get_option( 'active_plugins', array() ), true ) ) {

			$pieces[] = new Yoast( $context );
		}

		return $pieces;
	}

}
