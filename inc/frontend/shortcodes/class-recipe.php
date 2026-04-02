<?php

namespace Recipepress\Inc\Frontend\Shortcodes;

use Recipepress\Inc\Common\Abstracts\Shortcode;
use Recipepress\Inc\Admin\PostTypes;

/**
 * The class handling the `rpr_recipe` shortcode
 *
 * @link       https://wzymedia.com
 * @since      1.0.0
 *
 * @author    Kemory Grubb
 */
class Recipe extends Shortcode {

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

		parent::__construct( $plugin_name, $version );
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
				'id'      => 0,
				'excerpt' => 0,
				'no-desc' => 0,
			),
			$options
		);

		$output = $this->render_shortcode( $options );

		return do_shortcode( $output );
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

		$output  = '';
		$recipe  = null;
		$content = new PostTypes\Recipe( $this->plugin_name, $this->version );

		if ( 0 !== $options['id'] ) {

			// Get random recipe.
			if ( 'random' === $options['id'] ) {

				$recipe = get_posts(
					array(
						'post_type'      => 'rpr_recipe',
						'orderby'        => 'rand',
						'posts_per_page' => 1,
					)
				)[0]; // Returns an array; grab the first item.

			} else {

				$recipe = get_post( (int) $options['id'] );
			}

			if ( null !== $recipe && 'rpr_recipe' === $recipe->post_type ) {

				$GLOBALS['recipe_id'] = $recipe->ID;

				if ( 0 === (int) $options['excerpt'] && 0 === (int) $options['no-desc'] ) {

					// Embed complete recipe.
					$output = $content->render_post_type_content( $recipe );
				} elseif ( 1 === (int) $options['excerpt'] ) {

					// Embed excerpt only.
					$output = $content->render_post_type_excerpt( $recipe );
				} elseif ( 1 === (int) $options['no-desc'] ) {

					// Embed featured image only.
					$output = $content->render_post_type_excerpt( $recipe );
				}
			}
		}

		return $output;
	}

}
