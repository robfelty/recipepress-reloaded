<?php


namespace Recipepress\Inc\Frontend\Shortcodes;


use Recipepress\Inc\Common\Abstracts\Shortcode;
use Recipepress\Inc\Frontend\Rating;

/**
 * Class Ratings
 *
 * @package Recipepress\Inc\Frontend\Shortcodes
 */
class Ratings extends Shortcode {

    /**
     * Initialize the class and set its properties.
     *
     * @since 2.0.0
     *
     * @param string $plugin_name The ID of this plugin.
     * @param string $version The current version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version     = $version;

        parent::__construct( $this->plugin_name, $this->version );
    }

    /**
     * Creates the shortcode for use by WordPress
     *
     * @since 2.0.0
     *
     * @uses \do_shortcode()
     *
     * @param array $options The shortcode options.
     *
     * @return mixed
     */
    public function the_shortcode( $options ) {
        // Set default values for options not set explicitly.
        $options = shortcode_atts(
            array(
                'empty' => 1,
            ),
            $options
        );

        return $this->render_shortcode( $options );
    }

    /**
     * Renders the markup of our shortcode content
     *
     * @since 2.0.0
     *
     * @param array $options The shortcode options.
     *
     * @return mixed
     */
    public function render_shortcode( $options ) {
        $recipe_id = ! empty( $GLOBALS['recipe_id'] ) ? (int) $GLOBALS['recipe_id'] : get_the_ID();

        return ( new Rating( $this->plugin_name, $this->version ) )->rate_calculate( $recipe_id );
    }
}