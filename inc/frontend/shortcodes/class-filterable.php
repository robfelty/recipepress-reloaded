<?php

namespace Recipepress\Inc\Frontend\Shortcodes;

use Recipepress as NS;
use Recipepress\Inc\Common\Abstracts\Shortcode;
use Recipepress\Inc\Core\Options;

/**
 * The class handling the `rpr-filter` shortcode
 *
 * @link       https://wzymedia.com
 *
 * @since      2.0.0
 *
 * @author    Kemory Grubb
 */
class Filterable extends Shortcode {

    use NS\Inc\Common\Traits\Utilities;

    /**
     * @var array
     */
    public $taxonomies = array();

    /**
     * @var array
     */
    public $options = array();

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

        wp_enqueue_script( 'rpr-frontend-controllers' );

		// Set default values for options not set explicitly.
		$this->options = shortcode_atts(
			array(
				'count' => 20,
				'orderby' => 'date',
                'order'   => 'DESC'
			),
			$options
		);

        $this->options['nonce'] = wp_create_nonce( 'rpr-filterable' );

        wp_add_inline_script( 'rpr-frontend-controllers', 'const rprFilterableVars = ' . wp_json_encode( $this->options ) );

		return $this->render_shortcode( $this->options );
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

        $this->get_taxonomies();

        $args = array(
            'post_type' => 'rpr_recipe',
            'posts_per_page' => $options['count'],
            'orderby' => $options['orderby'],
            'order'   => $options['order'],
            'post_status' => 'publish'
        );

        $recipes = new \WP_Query( $args );

        ob_start();

        include __DIR__ . '/views/rpr-filterable-html.php';

		// Create an empty output variable.
		$output = ob_get_clean();

        if ( $output ) {

            return $output;
        }

        // No recipes.
        return __( 'There are no recipes to display.', 'recipepress-reloaded' );
    }

    /**
     * Sends more recipes via AJAX
     *
     * @since 2.0.0
     *
     * @return string
     */
    public function more_filtered_recipes() {
	    //  Nonce check
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'rpr-filterable' ) ) {
            wp_die( __( 'Failed security check', 'recipepress-reloaded' ) );
        }

        $output = '';
        $args = array(
            'post_type' => 'rpr_recipe',
            'posts_per_page' => isset( $_POST['count'] ) ? (int) $_POST['count'] : 20,
            'orderby' => isset( $_POST['orderby'] ) ? sanitize_text_field( $_POST['orderby'] ) : 'date',
            'order'   => isset( $_POST['order'] ) ? sanitize_text_field( $_POST['order'] ) : 'DESC',
            'post_status' => 'publish'
        );

        $pagenum = isset( $_POST['page'] ) ? (int) $_POST['page'] : 1;
        $args['offset'] = $pagenum > 1 ? $args['posts_per_page'] * ( $pagenum - 1 ) : 0;

        $recipes = new \WP_Query( $args );

        if ( $recipes->have_posts() ) {
            foreach( $recipes->posts as $recipe ) {
                $output .= '<div ';
                $output .= 'class="rpr-filterable-item ' . $this->rpr_recipe_html_classes( $recipe, 'string', ['rpr_ingredient'] ) . '" ';
                $output .= '>';
                $output .= '<a href="' . esc_url( get_the_permalink( $recipe->ID ) ) . '">';
                $output .= '<div class="rpr-filterable-image">';
                $output .= get_the_post_thumbnail( $recipe->ID, 'thumbnail' );
                $output .= '</div>';
                $output .= '<div class="rpr-filterable-title">';
                $output .= '<p>';
                $output .= esc_attr( $recipe->post_title );
                $output .= '</p>';
                $output .= '</div>';
                $output .= '</a>';
                $output .= '</div>';
            }

            wp_reset_postdata();
        }

        echo $output;

        wp_die();
    }

}
