<?php

namespace Recipepress\Inc\Common\Abstracts;

/**
 * The abstract post type class.
 *
 * @package    Recencio_Pro
 * @subpackage Recencio_Pro/inc/common/abstracts
 * @author     Kemory Grubb <kemory@wzymedia.com>
 */
abstract class Taxonomy {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 * @param   string $plugin_name     The ID of this plugin.
	 * @param   string $version         The current version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Registers the custom taxonomies.
	 *
	 * @since   1.0.0
	 * @uses    register_post_type()
	 * @return  void
	 */
	abstract public function register_taxonomy();

}
