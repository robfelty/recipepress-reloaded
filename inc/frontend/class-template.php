<?php


namespace Recipepress\Inc\Frontend;

defined( 'ABSPATH' ) || exit;

use Recipepress as NS;
use Recipepress\Inc\Core\Options;
use Recipepress\Inc\Libraries\Pluralizer\Pluralizer;
use Recipepress\Inc\Common\Utilities\Icons;
use Recipepress\Inc\Common\Traits\Utilities;
use Recipepress\Inc\Common\Traits\Values;

/**
 * The template functionalities of the plugin.
 *
 * @link       https://wzymedia.com
 * @since      1.0.0
 *
 * @package    Recipepress
 */

/**
 * The template functionalities of the plugin.
 *
 * Defines the methods used to render the different sections of our recipes such as
 * ingredient list, instruction, notes and the recipe JSON-LD schema data.
 *
 * @package    Recipepress
 *
 * @author     wzyMedia <wzy@outlook.com>
 */
class Template {

	use Utilities;
	use Values;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since  1.0.0
	 *
	 * @access protected
	 * @var    string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Plugin options
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @var    array $option Our saved plugin option/settings
	 */
	public $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->options = Options::get_options();

		$this->content_filter();
	}

	/**
	 * Add own own content filter to avoid conflicts
	 *
	 * Usually when other plugins use `the_content` this causes conflict with our
	 * usuage of `the_content`. This method avoid that.
	 *
	 * @since 1.7.0
	 *
	 * @see https://www.billerickson.net/code/duplicate-the_content-filters/
	 *
	 * @return void
	 */
	private function content_filter() {
		global $wp_embed;

		add_filter( 'rpr_content', array( $wp_embed, 'run_shortcode' ), 8 );
		add_filter( 'rpr_content', array( $wp_embed, 'autoembed' ), 8 );
		add_filter( 'rpr_content', 'do_blocks' );
		add_filter( 'rpr_content', 'wptexturize' );
		add_filter( 'rpr_content', 'convert_smilies' );
		add_filter( 'rpr_content', 'convert_chars' );
		add_filter( 'rpr_content', 'wpautop' );
		add_filter( 'rpr_content', 'shortcode_unautop' );
		add_filter( 'rpr_content', 'do_shortcode' );
		add_filter( 'rpr_content', 'wp_filter_content_tags' );

		if ( function_exists( 'wp_replace_insecure_home_url' ) ) {
			add_filter( 'rpr_content', 'wp_replace_insecure_home_url' );
		}
	}

	/**
	 * Get a setting
	 *
	 * @since 1.6.1
	 *
	 * @param string $setting The setting we are looking for
	 * @param string $default The setting default
	 *
	 * @return mixed
	 */
	private function get_setting( $setting = null, $default = null ) { // TODO: Is this necessary???

		return ( $setting && ! empty( $this->options[ $setting ] ) ) ? $this->options[ $setting ] : $default;
	}

	/**
	 * Fetches the custom meta that is attached to a recipe post
	 *
	 * @since 1.0.0
	 *
	 * @uses \get_post_meta()
	 *
	 * @param int $recipe_id The post ID of a recipe.
	 *
	 * @return array
	 */
	public function get_the_recipe_meta( $recipe_id ) {

		$metadata = array();
		$data = get_post_meta( $recipe_id );

		if ( ! $data ) {
			return $metadata;
		}

		foreach ( $data as $key => $value ) {
			$metadata[ $key ] = maybe_unserialize( $value[0] );
		}

		// Remove data not related to our recipe.
		$metadata = array_filter(
			$metadata,
			function( $key ) {
				return false !== strpos( $key, 'rpr' );
			},
			ARRAY_FILTER_USE_KEY
		);

		return apply_filters( 'rpr/recipe/metadata', $metadata, $recipe_id );
	}

	/**
	 * Gets the public rating of a recipe
	 *
	 * @since 1.0.0
	 *
	 * @param int    $recipe_id The post ID of a recipe.
	 * @param string $value     The rating value we are seeking.
	 *                          Values are 'avg', 'min', 'max' and 'count'.
	 *
	 * @return int|float
	 */
	public function get_the_recipe_rating( $recipe_id, $value = 'avg' ) {

		$rating = new Rating( $this->plugin_name, $this->version );
		return $rating->rating_info( $value, $recipe_id );
	}

	/**
	 * Creates a print button
	 *
	 * @since 1.0.0
	 *
	 * @param string $icon       The `rpr-icon` string of the icon name.
	 * @param string $print_area The print area HTML class.
	 *
	 * @return string
	 */
	public function get_the_recipe_print_button( $icon, $print_area = false ) {

		$enable_btn  = $this->get_setting( 'rpr_recipe_template_print_btn' );
		$button_text = $enable_btn ? $this->get_setting( 'rpr_recipe_template_print_btn_text' ) : __( 'Print Recipe', 'recipepress-reloaded' );

		$out = '';

		if ( $enable_btn && is_singular( 'rpr_recipe' ) ) {
			$out .= '<a href="#print" class="rpr-print-recipe no-print" ';
			$out .= 'title="' . esc_html__( 'Print this recipe', 'recipepress_reloaded' ) . '"';
			$out .= $print_area ? 'data-print-area="' . apply_filters( 'rpr_print_area_class', sanitize_html_class( $print_area ) ) . '"' : '';
			$out .= '>';
			$out .= ( $this->get_setting( 'rpr_recipe_template_use_icons' ) && $icon )
				? Icons::get_the_icon( $icon )
				: '';
			$out .= apply_filters( 'rpr/frontend/template/print_button_text', esc_html( $button_text ) );
			$out .= '</a>';
		}

		return $out;
	}

	/**
	 * Prints a print button
	 *
	 * @since 1.0.0
	 *
	 * @param string $icon       The `rpr-icon` string of the icon name.
	 * @param string $print_area The print area HTML class.
	 *
	 * @return void
	 */
	public function the_recipe_print_button( $icon, $print_area = false ) {
		echo $this->get_the_recipe_print_button( $icon, $print_area );
	}

	// ======================================================================
	// 0001 RECIPE SECTION HEADINGS
	// ======================================================================

	/**
	 * Generates the ingredients headline.
	 *
	 * @param string $heading The title of our recipe section headline.
	 * @param string $icons   The `rpr-icon` string of the icon name.
	 *
	 * @return string
	 */
	public function get_the_rpr_recipe_headline( $heading, $icons = '' ) {

		$output = '';
		$icon   = ( $this->options[ 'rpr_recipe_template_use_icons' ] && $icons )
			? Icons::get_the_icon( $icons )
			: null;
		$htag   = apply_filters( 'rpr/frontend/template/headline_htag', $this->is_recipe_embedded() ? 'h3' : 'h2' );
		$string = '<%1$s id="%2$s">%3$s%4$s</%5$s>';

		$output .= sprintf(
			$string,
			$htag,
			sanitize_html_class( strtolower( $heading ) ),
			$icon,
			apply_filters( 'rpr/frontend/template/headline', sanitize_text_field( $heading ) ),
			$htag
		);

		return $output;
	}

	/**
	 * Prints the ingredients headline.
	 *
	 * @param string $heading The title of our recipe section headline.
	 * @param string $icons   Should icons be displayed before the heading.
	 *
	 * @return void
	 */
	public function the_rpr_recipe_headline( $heading, $icons = '' ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_the_rpr_recipe_headline( $heading, $icons );
	}

	// ======================================================================
	// 0002 RECIPE TAXONOMIES
	// ======================================================================

	/**
	 * Generates the list of all taxonomies and terms used in a recipe.
	 *
	 * @param int    $recipe_id The post ID of a recipe.
	 * @param string $icons     The `rpr-icon` string of the icon name.
	 * @param bool   $label     Should the taxonomy be displayed as a label.
	 * @param string $sep       Separator used for multiple terms.
	 *
	 * @return string
	 */
	public function get_the_rpr_taxonomy_list( $recipe_id, $icons = '', $label = true, $sep = '/' ) {

		$taxonomies = $this->get_custom_taxonomies();

		$output     = '';
		$output    .= '<ul class="rpr-term-list">';

		if ( $this->options[ 'rpr_enable_categories' ] && $this->options[ 'rpr_show_categories' ] ) {
			$output .= $this->get_the_rpr_taxonomy_terms( $recipe_id, 'category', $label, $sep, $icons );
			$output .= ' ';
		}

		if ( $this->options[ 'rpr_enable_tags' ] && $this->options[ 'rpr_show_tags' ] ) {
			$output .= $this->get_the_rpr_taxonomy_terms( $recipe_id, 'post_tag', $label, $sep, $icons );
			$output .= ' ';
		}

		foreach ( $taxonomies as $key => $taxonomy ) {
			if ( isset( $this->options[ 'rpr_' . $taxonomy['tax_settings']['slug'] . '_show_front' ] )
                && $this->options[ 'rpr_' . $taxonomy['tax_settings']['slug'] . '_show_front' ] ) {
				$output .= $this->get_the_rpr_taxonomy_terms( $recipe_id, $taxonomy['tax_settings']['slug'], $label, $sep, $icons );
			}
		}

		$output     .= '</ul>';

		return $output;
	}

	/**
	 * Generates the list of all taxonomies and terms used in a recipe.
	 *
	 * @param int    $recipe_id The post ID of a recipe.
	 * @param string $icons     The `rpr-icon` string of the icon name.
	 * @param bool   $label     Should the taxonomy be displayed as a label.
	 * @param string $sep       Separator used for multiple terms.
	 *
	 * @return void
	 */
	public function the_rpr_taxonomy_list( $recipe_id, $icons = '', $label = true, $sep = '/' ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_the_rpr_taxonomy_list( $recipe_id, $icons, $label, $sep );
	}

	/**
	 * Get all the terms used with a taxonomy by recipe ID.
	 *
	 * @param int    $recipe_id The post ID of a recipe.
	 * @param string $taxonomy  The taxonomy of of our term.
	 * @param bool   $label     Should the taxonomy be displayed as a label.
	 * @param string $sep       Separator used for multiple terms.
	 * @param string $icons     The `rpr-icon` string of the icon name.
	 * @param bool   $link      Should the terms be wrapped in an anchor tag.
	 * @param string $tag       The HTML tag we are outputting, defaults to the `<li>` tag.
	 *
	 * @return null|string
	 */
	public function get_the_rpr_taxonomy_terms( $recipe_id, $taxonomy, $label, $sep, $icons = '', $link = true, $tag = 'li' ) {

		$output   = '';
		$taxonomy = taxonomy_exists( $taxonomy ) ? $taxonomy : 'rpr_' . $taxonomy; // Because previous plugin versions did not prepend 'rpr'.
		$terms    = get_the_term_list( $recipe_id, $taxonomy, '<span class="rpr-tax-term">', $sep, '</span>' );
		$tax      = get_taxonomy( $taxonomy );

		if ( false === $terms || false === $tax || is_wp_error( $terms ) ) {
			return null;
		}

		$count     = count( get_the_terms( $recipe_id, $taxonomy ) );
		$tax_label = $count > 1 ? $tax->labels->name : $tax->labels->singular_name;

		if ( ! $link ) {
			$terms = wp_strip_all_tags( $terms );
		}

		$icon = ( $this->options[ 'rpr_recipe_template_use_icons' ] && $icons )
				? Icons::get_the_icon( $icons )
				: '';

		$prefix = '';
		if ( $label ) {
			$prefix = '<span class="rpr-tax-name">' . apply_filters( 'rpr/frontend/template/taxonomy_label', $tax_label, $tax, $recipe_id ) . ': </span>';
		}

		$output .= sprintf( '<%4$s class="rpr-term-item">%1$s%2$s%3$s</%4$s>', $icon, $prefix, $terms, $tag );

		return $output;
	}

	/**
	 * Prints the terms used with a taxonomy by recipe ID.
	 *
	 * @param int    $recipe_id The post ID of a recipe.
	 * @param string $taxonomy  The taxonomy of of our term.
	 * @param bool   $label     Should the taxonomy be displayed as a label.
	 * @param string $sep       Separator used for multiple terms.
	 * @param string $icons     The `rpr-icon` string of the icon name.
	 * @param bool   $link      Should the terms be wrapped in an anchor tag.
	 * @param string $tag       The HTML tag we are outputting, defaults to the `<li>` tag.
	 *
	 * @return void
	 */
	public function the_rpr_taxonomy_terms( $recipe_id, $taxonomy, $label, $sep, $icons = '', $link = true, $tag = 'li' ) {
		echo $this->get_the_rpr_taxonomy_terms( $recipe_id, $taxonomy, $label, $sep, $icons, $link, $tag );
	}

	/**
	 * Get the first term of a taxonomy by recipe ID
	 *
	 * @param int    $recipe_id The post ID of a recipe.
	 * @param string $taxonomy  The taxonomy of of our term.
	 * @param int    $output    First term = 0, Array = 1, String = 2, Default = 3
	 *
	 * @return mixed
	 */
	public function get_the_rpr_taxonomy_term( $recipe_id, $taxonomy, $output = 0 ) {
		global $wpdb;
		$terms = get_the_terms( $recipe_id, $taxonomy );

		if ( ( false === $terms || is_wp_error( $terms ) ) && 3 !== $output ) {
			return null;
		}

		if ( 0 === $output ) {
			return wp_list_pluck( $terms, 'name' )[0];
		}

		if ( 1 === $output ) {
			return wp_list_pluck( $terms, 'name' );
		}

		if ( 2 === $output ) {
			return implode( ', ', wp_list_pluck( $terms, 'name' ) );
		}

		if ( 3 === $output ) {

			if ( false !== $terms || is_wp_error( $terms ) ) {
				return wp_list_pluck( $terms, 'name' )[0];
			}

			$result = $wpdb->get_var(
				$wpdb->prepare(
                    "SELECT $wpdb->terms.name
					FROM $wpdb->terms
					JOIN $wpdb->term_taxonomy ON $wpdb->terms.term_id = $wpdb->term_taxonomy.term_id
					JOIN $wpdb->termmeta ON $wpdb->term_taxonomy.term_id = $wpdb->termmeta.term_id
                    WHERE $wpdb->term_taxonomy.taxonomy = %s AND $wpdb->termmeta.meta_key = 'rpr_default_term'",
					$taxonomy
				)
			);

			return $result ?: null;
		}

		return false;
	}

	// ======================================================================
	// 0003 RECIPE DESCRIPTION
	// ======================================================================

	/**
	 * Gets the recipe description as entered in WordPress
	 * post editor.
	 *
	 * @since 1.0.0
	 *
	 * @param int  $recipe_id The current recipe's post ID.
	 * @param bool $wrapper   Output a wrapper div
	 *
	 * @return string
	 */
	public function get_the_recipe_description( $recipe_id, $wrapper = true ) {

		$read_more = Options::get_option( 'rpr_excerpt_read_more' );

		$output = '';
		$output .= $wrapper ? '<div class="rpr_description rpr-description">' : ''; // For legacy reasons.
		$output .= apply_filters( 'rpr_content', get_the_content( $read_more, false, $recipe_id ) );
		$output .= $wrapper ? '</div>' : '';

		return $output;
	}

	/**
	 * Prints the recipe description as entered in WordPress
	 * post editor.
	 *
	 * @since 1.0.0
	 * @param int $recipe_id The current recipe's post ID.
	 *
	 * @return void
	 */
	public function the_recipe_description( $recipe_id, $wrapper = true ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_the_recipe_description( $recipe_id, $wrapper );
	}

	/**
	 * Gets the recipe description as entered in WordPress
	 * post editor.
	 *
	 * @since 1.0.0
	 * @param int $recipe_id The current recipe's post ID.
	 *
	 * @return string
	 */
	public function get_the_recipe_excerpt( $recipe_id ) {

		$output    = '';
		$output   .= '<div class="rpr-excerpt">';

		// We need to check this or we'll get an infinite loop with embedded recipes.
		if ( $this->is_recipe_embedded() ) {
			$output .= wpautop( wptexturize( get_post_field( 'post_excerpt', $recipe_id ) ) );
		} else {
			$output .= wpautop( wptexturize( get_the_excerpt( $recipe_id ) ) );
		}
		$output .= '</div>';

		return $output;
	}

	/**
	 * Gets a better version of excerpt of a recipe by ID
	 *
	 * @since 1.11.0
	 *
	 * @param    $recipe_id  int     The ID or object of the post to get the excerpt of
	 * @param    $length     int     The length of the excerpt in words
	 * @param    $tags       string  The allowed HTML tags. These will not be stripped out
	 * @param    $extra      string  Text to append to the end of the excerpt
	 *
	 * @return string
	 */
	public function get_the_better_recipe_excerpt( $recipe_id, $length = 35, $tags = '<a><em><strong>', $extra = '...' ) {

		$recipe = get_post( $recipe_id );

		if ( null === $recipe ) {
			return false;
		}

		if ( has_excerpt( $recipe->ID ) ) {
			$recipe_excerpt = $recipe->post_excerpt;
			return apply_filters( 'rpr/frontend/template/excerpt', $recipe_excerpt );
		}

		$recipe_excerpt = $recipe->post_content;
		$recipe_excerpt = strip_shortcodes( strip_tags( $recipe_excerpt, $tags ) );
		$recipe_excerpt = preg_split( '/\b/', $recipe_excerpt, $length * 2 + 1 );
		$excerpt_waste  = array_pop( $recipe_excerpt );
		$recipe_excerpt = implode( $recipe_excerpt );
		$recipe_excerpt .= $extra;

		return apply_filters( 'rpr/frontend/template/excerpt', $recipe_excerpt );
	}

	/**
	 * Prints the recipe description as entered in WordPress
	 * post editor.
	 *
	 * @since 1.0.0
	 * @param int $recipe_id The current recipe's post ID.
	 *
	 * @return void
	 */
	public function the_recipe_excerpt( $recipe_id ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_the_recipe_excerpt( $recipe_id );
	}

	// ======================================================================
	// 0003 RECIPE INGREDIENTS
	// ======================================================================

	/**
	 * Generates a list of the ingredients in a recipe.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $recipe_id An array of recipe custom metadata.
	 * @param string $ul_icons The `rpr-icon` string of the icon name.
	 * @param string $li_icons The `rpr-icon` string of the icon name.
	 *
	 * @return string
	 */
	public function get_the_rpr_recipe_ingredients( $recipe_id, $ul_icons = '', $li_icons = '' ) {

		$metadata = $this->get_the_recipe_meta( $recipe_id );

		if ( null === $metadata ) {
			return null;
		}

		$output      = '';

		if ( ! empty( $metadata['rpr_recipe_ingredients'] ) && is_array( $metadata['rpr_recipe_ingredients'] ) ) {

			$i = 0;

			foreach ( $metadata['rpr_recipe_ingredients'] as $ingredient ) {

				if ( isset( $ingredient['grouptitle'] ) ) {
					$output .= $this->rpr_render_ingredient_grouptitle( $ingredient, $ul_icons );
				} else {
					// Start the UL on the first item.
					if ( 0 === $i ) {
						$output .= '<ul class="rpr-ingredient-list">';
					}

					// Render the ingredient line.
					$output .= $this->rpr_render_ingredient_line( $ingredient, $li_icons );

					// Close the UL on the last item.
					if ( isset( $ingredient['sort'] ) && count( $metadata['rpr_recipe_ingredients'] ) === (int) $ingredient['sort'] ) {
						$output .= '</ul>';
					}
				}

				$i ++;
			}
			// Close the UL on the last item.
			$output .= '</ul>';
		} else {
			// Issue a warning, if there are no ingredients for the recipe.
			$output .= '<p class="warning">' . __( 'No ingredients could be found for this recipe.', 'recipepress-reloaded' ) . '</p>';
		}

		// Return the rendered ingredient list.
		return apply_filters( 'rpr/frontend/template/ingredients', $output, $recipe_id );
	}

	/**
	 * Generates a list of the ingredients in a recipe.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $recipe_id An array of recipe custom metadata.
	 * @param string $ul_icons The `rpr-icon` string of the icon name.
	 * @param string $li_icons The `rpr-icon` string of the icon name.
	 *
	 * @return void
	 */
	public function the_rpr_recipe_ingredients( $recipe_id, $ul_icons = '', $li_icons = '' ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_the_rpr_recipe_ingredients( $recipe_id, $ul_icons, $li_icons );
	}

	/**
	 * Generates the ingredient group name title.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $ingredient A collection of ingredient data.
	 * @param string $icons  The `rpr-icon` string of the icon name.
	 *
	 * @return string
	 */
	public function rpr_render_ingredient_grouptitle( array $ingredient, $icons = '' ) {

		$output = '';

		if ( 0 === (int) $ingredient['sort'] ) {
			// Do not close the ingredient list of the previous group if this is
			// the first group.
			$output .= '';
		} else {
			// Close close the ingredient list of the previous group.
			$output .= '</ul>';
		}

		// Create the headline for the ingredient group.
		if ( $this->is_recipe_embedded() ) {
			// Fourth level headline for embedded recipe.
			$output .= '<h4 class="rpr-ingredient-group-title">';
			// Add an icon before the item.
			$output .= ( $this->options[ 'rpr_recipe_template_use_icons' ] && $icons ) ? Icons::get_the_icon( $icons ) : '';
			$output .= esc_html( $ingredient['grouptitle'] );
			$output .= '</h4>';
		} else {
			// Third level headline for standalone recipes.
			$output .= '<h3 class="rpr-ingredient-group-title">';
			// Add an icon before the item.
			$output .= ( $this->options[ 'rpr_recipe_template_use_icons' ] && $icons ) ? Icons::get_the_icon( $icons ) : '';
			$output .= esc_html( str_replace( '# ', '', $ingredient['grouptitle'] ) );
			$output .= '</h3>';
		}

		// Start the list for this ingredient group.
		$output .= '<ul class="rpr-ingredient-list">';

		// Return the rendered output.
		return $output;
	}

	/**
	 * Render the actual ingredient line
	 *
	 * @since 1.0.0
	 *
	 * @param array  $ingredient A collection of ingredient data.
	 * @param string $icons      The `rpr-icon` string of the icon name.
	 *
	 * @return string
	 */
	private function rpr_render_ingredient_line( $ingredient, $icons = '' ) {

		$comment_sep  = (int) $this->options[ 'rpr_ingredient_separator' ];
		$link_target  = (int) $this->options[ 'rpr_ingredient_links' ];
		$auto_plural  = $this->options[ 'rpr_ingredient_pluralization' ];

		// Get the term object for the ingredient.
		if ( isset( $ingredient['ingredient_id'] ) && get_term_by( 'id', $ingredient['ingredient_id'], 'rpr_ingredient' ) ) {
			$term = get_term_by( 'id', $ingredient['ingredient_id'], 'rpr_ingredient' );
		} else {
			$term = get_term_by( 'name', $ingredient['ingredient'], 'rpr_ingredient' );
		}

		if ( ! empty( $ingredient['line'] ) ) {
			$line = $ingredient['line'];

			if ( $ingredient['link'] && $ingredient['ingredient'] ) {
				$link = '<a href="' . esc_url( $ingredient['link'] ) . '"  rel="noopener">' . esc_html( $ingredient['ingredient'] ) . '</a>';
				$line = preg_replace("/\[(.*?)\]/", $link, $line );
				$line = preg_replace("/\((.*?)\)/", '', $line );

				return '<li class="rpr-ingredient">' . wp_kses( $line, array( 'a' => array( 'href' => array(), 'rel' => array() ) ) ) . '</li>';
			}

			if ( $term && $ingredient['ingredient'] ) {
				$link = '<a href="' . esc_url( get_term_link( $term->slug, 'rpr_ingredient' ) ) . '"  rel="noopener">' . esc_html( $ingredient['ingredient'] ) . '</a>';
				$line = preg_replace("/\[(.*?)\]/", $link, $line );

				return '<li class="rpr-ingredient">' . wp_kses( $line, array( 'a' => array( 'href' => array(), 'rel' => array() ) ) ) . '</li>';
			}

			return '<li class="rpr-ingredient">' . esc_html( $line ) . '</li>';
		}

		if ( ( false === $term || is_wp_error( $term ) ) ) {
			return sprintf( '<li>' . esc_html__( 'The %1$s ingredient no longer exists. Try re-saving this recipe.', 'recipepress-reloaded' ) . '</li>', '<em>' . esc_html( $ingredient['ingredient'] ) . '</em>' );
		}

		$term_meta   = get_term_meta( $term->term_id, 'ingredient_custom_meta', true );
		$global_link = ! empty( $term_meta['link'] ) ? $term_meta['link'] : '';

		// Create an empty output string.
		$output = '';

		// Start the line.
		$output .= '<li class="rpr-ingredient">';

		$output .= apply_filters( 'rpr/template/ingredient/before', $noop = null, $ingredient );

		// Add an icon before the list item.
		$output .= ( $this->options[ 'rpr_recipe_template_use_icons' ] && $icons )
			? Icons::get_the_icon( $icons )
			: '';

		// Render amount, if it is not empty.
		$output .= '' !== $ingredient['amount']
			? '<span class="rpr-ingredient-quantity">' . esc_html( $ingredient['amount'] ) . '</span> '
			: null;

		// Render the unit, if it is not empty
		$output .= '' !== $ingredient['unit']
			? '<span class="rpr-ingredient-unit">' . esc_html( $ingredient['unit'] ) . '</span> '
			: null;

		// Render the ingredient link according to the settings.
		if ( 0 === $link_target ) {
			// Set no link.
			$closing_tag = '';
		} elseif ( 1 === $link_target && isset( $term_meta['use_in_listings'] ) && $term_meta['use_in_listings'] ) {
			// Set link to archive.
			$output     .= '<a href="' . get_term_link( $term->slug, 'rpr_ingredient' ) . '"  rel="noopener">';
			$closing_tag = '</a>';
		} elseif ( 2 === $link_target && isset( $term_meta['use_in_listings'] ) && $term_meta['use_in_listings'] ) {
			// Set custom link if available, link to archive if not.
			if ( ! empty( $ingredient['link'] ) || $global_link ) {
				$url    = $ingredient['link'] ?: $global_link;
				$target = ( ! empty( $ingredient['target'] ) || $global_link ) ? 'target="_blank"' : null;
				$output .= sprintf(
					'<a class="rpr-ingredient-link%4$s" href="%1$s" %2$s rel="noopener%3$s">',
					esc_url( $url ),
					$target,
					$this->internal_url( $url ) ? '' : ' nofollow',
					$this->internal_url( $url ) ? '' : ' external-link'
				);
				// $closing_tag = '</a>';
			} else {
				$output .= '<a href="' . get_term_link( $term->slug, 'rpr_ingredient' ) . '" rel="noopener">';
			}

			$closing_tag = '</a>';
		} else {
			// Set custom link if available, no link if not.
			if ( ! empty( $ingredient['link'] || $global_link ) ) {
				$url    = $ingredient['link'] ?: $global_link;
				$target = ( ! empty( $ingredient['target'] ) || $global_link ) ? 'target="_blank"' : null;
				$output .= sprintf(
					'<a class="rpr-ingredient-link%4$s" href="%1$s" %2$s rel="noopener%3$s">',
					esc_url( $url ),
					$target,
					$this->internal_url( $url ) ? '' : ' nofollow',
					$this->internal_url( $url ) ? '' : ' external-link'
				);
				$closing_tag = '</a>';
			} else {
				$closing_tag = '';
			}
		}

		// Render the ingredient name.
		if ( ! empty( $ingredient['amount'] ) && $ingredient['amount'] > 1 && $auto_plural ) {
			// Use plural if amount > 1.
			if ( ! empty( $term_meta['plural_name'] ) ) {
				$output .= '<span class="rpr-ingredient-name">' . esc_html( $term_meta['plural_name'] ) . '</span>';
			} else {
				$output .= '<span class="rpr-ingredient-name">' . esc_html( Pluralizer::pluralize( $term->name ) ) . '</span>';
			}
		} else {
			// Use singular.
			$output .= '<span class="rpr-ingredient-name">' . esc_html( $term->name ) . '</span>';
		}

		$output .= $closing_tag; // This adds an empty space when ingredient links are not used.

		// Render the ingredient note.
		if ( ! empty( $ingredient['notes'] ) ) {
			$output .= '<span class="rpr-ingredient-note">';
			// Add the correct separator as set in the options.
			if ( 0 === $comment_sep ) {
				// No separator.
				$output     .= ' ';
				$closing_tag = ' ';
			} elseif ( 1 === $comment_sep ) {
				// Brackets.
				$output     .= ' (';
				$closing_tag = ')';
			} else {
				// Comma.
				$output     .= ', ';
				$closing_tag = '';
			}
			$output .= esc_html( $ingredient['notes'] ) . $closing_tag . '</span>';
		}

		$output .= apply_filters( 'rpr/template/ingredient/after', $noop = null, $ingredient );

		// End the line.
		$output .= '</li>';

		// Return the rendered output.
        return apply_filters( 'rpr/frontend/template/ingredient', $output, $ingredient );
	}

	// ======================================================================
	// 0004 RECIPE INSTRUCTIONS
	// ======================================================================

	/**
	 * Generates the instructions as an ordered list.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $recipe_id An array of recipe custom metadata.
	 * @param string $icons     The `rpr-icon` string of the icon name.
	 *
	 * @return string
	 */
	public function get_the_rpr_recipe_instructions( $recipe_id, $icons = '' ) {

		$metadata = $this->get_the_recipe_meta( $recipe_id );

		if ( null === $metadata ) {
			return null;
		}

		$output       = '';

		if ( ! empty( $metadata['rpr_recipe_instructions'] ) && is_array( $metadata['rpr_recipe_instructions'] ) ) {

			$i = 0;

			foreach ( $metadata['rpr_recipe_instructions'] as $instruction ) {

				// Check if the ingredient is a grouptitle.
				if ( isset( $instruction['grouptitle'] ) ) {
					// Render the grouptitle.
					$output .= $this->rpr_render_instruction_grouptitle( $instruction, $icons );
				} else {

					if ( 0 === $i ) {
						// Start the list on the first item.
						$output .= '<ol class="rpr-instruction-list" >';
					}
					// Render the instruction block.
					$output .= $this->rpr_render_instruction_block( $recipe_id, $instruction );
				}

				$i ++;
			}

			// Close the list on the last item.
			$output .= '</ol>';

		} else {
			// Issue a warning, if there are no instructions for the recipe.
			$output .= '<p class="warning">' . __( 'No instructions could be found for this recipe.', 'recipepress-reloaded' ) . '</p>';
		}

		// Return the rendered instructions list.
        return apply_filters( 'rpr/frontend/template/instructions', $output, $recipe_id );
	}

	/**
	 * Prints the instructions as an ordered list.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $recipe_id An array of recipe custom metadata.
	 * @param string $icons    The `rpr-icon` string of the icon name.
	 *
	 * @return void
	 */
	public function the_rpr_recipe_instructions( $recipe_id, $icons = '' ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_the_rpr_recipe_instructions( $recipe_id, $icons );
	}

	/**
	 * Render the grouptitle for a instruction group
	 *
	 * @since 1.0.0
	 *
	 * @param array  $instruction A collection of instruction data.
	 * @param string $icons       The `rpr-icon` string of the icon name.
	 *
	 * @return string
	 */
	public function rpr_render_instruction_grouptitle( $instruction, $icons = '' ) {

		$output = '';

		if ( 0 === $instruction['sort'] ) {
			// Do not close the instruction list of the previous group if this is
			// the first group.
			$output .= '';
		} else {
			// Close the instruction list of the previous group.
			$output .= '</ol>';
		}

		// Create the headline for the instruction group.
		if ( $this->is_recipe_embedded() ) {
			// Fourth level headline for embedded recipe.
			$output .= '<h4 class="rpr-instruction-group-title">';
			// Add an icon before the item.
			$output .= ( $this->options[ 'rpr_recipe_template_use_icons' ] && $icons ) ? Icons::get_the_icon( $icons ) : '';
			$output .= esc_html( $instruction['grouptitle'] );
			$output .= '</h4>';
		} else {
			// Third level headline for standalone recipes.
			$output .= '<h3 class="rpr-instruction-group-title">';
			// Add an icon before the item.
			$output .= ( $this->options[ 'rpr_recipe_template_use_icons' ] && $icons ) ? Icons::get_the_icon( $icons ) : '';
			$output .= esc_html( preg_replace("/#+\s/", '', sanitize_text_field( $instruction['grouptitle'] ) ) );
			$output .= '</h3>';
		}

		// Start the list for this ingredient group.
		$output .= '<ol class="rpr-instruction-list">';

		// Return the rendered output.
		return $output;
	}

	/**
	 * Renders a single recipe instruction item
	 *
	 * @since 1.0.0
	 *
	 * @param int   $recipe_id   The post ID of the recipe.
	 * @param array $instruction A collection of recipe instruction data.
	 *
	 * @return string
	 */
	public function rpr_render_instruction_block( $recipe_id, $instruction ) {

		$output = '';
		$image  = '';
		$key    = ! empty( $instruction['key'] ) ? esc_attr( $instruction['key'] ) : substr( md5( $instruction['description'] ), 0, 9 );

		// Determine the class for the instruction text depending on image options.
		if ( ! empty( $instruction['image'] ) ) {
			$instr_class  = ' has_thumbnail';
			$instr_class .= ' ' . $this->options[ 'rpr_recipe_template_inst_image' ] ?: 'right';
		} else {
			$instr_class = '';
		}

		// Start the line.
		$output .= '<li id="r' . $key . '" class="rpr-instruction">';

		$output .= apply_filters( 'rpr/template/instruction/before', $noop = null, $instruction );

		$output .= '<span class="rpr-instruction-wrapper' . esc_attr( $instr_class ) . '">';

		// Render the instruction text.
		$input  = ! empty( $instruction['description'] ) ? $instruction['description'] : $instruction['line'];
		$output .= '<span class="rpr-recipe-instruction-text">' . esc_html( $this->parse_instruction( $input, 'instruction' ) ) . '</span>';

		// Render the instruction step image.
		if ( ! empty( $instruction['image'] ) && 'hide' !== $this->options[ 'rpr_recipe_template_inst_image' ] ) {
			// Get the image data.
			if ( 'right' === $this->options[ 'rpr_recipe_template_inst_image' ] ) {
				$image = wp_get_attachment_image( $instruction['image'], 'medium', '', array( 'class' => 'rpr-instruction-image right' ) );
			} else {
				$image = wp_get_attachment_image( $instruction['image'], 'large', '', array( 'class' => 'rpr-instruction-image' ) );
			}

			// Get link target for clickable images.
			if ( $this->options[ 'rpr_recipe_template_click_img' ] && '' !== $image ) {
			    // If we've enabled clickable image and there is an image to click on, enqueue the lightbox
                wp_enqueue_style( 'rpr-lightbox' );
                wp_enqueue_script( 'rpr-lightbox' );

				$img_full = wp_get_attachment_image_src( $instruction['image'], 'full' );
				$output .= '<span class="rpr-recipe-instruction-image">';
				$output .= '<a class="rpr_img_link" href="' . esc_url( $img_full[0] ) .
							'" rel="lightbox" data-lightbox="' . $recipe_id . '">';
			}

			// Render the image.
			$output .= '<span class="rpr-recipe-instruction-image">';
			$output .= $image;
			$output .= '</span>';

			// Close the link for clickable images.
			if ( $this->options[ 'rpr_recipe_template_click_img' ] && '' !== $image ) {
				$output .= '</a>';
				$output .= '</span>';
			}
		}

		// End the line.
		$output .= '</span>';

		$output .= apply_filters( 'rpr/template/instruction/after', $noop = null, $instruction );

		$output .= '</li>';

		// Return the rendered output.
        return apply_filters( 'rpr/frontend/template/instruction', $output, $recipe_id );
	}

	// ======================================================================
	// 0005 RECIPE NOTES
	// ======================================================================

	/**
	 * Generates the recipe notes section of the recipe.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $recipe_id The post ID of a recipe.
	 * @param string $wrapper   Output wrapper div
	 *
	 * @return string|null
	 */
	public function get_the_rpr_recipe_notes( $recipe_id, $wrapper = true ) {

		$metadata = $this->get_the_recipe_meta( $recipe_id );

		// Render the notes only if it is not empty.
		if ( null === $metadata || empty( $metadata['rpr_recipe_notes'] ) ) {
			return null;
		}

		$output = '';

		$output .= $wrapper ? '<div class="rpr_notes" >' : '';
		$output .= apply_filters( 'rpr_content', $metadata['rpr_recipe_notes'] );
		$output .= $wrapper ? '</div>' : '';

        return apply_filters( 'rpr/frontend/template/notes', $output, $recipe_id );
	}

	/**
	 * Generates the recipe notes section of the recipe.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $recipe_id The post ID of a recipe.
	 * @param string $wrapper   Output wrapper div
	 *
	 * @return void
	 */
	public function the_rpr_recipe_notes( $recipe_id, $wrapper = true ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_the_rpr_recipe_notes( $recipe_id, $wrapper );
	}

	// ======================================================================
	// 0006 RECIPE SOURCE INFORMATION
	// ======================================================================

	/**
	 * Generates the recipe source/credit information
	 *
	 * @since 1.0.0
	 *
	 * @param int    $recipe_id An array of recipe custom metadata.
	 * @param string $icons     The `rpr-icon` string of the icon name.
	 *
	 * @return string
	 */
	public function get_the_rpr_recipe_source( $recipe_id, $icons = '' ) {

		$metadata = $this->get_the_recipe_meta( $recipe_id );

		if ( null === $metadata ) {
			return null;
		}

		$output = '';
		$source = isset( $metadata['rpr_recipe_source'] ) ? $metadata['rpr_recipe_source'] : array();

		if ( ! isset( $source['name'] ) || '' === $source['name'] ) {
			return null; // Return early if no recipe source data is stored.
		}

		if ( $this->options[ 'rpr_use_source_meta' ] ) {
			$output .= '<cite class="rpr-source">';
			$output .= ( $this->options[ 'rpr_recipe_template_use_icons' ] && $icons ) ? Icons::get_the_icon( $icons ) : '';
			$output .= '<label for="rpr-source">' . __( 'Source', 'recipepress-reloaded' ) . ': </label>';
			$output .= ( '' !== $source['link'] ) ? '<a href="' . esc_url( $source['link'] ) . '" target="_blank" rel="noopener">' : '';
			$output .= esc_html( $source['name'] );
			$output .= ( '' !== $source['link'] ) ? '</a>' : '';
			$output .= '</cite>';
		}

		return $output;
	}

	/**
	 * Prints the recipe source/credit information
	 *
	 * @since 1.0.0
	 *
	 * @param int    $recipe_id An array of recipe custom metadata.
	 * @param string $icons     The `rpr-icon` string of the icon name.
	 *
	 * @return void
	 */
	public function the_rpr_recipe_source( $recipe_id, $icons = '' ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_the_rpr_recipe_source( $recipe_id, $icons );
	}

	// ======================================================================
	// 0007 RECIPE NUTRITIONAL INFORMATION
	// ======================================================================

	/**
	 * Generates the recipe nutritional information
	 *
	 * @since 1.0.0
	 *
	 * @param int    $recipe_id An array of recipe custom metadata.
	 * @param string $icons     The `rpr-icon` string of the icon name.
	 * @param bool   $per       Display the nutrition per serving information.
	 *
	 * @return string
	 */
	public function get_the_rpr_recipe_nutrition( $recipe_id, $icons = '', $per = true ) {

		$metadata = $this->get_the_recipe_meta( $recipe_id );

		if ( null === $metadata ) {
			return null;
		}

		$output = '';
		$data   = array();
		$fields = array(
			'rpr_recipe_calorific_value',
			'rpr_recipe_protein',
			'rpr_recipe_fat',
			'rpr_recipe_carbohydrate',
			'rpr_recipe_nutrition_per',
		);
		$additional = $this->get_nutrition_fields( 'additional' );

		foreach ( $fields as $field ) {
			$data[ $field ] = isset( $metadata[ $field ] ) ? $metadata[ $field ] : null;
		}

		if ( ! $this->options[ 'rpr_use_nutritional_meta' ] || array_sum( array_values( $data ) ) <= 0 ) {
			return null;
		}

		$output .= '<ul class="rpr-nutrition" >';
		$output .= $per ? $this->get_the_rpr_recipe_nutrition_per( $data, $icons ) : null;

		// We are specifically checking for an empty string as it is an indicator the user did not
		// enter any value in the field. A `0` would indicate the value zero was entered by the user.

		if ( '' !== $data['rpr_recipe_calorific_value'] ) {
			$output .= sprintf(
				'<li class="nutrition-energy"><span class="energy-label">' . __( 'Energy', 'recipepress-reloaded' ) . ': </span><span class="energy-value">%1s kcal / %2s kJ</span></li>',
				esc_html( $data['rpr_recipe_calorific_value'] ),
				esc_html( round( 4.18 * $data['rpr_recipe_calorific_value'] ) )
			);
		}

		if ( $this->not_empty_string_or_zero_or_null( $data['rpr_recipe_fat'] ) ) {
			$output .= sprintf(
				'<li class="nutrition-fat"><span class="fat-label">' . __( 'Fat', 'recipepress-reloaded' ) . ': </span><span class="fat-value">%s g</span></li>',
				esc_html( $data['rpr_recipe_fat'] )
			);
		}

		if ( $this->not_empty_string_or_zero_or_null( $data['rpr_recipe_protein'] ) ) {
			$output .= sprintf(
				'<li class="nutrition-protein"><span class="protein-label">' . __( 'Protein', 'recipepress-reloaded' ) . ': </span><span class="protein-value">%s g</span></li>',
				esc_html( $data['rpr_recipe_protein'] )
			);
		}

		if ( $this->not_empty_string_or_zero_or_null( $data['rpr_recipe_carbohydrate'] ) ) {
			$output .= sprintf(
				'<li class="nutrition-carbs"><span class="carbs-label">' . __( 'Carbs', 'recipepress-reloaded' ) . ': </span><span class="carbs-value">%s g</span></li>',
				esc_html( $data['rpr_recipe_carbohydrate'] )
			);
		}

		foreach( $additional as $k => $v ) {
			$key   = array_keys( $v )[0];
			$value = array_values( $v )[0];

			if ( isset( $metadata[ $value ] ) && '' !== $metadata[ $value ] ) {
				$output .= sprintf(
					'<li class="%2$s"><span class="label">' . esc_html( self::$additional_nutrition_keys[ $key ] ) . ': </span><span class="value">%1$s g</span></li>',
					esc_html( $metadata[ $value ] ),
					str_replace( '_', '-', $value )
				);
			}
		}

		$output .= '</ul>';

		return $output;
	}

	/**
	 * Displays the nutrition serving information
	 *
	 * @param array $data   An array of the recipe nutrition data.
	 * @param string $icons The `rpr-icon` string of the icon name.
	 * @param string $tag   The HTML tag we are outputting, defaults to the `<li>` tag.
	 *
	 * @return string
	 */
	public function get_the_rpr_recipe_nutrition_per( $data, $icons, $tag = 'li' ) {

		$output  = '';
		$output .= '<' . $tag . ' class="nutrition-per">';
		$output .= ( $this->options[ 'rpr_recipe_template_use_icons' ] && $icons )
			? Icons::get_the_icon( $icons )
			: '';

		switch ( $data['rpr_recipe_nutrition_per'] ) {
			case 'per_100g':
				$output .= __( 'Per 100g', 'recipepress-reloaded' );
				break;
			case 'per_portion':
				$output .= __( 'Per portion', 'recipepress-reloaded' );
				break;
			case 'per_recipe':
				$output .= __( 'Per recipe', 'recipepress-reloaded' );
				break;
			default:
				$output .= __( 'Per serving', 'recipepress-reloaded' );
		}

		$output .= '</' . $tag . '>';

		return $output;
	}

	/**
	 * Prints the recipe nutritional information
	 *
	 * @since 1.0.0
	 *
	 * @param int    $recipe_id An array of recipe custom metadata.
	 * @param string $icons     The `rpr-icon` string of the icon name.
	 * @param bool   $per       Display the nutrition per serving information.
	 *
	 * @return void
	 */
	public function the_rpr_recipe_nutrition( $recipe_id, $icons = '', $per = true ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_the_rpr_recipe_nutrition( $recipe_id, $icons, $per );
	}

	// ======================================================================
	// 0008 RECIPE SERVING INFORMATION
	// ======================================================================

	/**
	 * Prints the recipe serving information
	 *
	 * @since 1.0.0
	 *
	 * @param int  $recipe_id An array of metadata attached to a recipe post.
	 * @param string $icons    The `rpr-icon` string of the icon name.
	 *
	 * @return string
	 */
	public function get_the_rpr_recipe_servings( $recipe_id, $icons = '' ) {

		$metadata = $this->get_the_recipe_meta( $recipe_id );

		if ( null === $metadata ) {
			return null;
		}

		$output   = '';
		$fields   = array(
			'rpr_recipe_servings',
			'rpr_recipe_servings_type',
		);
		$servings = array();

		foreach ( $fields as $field ) {
			$servings[ $field ] = isset( $metadata[ $field ] ) ? $metadata[ $field ] : null;
		}

		if ( null === $servings['rpr_recipe_servings'] || '' === $servings['rpr_recipe_servings'] ) {
			return null;
		}

		$output .= '<ul class="rpr-servings">';
		$output .= '<li>';
		$output .= '<span class="rpr_servings_label">';
		$output .= ( $this->options[ 'rpr_recipe_template_use_icons' ] && $icons ) ? Icons::get_the_icon( $icons ) : '';
		$output .= apply_filters( 'rpr_servings_label', __( 'For', 'recipepress-reloaded' ) );
		$output .= ': </span>';
		$output .= '<span class="rpr_servings">' . esc_html( $servings['rpr_recipe_servings'] ) . ' ';
		$output .= esc_html( $servings['rpr_recipe_servings_type'] ) . '</span>';
		$output .= '</li>';
		$output .= '</ul>';

		return $output;
	}

	/**
	 * Prints the recipe serving information
	 *
	 * @since 1.0.0
	 *
	 * @param int    $recipe_id An array of metadata attached to a recipe post.
	 * @param string $icons     The `rpr-icon` string of the icon name.
	 *
	 * @return void
	 */
	public function the_rpr_recipe_servings( $recipe_id, $icons = '' ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_the_rpr_recipe_servings( $recipe_id, $icons );
	}

	// ======================================================================
	// 0009 RECIPE COOKING TIME INFORMATION
	// ======================================================================

	/**
	 * Generates the recipe prep time information
	 *
	 * @since 1.0.0
	 *
	 * @param int    $recipe_id An array of metadata attached to a recipe post.
	 * @param string $icons     The `rpr-icon` string of the icon name.
	 *
	 * @return string
	 */
	public function get_the_rpr_recipe_times( $recipe_id, $icons = '' ) {

		$metadata = $this->get_the_recipe_meta( $recipe_id );

		if ( null === $metadata ) {
			return null;
		}

		$output = '';
		$fields = array(
			'rpr_recipe_prep_time',
			'rpr_recipe_cook_time',
			'rpr_recipe_passive_time',
		);
		$times  = array();

		foreach ( $fields as $field ) {
			$times[ $field ] = isset( $metadata[ $field ] ) ? $metadata[ $field ] : null;
		}

		if ( array_sum( array_values( $times ) ) <= 0 ) {
			return null;
		}

		$output .= '<ul class="rpr-times">';

		if ( $times['rpr_recipe_prep_time'] > 0 ) {
			$output .= '<li class="prep-time">';
			$output .= '<span class="prep-time-label">';
			$output .= ( $this->options[ 'rpr_recipe_template_use_icons' ] && $icons ) ? Icons::get_the_icon( $icons[0] ) : '';
			$output .= apply_filters( 'rpr_prep_time_label', __( 'Preparation', 'recipepress-reloaded' ) );
			$output .= ': </span>';
			$output .= '<span class="prep-time-value">';
			$output .= $this->rpr_format_time_hum( esc_attr( $times['rpr_recipe_prep_time'] ) );
			$output .= '</span>';
			$output .= '</li>';
		}

		if ( $times['rpr_recipe_cook_time'] > 0 ) {
			$output .= '<li class="cook-time">';
			$output .= '<span class="cook-time-label">';
			$output .= ( $this->options[ 'rpr_recipe_template_use_icons' ] && $icons ) ? Icons::get_the_icon( $icons[1] ) : '';
			$output .= apply_filters( 'rpr_cook_time_label', __( 'Cooking', 'recipepress-reloaded' ) );
			$output .= ': </span>';
			$output .= '<span class="cook-time-value">';
			$output .= $this->rpr_format_time_hum( esc_attr( $times['rpr_recipe_cook_time'] ) );
			$output .= '</span>';
			$output .= '</li>';
		}

		if ( array_sum( array_values( $times ) ) > 0  ) {
			$output .= '<li class="ready-time">';
			$output .= '<span class="ready-time-label">';
			$output .= ( $this->options[ 'rpr_recipe_template_use_icons' ] && $icons ) ? Icons::get_the_icon( $icons[2] ) : '';
			$output .= apply_filters( 'rpr_ready_time_label', __( 'Ready in', 'recipepress-reloaded' ) );
			$output .= ': </span>';
			$output .= '<span class="ready-time-value">';
			$output .= $this->rpr_format_time_hum( esc_attr( array_sum( array_values( $times ) ) ) );
			$output .= '</span>';
			$output .= '</li>';
		}

		$output .= '</ul>';

		return $output;
	}

	/**
	 * Prints the recipe prep time information
	 *
	 * @since 1.0.0
	 *
	 * @param int    $recipe_id An array of metadata attached to a recipe post.
	 * @param string $icons     The `rpr-icon` string of the icon name.
	 *
	 * @return void
	 */
	public function the_rpr_recipe_times( $recipe_id, $icons = '' ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_the_rpr_recipe_times( $recipe_id, $icons );
	}

	// ======================================================================
	// 0010 RECIPE JUMP BUTTON
	// ======================================================================

	/**
	 * Create a jump to recipe button
	 *
	 * @since 1.0.0
	 *
	 * @param string $icon     The `rpr-icon` string of the icon name.
	 * @param string $location The anchor point to jump to
	 *
	 * @return string
	 */
	public function get_the_rpr_recipe_jump_button( $icon, $location = false ) {

		$enable_btn  = $this->options[ 'rpr_recipe_template_recipe_jump' ];
		$button_text = $enable_btn ? $this->options[ 'rpr_recipe_template_jump_btn_text' ] : __( 'Jump to Recipe', 'recipepress-reloaded' );

		$out = '';

		if ( $enable_btn && is_singular( 'rpr_recipe' ) ) {
			$out .= '<a class="rpr-jump-to-recipe no-print" href="' . sprintf( '#%s', $location ?: 'rpr-recipe' ) . '">';
			$out .= ( $this->options[ 'rpr_recipe_template_use_icons' ] && $icon )
				? Icons::get_the_icon( $icon )
				: '';
			$out .= apply_filters( 'rpr_jump_button_text', esc_html( $button_text ) );
			$out .= '</a>';
		}

		return $out;
	}

	/**
	 * The jump to recipe button
	 *
	 * @since 1.0.0
	 *
	 * @param string $icon The `rpr-icon` string of the icon name.
	 * @param string $location The anchor point to jump to
	 *
	 * @return void
	 */
	public function the_rpr_recipe_jump_button( $icon, $location = false ) {

		echo $this->get_the_rpr_recipe_jump_button( $icon, $location );
	}

    /**
     * Renders a unordered list of recipe equipment
     * for the frontend, if enabled.
     *
     * @since 2.0.0
     *
     * @param int $recipe_id
     *
     * @return string
     */
    public function get_rpr_equipment_list( $recipe_id ) {

        $out       = '';
        $equipment = get_post_meta( $recipe_id, 'rpr_recipe_equipment', true );
        $enabled   = ! empty( $this->options['rpr_recipe_equipment'] );

        if ( $equipment && $enabled ) {
            $out .= '<ul class="rpr-equipment__list">';
            foreach( $equipment as $equip ) {
                $out .= '<li class="rpr-equipment__item">';
                $out .= ! empty( $equip['link'] ) ? '<a href="' . esc_url( $equip['link'] ) . '" rel="noopener">' : '';
                $out .= '<span class="rpr-equipment__list__name">' . $equip['name'] . '</span>';
                $out .= ! empty( $equip['notes'] ) ? ', <span class="rpr-equipment__list__notes">' . $equip['notes'] . '</span>' : '';
                $out .= ! empty( $equip['link'] ) ? '</a>' : '';
                $out .= '</li>';
            }
            $out .= '</ul>';
        }

        return $out;
    }

    /**
     * Echoes an unordered list of recipe equipment
     * for the frontend.
     *
     * @since 2.0.0
     *
     * @param int $recipe_id
     *
     * @return void
     */
    public function the_rpr_equipment_list( $recipe_id ) {
        echo $this->get_rpr_equipment_list( $recipe_id );
    }

	// ======================================================================
	// 0011 RECIPE JSON SCHEMA
	// ======================================================================

	/**
	 * Generates the data needed for recipe JSON-LD schema.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $recipe_id The recipe post ID.
	 *
	 * @return array
	 */
	public function get_the_rpr_recipe_schema( $recipe_id ) {

		$metadata = $this->get_the_recipe_meta( $recipe_id );

		if ( null === $metadata ) {
			return null;
		}

		$instructions = ! empty( $metadata['rpr_recipe_instructions'] ) ? $metadata['rpr_recipe_instructions'] : array();
		$ingredients  = ! empty( $metadata['rpr_recipe_ingredients'] ) ? $metadata['rpr_recipe_ingredients'] : array();
		$comments     = get_comments( array( 'post_id' => $recipe_id ) );
		$course       = $this->get_the_rpr_taxonomy_term( $recipe_id, 'rpr_course', 3 );
		$cuisine      = $this->get_the_rpr_taxonomy_term( $recipe_id, 'rpr_cuisine', 3 );
		$keywords     = $this->get_the_rpr_taxonomy_term( $recipe_id, 'rpr_keywords', 2 );

		$recipe_rating_avg   = $this->get_the_recipe_rating( $recipe_id );
		$recipe_rating_count = $this->get_the_recipe_rating( $recipe_id, 'count' );

		$recipe_author   = get_post_field( 'post_author', $recipe_id );
		$description     = get_post_field( 'post_excerpt', $recipe_id ) ?: wp_strip_all_tags( get_the_excerpt( $recipe_id ) );
		$prep_time       = ! empty( $metadata['rpr_recipe_prep_time'] ) ? (int) $metadata['rpr_recipe_prep_time'] : 0;
		$cook_time       = ! empty( $metadata['rpr_recipe_cook_time'] ) ? (int) $metadata['rpr_recipe_cook_time'] : 0;
		$passive_time    = ! empty( $metadata['rpr_recipe_passive_time'] ) ? (int) $metadata['rpr_recipe_passive_time'] : 0;
		$servings        = ! empty( $metadata['rpr_recipe_servings'] ) ? $metadata['rpr_recipe_servings'] : '';
		$servings_type   = ! empty( $metadata['rpr_recipe_servings_type'] ) ? $metadata['rpr_recipe_servings_type'] : '';
		$calories        = ! empty( $metadata['rpr_recipe_calorific_value'] ) ? $metadata['rpr_recipe_calorific_value'] : null;
		$carbohydrate    = ! empty( $metadata['rpr_recipe_carbohydrate'] ) ? $metadata['rpr_recipe_carbohydrate'] : null;
		$cholesterol     = ! empty( $metadata['rpr_recipe_cholesterol'] ) ? $metadata['rpr_recipe_cholesterol'] : null;
		$fat             = ! empty( $metadata['rpr_recipe_fat'] ) ? $metadata['rpr_recipe_fat'] : null;
		$protein         = ! empty( $metadata['rpr_recipe_protein'] ) ? $metadata['rpr_recipe_protein'] : null;
		$saturated_fat   = ! empty( $metadata['rpr_recipe_saturated_fat'] ) ? $metadata['rpr_recipe_saturated_fat'] : null;
		$unsaturated_fat = ! empty( $metadata['rpr_recipe_unsaturated_fat'] ) ? $metadata['rpr_recipe_unsaturated_fat'] : null;
		$sodium          = ! empty( $metadata['rpr_recipe_sodium'] ) ? $metadata['rpr_recipe_sodium'] : null;
		$fiber           = ! empty( $metadata['rpr_recipe_fiber'] ) ? $metadata['rpr_recipe_fiber'] : null;
		$sugar           = ! empty( $metadata['rpr_recipe_sugar'] ) ? $metadata['rpr_recipe_sugar'] : null;
		$video           = ! empty( $metadata['rpr_recipe_video_data'] ) ? $metadata['rpr_recipe_video_data'] : null;

		$data             = array();
		$data['@context'] = 'http://schema.org';
		$data['@type']    = 'Recipe';
		$data['name']     = get_the_title( $recipe_id );

		// Images.
		$data['image'] = array_filter(
			array(
				get_the_post_thumbnail_url( $recipe_id, 'thumbnail' ),
				get_the_post_thumbnail_url( $recipe_id, 'medium' ),
				get_the_post_thumbnail_url( $recipe_id, 'full' ),
			)
		);

		// Author.
		$data['author'] = array(
			'@type'  => 'Person',
			'name'   => get_the_author_meta( 'display_name', $recipe_author ),
			'url'    => get_the_author_meta( 'user_url', $recipe_author ),
			// There is a weird bug here, where `array_values()` is needed on live site.
			'sameAs' => array_values(
				array_filter(
					array(
						get_the_author_meta( 'rpr_twitter', $recipe_author ),
						get_the_author_meta( 'rpr_facebook', $recipe_author ),
						get_the_author_meta( 'rpr_yummly', $recipe_author ),
						get_the_author_meta( 'rpr_linkedin', $recipe_author ),
						get_the_author_meta( 'rpr_pinterest', $recipe_author ),
						get_the_author_meta( 'rpr_youtube', $recipe_author ),
						get_the_author_meta( 'rpr_instagram', $recipe_author ),
					)
				)
			),
		);

		$data['datePublished']  = get_the_date( 'c', $recipe_id );
		$data['dateModified']   = get_the_modified_date( 'c', $recipe_id );
		$data['description']    = $description;
		$data['prepTime']       = $this->rpr_format_time_xml( $prep_time );
		$data['cookTime']       = $this->rpr_format_time_xml( $cook_time );
		$data['totalTime']      = $this->rpr_format_time_xml( $prep_time + $cook_time + $passive_time );
		$data['keywords']       = $keywords ?: '';
		$data['recipeYield']    = $servings ? array( (string) $servings, esc_attr( $servings . ' ' . $servings_type ) ) : '';
		$data['recipeCategory'] = $course;
		$data['recipeCuisine']  = $cuisine;

		// Nutrition.
		$data['nutrition'] = array_filter(
			array(
				'@type'                 => 'NutritionInformation',
				'calories'              => $calories,
				'carbohydrateContent'   => $carbohydrate,
				'cholesterolContent'    => $cholesterol,
				'fatContent'            => $fat,
				'fibreContent'          => $fiber,
				'proteinContent'        => $protein,
				'saturatedFatContent'   => $saturated_fat,
				'sodiumContent'         => $sodium,
				'sugarContent'          => $sugar,
				// 'transFatContent'       => '',
				'unsaturatedFatContent' => $unsaturated_fat,
			),
			function( $v, $k ) {
				return strlen( (string) $v ) &&  $v !== null;
			},
			ARRAY_FILTER_USE_BOTH
		);

		// Ingredients.
		foreach ( $ingredients as $ingredient ) {
			if ( ! isset( $ingredient['grouptitle'] ) ) { // If 'grouptitle' is set we're skipping it.
				unset( $ingredient['key'], $ingredient['sort'], $ingredient['link'], $ingredient['ingredient_id'], $ingredient['target'] ); // Remove unnecessary items.

				if ( ! empty( $ingredient['line'] ) && empty( $ingredient['ingredient'] ) ) {
					$data['recipeIngredient'][] = $ingredient['line'];
                } elseif ( ! empty( $ingredient['line'] ) && ! empty( $ingredient['ingredient'] ) ) {
					// $data['recipeIngredient'][] = preg_replace("/\((.*?)\)/", '', $ingredient['line'] );
					$data['recipeIngredient'][] = preg_replace("/\[(.*?)\]/", "$1", preg_replace("/\((.*?)\)/", '', $ingredient['line'] ) );
				} elseif ( empty( $ingredient['line'] ) )
					$data['recipeIngredient'][] = implode( ' ', array_values( array_filter( $ingredient, 'strlen' ) ) );
				}
		}

		// Instructions.
		if ( ! empty( $instructions[0]['grouptitle'] ) ) {

			$sorted_instructions = null;
			$group_title         = '';

			foreach ( $instructions as $instruction ) {
				if ( isset( $instruction['grouptitle'] ) ) {
					$group_title                         = $instruction['grouptitle'];
					$sorted_instructions[ $group_title ] = array();
				} else {
					$sorted_instructions[ $group_title ][] = $instruction;
				}
			}

			if ( $sorted_instructions ) {
				foreach ( $sorted_instructions as $section => $instruction ) {
					$all_steps = array();
					foreach ( $instruction as $steps ) {
						$input  = ! empty( $steps['description'] ) ? $steps['description'] : $steps['line'];
						$all_steps[] = array(
							'@type' => 'HowToStep',
							'name'  => $this->parse_instruction( $input, 'name' ),
							'text'  => $this->parse_instruction( $input, 'instruction' ),
							'url'   => get_permalink( $recipe_id ) . '#r' . substr( md5( $input ), 0, 6 ),
							'image' => wp_get_attachment_url( $steps['image'] ),
						);
					}
					$data['recipeInstructions'][] = array(
						'@type'           => 'HowToSection',
						'name'            => preg_replace("/#+\s/", '', $section ),
						'itemListElement' => $all_steps,
					);
				}
			}
		} else {
			if ( is_array( $instructions ) ) {
				foreach ( $instructions as $instruction ) {
					$key = ! empty( $instruction['key'] ) ? esc_attr( $instruction['key'] ) : substr( md5( $instruction['description'] ), 0, 9 );
					if ( ! isset( $instruction['grouptitle'] ) ) { // If 'grouptitle' is set we're skipping it.
						$input  = ! empty( $instruction['description'] ) ? $instruction['description'] : $instruction['line'];
						$data['recipeInstructions'][] = array(
							'@type' => 'HowToStep',
							'name'  => $this->parse_instruction( $input, 'name' ),
							'text'  => $this->parse_instruction( $input, 'instruction' ),
							'url'   => get_permalink( $recipe_id ) . '#r' . $key,
							'image' => ! empty( $instruction['image'] ) ? wp_get_attachment_url( $instruction['image'] ) : '',
						);
					}
				}
			}
		}

		// Review.
		if ( count( $comments ) > 1 ) {
			foreach ( $comments as $comment ) {
				if ( (int) $comment->comment_karma > 0 ) {
					$data['review'][] = array(
						'@type'         => 'Review',
						'reviewRating'  => array(
							'@type'       => 'Rating',
							'ratingValue' => (int) $comment->comment_karma,
							'bestRating'  => 5,
						),
						'author'        => array(
							'@type' => 'Person',
							'name'  => $comment->comment_author,
						),
						'datePublished' => $comment->comment_date_gmt,
						'reviewBody'    => $comment->comment_content,
						// 'publisher'     => '',
					);
				}
			}
		}

		// Aggregate rating.
		if ( $recipe_rating_count >= 1 ) {
			$data['aggregateRating'] = array(
				'@type'       => 'AggregateRating',
				'ratingValue' => (float) number_format( $recipe_rating_avg, 1, '.', '' ),
				'ratingCount' => $recipe_rating_count,
			);
		}

		$data['interactionStatistic'] = array(
			'@type'                => 'InteractionCounter',
			'interactionType'      => 'http://schema.org/Comment',
			'userInteractionCount' => (int) get_comments_number( $recipe_id ),
		);

		// Recipe video.
		if ( $video && '' !== $video['video_url'] ) {
			$data['video'][] = array(
				'@type'            => 'VideoObject',
				'name'             => isset( $video['video_title'] ) ? $video['video_title'] : '',
				'description'      => isset( $video['video_description'] ) ? $video['video_description'] : '',
				'thumbnailUrl'     => isset( $video['video_thumb'] ) ? $video['video_thumb'] : array(),
				'contentUrl'       => isset( $video['video_url'] ) ? $video['video_url'] : '',
				'uploadDate'       => isset( $video['video_date'] ) ? $video['video_date'] : '',
				// 'duration'         => 'PT1M33S',
				// 'interactionCount' => '4335',
			);
		}

		// Diet. This could also be handled by a "Diet" taxonomy for a per recipe basis.
		$suitable_diet = explode( ',', $this->options[ 'rpr_diet_selection' ] );
		if ( ! empty( $suitable_diet[0] ) ) {
			foreach ( $suitable_diet as $diet ) {
				$data['suitableForDiet'][] = 'http://schema.org/' . $diet;
			}
		}

		/**
		 * Here we cleanup our data to remove any invalid schema field values.
		 */
		if ( ! empty( $data['recipeInstructions'] ) ) {
			$data['recipeInstructions'] = $this->remove_element_by_value( $data['recipeInstructions'], false );
		}

		$data = apply_filters( 'rpr/recipe/json_ld/schema', $data, $recipe_id );

		return $data;
	}

	/**
	 * Prints the recipe JSON-LD schema.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $recipe_id The recipe post ID.
	 *
	 * @return void
	 */
	public function the_rpr_recipe_schema( $recipe_id ) {
		echo '<script class="rpr-recipe-schema" type="application/ld+json">' . wp_json_encode( $this->get_the_rpr_recipe_schema( $recipe_id ) ) . '</script>';
	}

	// ======================================================================
	// 0012 RECIPE UTILITIES
	// ======================================================================

	/**
	 * Check if the recipe is embedded in a post, page or another custom post type.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	public function is_recipe_embedded() {
		return 'rpr_recipe' !== get_post_type();
	}

	/**
	 * Converts a time value to an XML time string?
	 *
	 * @since 1.0.0
	 *
	 * @param int $time The time usually in minutes.
	 *
	 * @return string
	 */
	public function rpr_format_time_xml( $time ) {

		$hours   = floor( (int) $time / 60 );
		$minutes = (int) $time % 60;

		if ( $hours > 0 && $minutes > 0 ) {
			return sprintf( 'PT%1$dH%2$dM', $hours, $minutes );
		} elseif ( $hours > 0 && 0 === $minutes ) {
			return sprintf( 'PT%dH', $hours );
		} else {
			return sprintf( 'PT%dM', $minutes );
		}
	}

	/**
	 * Formats a number of minutes to a human readable time string
	 *
	 * @param int $min
	 *
	 * @return string
	 */
	function rpr_format_time_hum( $min ) {

		$hours   = floor( (int) $min / 60 );
		$minutes = (int) $min % 60;

		if ( $hours > 0 && $minutes > 0 ) {
			return sprintf( '%1$d h %2$d min', $hours, $minutes );
		} elseif ( $hours > 0 && $minutes === 0 ) {
			return sprintf( '%d h', $hours );
		} else {
			return sprintf( '%d min', $minutes );
		}
	}

	/**
	 * @param string $class
	 */
	public function rpr_html_classes( $class = '' ) {
		$classes = array( 'rpr' );
		$classes[] = $class;
		// Separates classes with a single space, collates classes for body element
		echo 'class="' . implode( ' ', array_map( 'esc_attr', $classes ) ) . '"';
	}

	/**
	 * @param string $separator
	 * @param null   $taxonomy
	 */
	public function breadcrumbs( $separator = '>', $taxonomy = null ) {

		// Settings
		$breadcrumbs_id    = 'rpr-breadcrumbs';
		$breadcrumbs_class = 'rpr-breadcrumbs';
		$home_title        = 'Home';

		// If you have any custom post types with custom taxonomies, put the taxonomy name below (e.g. product_cat)
		$taxonomy = in_array( $taxonomy, array( 'category', 'post_tag' ), true ) ? $taxonomy : 'rpr_' . $taxonomy;

		// Get the query & post information
		global $post, $wp_query;

		// Do not display on the homepage
		if ( ! is_front_page() ) {

			// Build the breadcrums
			echo '<ol id="' . $breadcrumbs_id . '" class="' . $breadcrumbs_class . '">';

			// Home page
			echo '<li class="item-home"><a class="bread-link bread-home" href="' . get_home_url() . '" title="' . $home_title . '">' . $home_title . '</a></li>';
			echo '<li class="separator separator-home"> ' . $separator . ' </li>';

			if ( is_archive() && ! is_tax() && ! is_category() && ! is_tag() ) {

				echo '<li class="item-current item-archive"><strong class="bread-current bread-archive">' . post_type_archive_title( '', false ) . '</strong></li>';

			} elseif ( is_archive() && is_tax() && ! is_category() && ! is_tag() ) {

				// If post is a custom post type
				$post_type = get_post_type();

				// If it is a custom post type display name and link
				if ( $post_type && 'post' !== $post_type ) {

					$post_type_object  = get_post_type_object( $post_type );
					$post_type_archive = get_post_type_archive_link( $post_type );

					echo '<li class="item-cat item-custom-post-type-' . $post_type . '"><a class="bread-cat bread-custom-post-type-' . $post_type . '" href="' . $post_type_archive . '" title="' . $post_type_object->labels->name . '">' . $post_type_object->labels->name . '</a></li>';
					echo '<li class="separator"> ' . $separator . ' </li>';

				}

				$custom_tax_name = get_queried_object()->name;
				echo '<li class="item-current item-archive"><strong class="bread-current bread-archive">' . $custom_tax_name . '</strong></li>';

			} elseif ( is_single() ) {

				// If post is a custom post type
				$post_type = get_post_type();

				// If it is a custom post type display name and link
				if ( $post_type && 'post' !== $post_type ) {

					$post_type_object  = get_post_type_object( $post_type );
					$post_type_archive = get_post_type_archive_link( $post_type );

					echo '<li class="item-cat item-custom-post-type-' . $post_type . '"><a class="bread-cat bread-custom-post-type-' . $post_type . '" href="' . $post_type_archive . '" title="' . $post_type_object->labels->name . '">' . $post_type_object->labels->name . '</a></li>';
					echo '<li class="separator"> ' . $separator . ' </li>';

				}

				// Get post category info
				$category = get_the_category();

				if ( ! empty( $category ) ) {

					// Get last category post is in
					$categories    = array_values( $category );
					$last_category = end( $categories );

					// Get parent any categories and create array
					$get_cat_parents = rtrim( get_category_parents( $last_category->term_id, true, ',' ), ',' );
					$cat_parents     = explode( ',', $get_cat_parents );

					// Loop through parent categories and store in variable $cat_display
					$cat_display = '';

					foreach ( $cat_parents as $parents ) {
						$cat_display .= '<li class="item-cat">' . $parents . '</li>';
						$cat_display .= '<li class="separator"> ' . $separator . ' </li>';
					}

				}

				// If it's a custom post type within a custom taxonomy
				$taxonomy_exists = taxonomy_exists( $taxonomy );

				if ( ! empty( $taxonomy ) && $taxonomy_exists ) {

					$taxonomy_terms = get_the_terms( $post->ID, $taxonomy );

					if ( is_array( $taxonomy_terms ) ) {
						$cat_id       = $taxonomy_terms[0]->term_id;
						$cat_nicename = $taxonomy_terms[0]->slug;
						$cat_link     = get_term_link( $taxonomy_terms[0]->term_id, $taxonomy );
						$cat_name     = $taxonomy_terms[0]->name;
					}
				}

				// if post is in a custom taxonomy
				if ( ! empty( $cat_id ) ) {
					echo '<li class="item-cat item-cat-' . $cat_id . ' item-cat-' . $cat_nicename . '"><a class="bread-cat bread-cat-' . $cat_id . ' bread-cat-' . $cat_nicename . '" href="' . $cat_link . '" title="' . $cat_name . '">' . $cat_name . '</a></li>';
					echo '<li class="separator"> ' . $separator . ' </li>';
					echo '<li class="item-current item-' . $post->ID . '"><strong class="bread-current bread-' . $post->ID . '" title="' . get_the_title() . '">' . get_the_title() . '</strong></li>';

				// Else if the post is in a category
				} else if ( ! empty( $last_category ) && empty( $cat_id ) ) {

					echo $cat_display;
					echo '<li class="item-current item-' . $post->ID . '"><strong class="bread-current bread-' . $post->ID . '" title="' . get_the_title() . '">' . get_the_title() . '</strong></li>';

				} else {

					echo '<li class="item-current item-' . $post->ID . '"><strong class="bread-current bread-' . $post->ID . '" title="' . get_the_title() . '">' . get_the_title() . '</strong></li>';
				}

			} elseif ( is_category() ) {

				// Category page
				echo '<li class="item-current item-cat"><strong class="bread-current bread-cat">' . single_cat_title( '', false ) . '</strong></li>';

			} elseif ( is_page() ) {

				// Standard page
				if ( $post->post_parent ) {

					// If child page, get parents
					$anc = get_post_ancestors( $post->ID );

					// Get parents in the right order
					$anc = array_reverse( $anc );

					// Parent page loop
					if ( ! isset( $parents ) ) {
						$parents = null;
					}
					foreach ( $anc as $ancestor ) {
						$parents .= '<li class="item-parent item-parent-' . $ancestor . '"><a class="bread-parent bread-parent-' . $ancestor . '" href="' . get_permalink( $ancestor ) . '" title="' . get_the_title( $ancestor ) . '">' . get_the_title( $ancestor ) . '</a></li>';
						$parents .= '<li class="separator separator-' . $ancestor . '"> ' . $separator . ' </li>';
					}

					// Display parent pages
					echo $parents;

					// Current page
					echo '<li class="item-current item-' . $post->ID . '"><strong title="' . get_the_title() . '"> ' . get_the_title() . '</strong></li>';

				} else {

					// Just display current page if not parents
					echo '<li class="item-current item-' . $post->ID . '"><strong class="bread-current bread-' . $post->ID . '"> ' . get_the_title() . '</strong></li>';

				}

			} elseif ( is_tag() ) {

				// Tag page

				// Get tag information
				$term_id       = get_query_var( 'tag_id' );
				$taxonomy      = 'post_tag';
				$args          = 'include=' . $term_id;
				$terms         = get_terms( $taxonomy, $args );
				$get_term_id   = $terms[0]->term_id;
				$get_term_slug = $terms[0]->slug;
				$get_term_name = $terms[0]->name;

				// Display the tag name
				echo '<li class="item-current item-tag-' . $get_term_id . ' item-tag-' . $get_term_slug . '"><strong class="bread-current bread-tag-' . $get_term_id . ' bread-tag-' . $get_term_slug . '">' . $get_term_name . '</strong></li>';

			} elseif ( is_day() ) {

				// Day archive

				// Year link
				echo '<li class="item-year item-year-' . get_the_time( 'Y' ) . '"><a class="bread-year bread-year-' . get_the_time( 'Y' ) . '" href="' . get_year_link( get_the_time( 'Y' ) ) . '" title="' . get_the_time( 'Y' ) . '">' . get_the_time( 'Y' ) . ' Archives</a></li>';
				echo '<li class="separator separator-' . get_the_time( 'Y' ) . '"> ' . $separator . ' </li>';

				// Month link
				echo '<li class="item-month item-month-' . get_the_time( 'm' ) . '"><a class="bread-month bread-month-' . get_the_time( 'm' ) . '" href="' . get_month_link( get_the_time( 'Y' ), get_the_time( 'm' ) ) . '" title="' . get_the_time( 'M' ) . '">' . get_the_time( 'M' ) . ' Archives</a></li>';
				echo '<li class="separator separator-' . get_the_time( 'm' ) . '"> ' . $separator . ' </li>';

				// Day display
				echo '<li class="item-current item-' . get_the_time( 'j' ) . '"><strong class="bread-current bread-' . get_the_time( 'j' ) . '"> ' . get_the_time( 'jS' ) . ' ' . get_the_time( 'M' ) . ' Archives</strong></li>';

			} elseif ( is_month() ) {

				// Month Archive

				// Year link
				echo '<li class="item-year item-year-' . get_the_time( 'Y' ) . '"><a class="bread-year bread-year-' . get_the_time( 'Y' ) . '" href="' . get_year_link( get_the_time( 'Y' ) ) . '" title="' . get_the_time( 'Y' ) . '">' . get_the_time( 'Y' ) . ' Archives</a></li>';
				echo '<li class="separator separator-' . get_the_time( 'Y' ) . '"> ' . $separator . ' </li>';

				// Month display
				echo '<li class="item-month item-month-' . get_the_time( 'm' ) . '"><strong class="bread-month bread-month-' . get_the_time( 'm' ) . '" title="' . get_the_time( 'M' ) . '">' . get_the_time( 'M' ) . ' Archives</strong></li>';

			} elseif ( is_year() ) {

				// Display year archive
				echo '<li class="item-current item-current-' . get_the_time( 'Y' ) . '"><strong class="bread-current bread-current-' . get_the_time( 'Y' ) . '" title="' . get_the_time( 'Y' ) . '">' . get_the_time( 'Y' ) . ' Archives</strong></li>';

			} elseif ( is_author() ) {

				// Auhor archive

				// Get the author information
				global $author;
				$userdata = get_userdata( $author );

				// Display author name
				echo '<li class="item-current item-current-' . $userdata->user_nicename . '"><strong class="bread-current bread-current-' . $userdata->user_nicename . '" title="' . $userdata->display_name . '">' . 'Author: ' . $userdata->display_name . '</strong></li>';

			} elseif ( get_query_var( 'paged' ) ) {

				// Paginated archives
				echo '<li class="item-current item-current-' . get_query_var( 'paged' ) . '"><strong class="bread-current bread-current-' . get_query_var( 'paged' ) . '" title="Page ' . get_query_var( 'paged' ) . '">' . __( 'Page' ) . ' ' . get_query_var( 'paged' ) . '</strong></li>';

			} else if ( is_search() ) {

				// Search results page
				echo '<li class="item-current item-current-' . get_search_query() . '"><strong class="bread-current bread-current-' . get_search_query() . '" title="Search results for: ' . get_search_query() . '">Search results for: ' . get_search_query() . '</strong></li>';

			} elseif ( is_404() ) {

				// 404 page
				echo '<li>' . 'Error 404' . '</li>';
			}

			echo '</ol>';
		}

	}

}
