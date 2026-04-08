<?php


namespace Recipepress\Inc\Frontend\Shortcodes;

defined( 'ABSPATH' ) || exit;

use Recipepress\Inc\Common\Abstracts\Shortcode;

/**
 * The class handling the `rpr-recipe` shortcode
 *
 * @link       https://wzymedia.com
 * @since      1.0.0
 *
 * @author    Kemory Grubb
 */
class Index extends Shortcode {

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
				'header'    => 'true',
				'thumbnail' => 'false',
			),
			$options
		);

		$output = $this->render_shortcode( $options );

		return $output;
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

		global $wpdb;

		$thumbs = 'true' === $options['thumbnail'] ? ' thumbnail' : '';
		$query  = "SELECT post.ID, post.post_title, thumbnail.meta_value as thumb_id
					FROM $wpdb->posts as post 
					LEFT OUTER JOIN $wpdb->postmeta as thumbnail 
    				ON post.ID = thumbnail.post_id 
    				AND thumbnail.meta_key = '_thumbnail_id' 
					WHERE 1 = 1 
					AND post.post_type = 'rpr_recipe' 
					AND post.post_status = 'publish' 
					ORDER BY post.post_title ASC";

		if ( false === ( $posts = get_transient( 'rpr_recipe_index' ) ) ) {
			// It wasn't there, so regenerate the data and save the transient.
			$posts = $wpdb->get_results( $query, OBJECT );
			set_transient( 'rpr_recipe_index', $posts, 12 * HOUR_IN_SECONDS );
			// TODO: Delete transient on new recipe published.
		}

		// Create an empty output variable.
		$output = '';

		if ( $posts && count( $posts ) > 0 ) {

			// Create an index i to compare the number in the list and check for first and last item.
			$i = 0;

			// Create an empty array to take with the first letters of all headlines.
			$letters = array();

			// Walk through all the terms to build alphabet navigation.
			foreach ( $posts as $post ) {

				if ( 'true' === $options['header'] ) {
					// Add first letter headlines for easier navigation if set so.
					// Get the first letter (without special chars).
					// Check if 1st character is a number, if so, add a "#".
					$first_letter = is_numeric( mb_substr( remove_accents( strtoupper( $post->post_title ) ), 0, 1 ) )
						? '#' : mb_substr( remove_accents( strtoupper( $post->post_title ) ), 0, 1 );

					// Check if we've already had a headline.
					if ( ! in_array( $first_letter, $letters, true ) ) {
						// Close list of preceding group.
						if ( 0 !== $i ) {
							$output .= '</div>';
						}
						// Create a headline.
						$output .= '<h2 id="' . $first_letter . '"><a class="rpr-toplink" href="#top">&uarr;</a></a>';
						$output .= strtoupper( $first_letter );
						$output .= '</h2>';

						// Start new list.
						$output .= '<div class="rpr-taxlist' . $thumbs . '">';

						// Add the letter to the list.
						$letters[] = $first_letter;
					}
				} else {
					// Start list before first item.
					if ( 0 === $i ) {
						$output .= '<div class="rpr-taxlist' . $thumbs . '">';
					}
				}

				// Add the entry for the post.
				$output .= '<div class="rpr-taxlist-item">';
				$output .= '<a href="' . get_permalink( $post->ID ) . '">';
				if ( $thumbs ) {
					$output .= '<div class="rpr-taxlist-image">';
					$output .= $post->thumb_id ? '<img src="'
											. ( wp_get_attachment_image_src( (int) $post->thumb_id, 'medium' )[0] ?: '' )
											. '" alt="' . strtolower( $post->post_title ) . '" />' : '';
					$output .= '</div>';
				}
				$output .= '<div class="rpr-taxlist-title">';
				$output .= $post->post_title;
				$output .= '</div>';
				$output .= '</a>';
				$output .= '</div>';

				// Increment the counter.
				$i ++;
			}
			// Close the last list.
			$output .= '</div>';

			// Output the rendered list.
			$out  = '';
			$out .= '<a id="top"></a>';
			$out .= 'true' === $options['header'] ? $this->the_alphabet_nav_bar( $letters ) : '';
			$out .= $output;
			$out .= 'true' === $options['header'] ? $this->the_alphabet_nav_bar( $letters ) : '';

			return $out;
		} else {
			// No recipes.
			return __( 'There are no recipes to display.', 'recipepress-reloaded' );
		}
	}

	/**
	 * Creates an list of letters of the alphabet
	 *
	 * This is used as a navigational aid used to create jump
	 * points on the list of recipes.
	 *
	 * @since 1.0.0
	 *
	 * @param array $letters The list of first letters taken from recipe titles.
	 *
	 * @return string
	 */
	public function the_alphabet_nav_bar( $letters ) {

		// An array with the (complete) alphabet, plus the '#' sign.
		$alphabet = range( 'A', 'Z' );
		array_unshift( $alphabet, '#' );

		// Create an empty output string.
		$output = '';

		// Start the list.
		$output .= '<ul class="rpr-alphabet-navigation">';

		foreach ( $alphabet as $a ) {

			// Loop through the alphabet.
			if ( $letters ) {

				if ( in_array( $a, $letters, true ) ) {
					// Active letter, so we should set a link in the nav menu.
					$output .= '<li class="active"><a href="#' . $a . '">' . $a . '</a></li>';
				} else {
					// Inactive letter, no link.
					$output .= '<li class="inactive">' . $a . '</li>';
				}
			} else {
				// Each letter active.
				$output .= '<li class="active"><a href="#' . $a . '">' . $a . '</a></li>';
			}
		}

		// End the list.
		$output .= '</ul>';

		// Return the rendered nav bar.
		return $output;
	}

}
