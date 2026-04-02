<?php
/**
 * The file handling the Shortcode\Ingredients class
 *
 * @since      1.0.0
 *
 * @package Recipepress
 */

namespace Recipepress\Inc\Frontend\Shortcodes;

use Recipepress\Inc\Common\Abstracts\Shortcode;

/**
 * The class handling the `rpr-ingredients` shortcode
 *
 * @link       https://wzymedia.com
 * @since      1.0.0
 *
 * @author    Kemory Grubb
 */
class Ingredients extends Shortcode {

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

		// wp_enqueue_script( 'rpr-hideseek' );

		// Set default values for options not set explicitly.
		$options = shortcode_atts(
			array(
				'header' => '1',
				'counts' => '1',
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

		$ingredients = get_terms(
			array(
				'taxonomy'   => 'rpr_ingredient',
				'hide_empty' => true,
				'header'     => true,
			)
		);

		// Create an empty output variable.
		$output = '';

		if ( $ingredients && count( $ingredients ) > 0 ) {

			// Create an index i to compare the number in the list and check for first and last item.
			$i = 0;

			// Create an empty array to take with the first letters of all headlines.
			$letters = array();

			// Walk through all the terms to build alphabet navigation.
			foreach ( $ingredients as $ingredient ) {

				if ( (bool) $options['header'] ) {
					// Add first letter headlines for easier navigation if set so.
					// Get the first letter (without special chars).
					// Check if 1st character is a number, if so, add a "#".
					$first_letter = mb_substr( remove_accents( strtoupper( $ingredient->name ) ), 0, 1 );

					// Check if we've already had a headline.
					if ( ! in_array( $first_letter, $letters, true ) ) {
						// Close list of preceding group.
						if ( 0 !== $i ) {
							$output .= '</ul>';
						}
						// Create a headline.
						$output .= '<h2 id="' . $first_letter . '"><a class="rpr-toplink" href="#top">&uarr;</a></a>';
						$output .= strtoupper( $first_letter );
						$output .= '</h2>';

						// Start new list.
						$output .= '<ul class="rpr-ingredients-list">';

						// Add the letter to the list.
						$letters[] = $first_letter;
					}
				} else {
					// Start list before first item.
					if ( 0 === $i ) {
						$output .= '<ul class="rpr-ingredients-list">';
					}
				}

				// Add the entry for the post.
				$output .= '<li class="rpr-ingredients-list-item">';
				$output .= '<a href="' . get_term_link( $ingredient ) . '">';
				$output .= esc_html( $ingredient->name );
				$output .= ( 'true' === $options['header'] ) ? ' <span>(' . absint( $ingredient->count ) . ')</span>' : null;
				$output .= '</a>';
				$output .= '</li>';

				// Increment the counter.
				$i ++;
			}
			// Close the last list.
			$output .= '</ul>';

			// Output the rendered list.
			$out  = '';
			$out .= '<a id="top"></a>';
			// $out .= '<input id="search" name="search" type="text" data-toggle="hideseek" data-list=".rpr-ingredients-list">';
			$out .= $this->the_alphabet_nav_bar( $letters );
			$out .= $output;
			$out .= $this->the_alphabet_nav_bar( $letters );

			return $out;
		}

        // No recipes.
        return __( 'There are no ingredients to display.', 'recipepress-reloaded' );
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
