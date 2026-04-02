<?php
/**
 * Handles saving the recipe source meta information.
 *
 * @package    Recipepress
 * @author     Kemory Grubb <kemory@wzymedia.com>
 */

namespace Recipepress\Inc\Admin\Metaboxes;

use Recipepress as NS;
use Recipepress\Inc\Core\Options;

/**
 * Create our own link modal
 *
 * The default WP link modal has too many quirks,
 * so we have re-implemented it
 *
 * @since   2.0.0
 *
 * @package Recipepress
 *
 * @author  Kemory Grubb <kemory@wzymedia.com>
 */
class Link {

	use NS\Inc\Common\Traits\Utilities;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   2.0.0
	 *
	 * @param   string $plugin_name The ID of this plugin.
	 * @param   string $version     The current version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

	}

    /**
     * Add our own link modal to the recipe edit page
     *
     * @since 2.0.0
     *
     * @return void
     */
    public function add_link_modal() {

        $screen = function_exists( 'get_current_screen' ) ? \get_current_screen() : null;

        if ( $screen && 'rpr_recipe' === $screen->id ) {
            include_once __DIR__.'/views/rpr-link-modal.php';
        }
    }

    /**
     * Send the latest recipes, posts and pages
     * to out link modal
     *
     * @since 2.0.0
     *
     * @return void
     */
    public function latest_recipes_posts() {

        if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( $_POST['nonce'], 'rpr-link-modal-nonce' ) ) {
            return;
        }

        $output = '';
        $args = [
            'post_type' => ['post', 'page', 'rpr_recipe'],
            'posts_per_page' => 10,
            'orderby' => 'date',
            'post_status' => 'publish'
        ];
        $pagenum = isset( $_POST['page'] ) ? (int) $_POST['page'] : 1;
        $args['offset'] = $pagenum > 1 ? $args['posts_per_page'] * ( $pagenum - 1 ) : 0;

        $posts = new \WP_Query( $args );

        wp_reset_postdata();

        foreach( $posts->posts as $post ) {
            $output .= '<li data-action="click->rpr-link#selectListItem" ';
            $output .= 'data-permalink="' . get_the_permalink( $post->ID ) . '" ';
            $output .= 'data-title="' . $post->post_title . '" ';
            $output .= '>';
            $output .= '<span>' . $post->post_title . '</span>';
            $output .= ' <span style="color:#a3a3a3;">' . str_replace( 'rpr_', '', $post->post_type ) . '</span>';
            $output .= '</li>';
        }

        echo $output;

        wp_die();
    }

    /**
     * Search our recipes, posts and pages
     *
     * @since 2.0.0
     *
     * @return void
     */
    public function search_recipes_posts() {

        if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( $_POST['nonce'], 'rpr-link-modal-nonce' ) ) {
            return;
        }

        $output = '';
        $search = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
        $posts  = new \WP_Query([
            's' => $search
        ]);

        wp_reset_postdata();

        foreach( $posts->posts as $post ) {
            $output .= '<li data-action="click->link-modal#selectListItem" ';
            $output .= 'data-permalink="' . get_the_permalink( $post->ID ) . '" ';
            $output .= 'data-title="' . $post->post_title . '" ';
            $output .= '>';
            $output .= '<span>' . $post->post_title . '</span>';
            $output .= ' <span style="color:#a3a3a3;">' . str_replace( 'rpr_', '', $post->post_type ) . '</span>';
            $output .= '</li>';
        }

        echo $output;

        wp_die();
    }


}
