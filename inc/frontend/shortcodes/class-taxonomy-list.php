<?php

namespace Recipepress\Inc\Frontend\Shortcodes;

use Recipepress\Inc\Common\Abstracts\Shortcode;

/**
 * The class handling the `[rpr-tax-list]` shortcode
 *
 * @link       https://wzymedia.com
 * @since      1.0.0
 *
 * @author    Kemory Grubb
 */
class Taxonomy_List extends Shortcode {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
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
	 * @since 1.0.0
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
				'count'    => 8,
				'taxonomy' => 'course',
			),
			$options
		);

		return $this->render_shortcode( $options );
	}

	/**
	 * Renders the markup of our shortcode content
	 *
	 * @since 1.0.0
	 *
	 * @param array $options The shortcode options.
	 *
	 * @return mixed
	 */
	public function render_shortcode( $options ) {

		$all_posts = array();
		$terms     = false;
		$exclude   = array();
		$taxonomy  = '';

		if ( 'n/a' !== $options['taxonomy'] && '' !== $options['taxonomy'] ) {
			$taxonomy = false !== strpos( $options['taxonomy'], 'rpr_' ) ? $options['taxonomy'] : 'rpr_' . $options['taxonomy'];
			// Get the terms of the selected taxonomy.
			$terms = get_terms(
				$taxonomy,
				array(
					'orderby' => 'name',
					'order'   => 'ASC',
					'number'  => 100,
				)
			);
		}

		if ( ! is_wp_error( $terms ) && count( $terms ) > 0 ) {
			foreach ( $terms as $term ) {
				$all_posts[ $term->term_id ] = get_posts(
					array(
						'post_type'   => 'rpr_recipe',
						'numberposts' => ( (int) $options['count'] > 100 ) ? 100 : (int) $options['count'],
						'tax_query'   => array(
							array(
								'taxonomy' => $taxonomy,
								'field'    => 'id',
								'terms'    => $term->term_id,
							),
						),
						'exclude'     => $exclude,
					)
				);
				foreach ( $all_posts[ $term->term_id ] as $post ) {
					$exclude[] = $post->ID;
				}
			}
		}

		// Create an empty output variable.
		$output = '';
		$links  = array();

		if ( $all_posts && count( $all_posts ) > 0 ) {

			// Create an index i to compare the number in the list and check for first and last item.
			$i = 0;

			// Walk through all the terms to build alphabet navigation.
			foreach ( $all_posts as $term_id => $posts ) {
				$term = get_term( $term_id, $taxonomy );
				$url  = get_term_link( $term_id, $taxonomy );
				// translators: please ignore.
				$links[] = '<a href="' . esc_url( $url ) . '" class="rpr-tax-link">' . sprintf( __( 'View more %s recipes', 'recipepress-reloaded' ), strtolower( $term->name ) ) . '</a>';

				if ( null === $term && is_wp_error( $term ) ) {
					return null;
				}

				// Close list of preceding group.
				if ( 0 !== $i ) {
					$output .= '</div> <!-- end .rpr-tax-grid -->';
					$output .= $links[ $i - 1 ];
					$output .= '</div> <!-- end .rpr-tax-container -->';
				}
				// Create a headline.
				$output .= '<div class="rpr-tax-container"> <!-- start .rpr-tax-container -->';
				$output .= '<h2 id="' . $term->slug . '"><a class="rpr-toplink" href="#top">&uarr;</a></a>';
				$output .= strtoupper( $term->name );
				$output .= '</h2>';

				// Start new grid.
				$output .= '<div class="rpr-tax-grid"> <!-- start .rpr-tax-grid -->';

				foreach ( $posts as $post ) {
					// Add the entry for the post.
					$output .= '<div class="rpr-tax-grid-item">';
					$output .= '<a href="' . get_permalink( $post->ID ) . '">';
					$output .= '<div class="rpr-tax-grid-image">';
					$output .= get_the_post_thumbnail( $post->ID, 'medium' );
					$output .= '</div>';
					$output .= '<div class="rpr-tax-grid-title">';
					$output .= $post->post_title;
					$output .= '</div>';
					$output .= '</a>';
					$output .= '</div>';
				}

				// Increment the counter.
				$i ++;
			}
			// Close the last grid.
			$output .= '</div> <!-- last .rpr-tax-grid -->';
			$output .= $links[ $i - 1 ];
			$output .= '</div> <!-- last .rpr-tax-container -->';

			// Output the rendered list.
			$out  = '';
			$out .= '<a id="top"></a>';
			$out .= $output;

			return $out;
		} else {
			// translators:please ignore.
			return sprintf( __( 'There are no recipes to display for the "%s" taxonomy.', 'recipepress-reloaded' ), $taxonomy );
		}
	}

}
