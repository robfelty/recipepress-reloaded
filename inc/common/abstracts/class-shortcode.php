<?php

namespace Recipepress\Inc\Common\Abstracts;

/**
 * The abstract metadata class.
 *
 * @package    Recipepress
 *
 * @author     Kemory Grubb <kemory@wzymedia.com>
 */
abstract class Shortcode {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @access   protected
	 * @var      string $plugin_name The ID of this plugin.
	 */
	protected $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @access   protected
	 * @var      string $version The current version of this plugin.
	 */
	protected $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_name The ID of this plugin.
	 * @param string $version     The current version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Creates the shortcode for use by WordPress
	 *
	 * @since 1.0.0
	 *
	 * @uses \do_shortcode()
	 *
	 * @param array $options The shortcode options.
	 *
	 * @return mixed
	 */
	abstract public function the_shortcode( $options );

	/**
	 * Renders the markup of our shortcode content
	 *
	 * @since 1.0.0
	 *
	 * @param array $options The shortcode options.
	 *
	 * @return mixed
	 */
	abstract public function render_shortcode( $options );
}
