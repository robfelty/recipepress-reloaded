<?php

namespace Recipepress\Inc\Common\Traits;

use Mockery\Exception;
use Recipepress\Inc\Core\Activator;
use Recipepress\Inc\Core\Options;
use Recipepress\Inc\Libraries\Pluralizer\Pluralizer;

/**
 * Trait Utilities.
 *
 * @since 1.0.0
 *
 * @package Recipepress\Inc\Common\Traits
 */
trait Utilities {

	/**
	 * Creates a data attribute with the ingredient quantity as a float
	 *
	 * @param string $value The value we are working with.
	 * @param bool   $data  Return the `data` attribute.
	 *
	 * @return float|string
	 */
	public function data_ingredient_quantity( $value, $data ) {

		$value = trim( $value );

		if ( '' === $value ) {
			return null;
		}

		$result_1 = null;
		$result_2 = null;
		$numbers  = array();

		if ( false !== strpos( $value, ' ' ) ) { // "1 1/2"
			$numbers  = (array) explode( ' ', $value );
			$result_1 =  $numbers[0];
		} elseif ( false !== strpos( $value, '-' ) ) { // "1/2-3/4"
			$numbers  = (array) explode( '/', explode( '-', $value )[1] );
			$result_1 = 1 === count( $numbers ) ? $numbers[0] : round( $numbers[0] / $numbers[1], 2 );
		} elseif ( false !== strpos( $value, '/' ) ) { // "1/2"
			$numbers = (array) array_filter( explode( '/', $value ), 'is_numeric' );
			$result_1 = 1 === count( $numbers ) ? $numbers[0] : round( $numbers[0] / $numbers[1], 2 );
		} else { // "1"
			$result_1 = (float) $value;
		}

		foreach ( $numbers as $number ) {
			if ( false !== strpos( $number, '/' ) ) {
				$_numbers = (array) explode( '/', trim( $number ) );
				$result_2 = round( $_numbers[0] / $_numbers[1], 2 );
			}
		}

		if ( $data ) {
			return 'data-ingredient-quantity="' . ( (float) $result_1 + (float) $result_2 ) . '"';
		}

		return (float) $result_1 + (float) $result_2;
	}

	/**
	 * Creates a data attribute with the ingredient unit
	 *
	 * @param string $value The value we are working with.
	 * @param bool   $data  Return the `data` attribute.
	 *
	 * @return string
	 */
	public function data_ingredient_unit( $value, $data ) {

		$units = explode( ',', Options::get_option( 'rpr_ingredient_unit_list' ) );
		$unit  = in_array( $value, $units, true ) ? $value : null;

		if ( $data && $unit ) {
			return 'data-ingredient-unit="' . esc_attr( $unit ) . '"';
		}

		return $unit;
	}

	/**
	 * Array map for multidimensional arrays
	 *
	 * A utility function to  recursively apply a function to each value of a
	 * multidimensional array.
	 *
	 * @param string $function Name of global function.
	 * @param array  $arr Array to work on.
	 *
	 * @return array
	 */
	public function array_walker( $function, array $arr ) {

		$result = array();
		foreach ( $arr as $key => $val ) {
			$result[ $key ] = ( is_array( $val ) ? $this->array_walker( $function, $val ) : $function( $val ) );
		}

		return $result;
	}

	/**
	 * Returns and array of all the taxonomies created on the options page.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_custom_taxonomies() {

		$taxonomies        = array();
		$custom_taxonomies = Options::get_option( 'rpr_taxonomy_selection' );
		$custom_taxonomies = explode( ',', $custom_taxonomies );

		foreach ( $custom_taxonomies as $key ) {
			$labels['singular'] = ucwords( $key );
			$labels['plural']   = Pluralizer::pluralize( ucwords( $key ) );
			$taxonomies[] = array(
				'tax_settings' => array(
					'settings_key'   => Options::get_option( 'rpr_' . $this->sanitize_input( $key ) . '_key', $this->sanitize_input( $key ) ),
					'slug'           => Options::get_option( 'rpr_' . $this->sanitize_input( $key ) . '_slug', $this->sanitize_input( $key ) ),
					'labels'         => Options::get_option( 'rpr_' . $this->sanitize_input( $key ) . '_labels', $labels ),
					'hierarchy'      => Options::get_option( 'rpr_' . $this->sanitize_input( $key ) . '_hierarchical' ),
					'show_in_table'  => Options::get_option( 'rpr_' . $this->sanitize_input( $key ) . '_show' ),
					'show_in_filter' => Options::get_option( 'rpr_' . $this->sanitize_input( $key ) . '_filter' ),
					'show_on_front'  => Options::get_option( 'rpr_' . $this->sanitize_input( $key ) . '_show_front' ),
				),
			);
		}

		return apply_filters( 'rpr_custom_taxonomies', $taxonomies );
	}

	/**
	 * Compares a provided URL against our home URL
	 *
	 * Return `true` if the URL is on our domain,
	 * `false` for an external URL
	 *
	 * @since 1.0.0
	 *
	 * @uses \wp_parse_url()
	 * @uses \get_home_url()
	 *
	 * @param string $url The URL we are checking
	 *
	 * @return bool
	 */
	public function internal_url( $url ) {

		$url  = (string) \wp_parse_url( $url, PHP_URL_HOST );
		$home = (string) \wp_parse_url( get_home_url(), PHP_URL_HOST );

		return $url === $home;
	}

	/**
	 * Generates a time ago string
	 *
	 * @since 1.0.0
	 *
	 * @throws \Exception
	 * @uses   \DateTime
	 *
	 * @param string $datetime
	 * @param bool   $full
	 *
	 * @return string
	 */
	public function time_ago( $datetime, $full = null ) {

		if ( false === strtotime( $datetime ) ) {
			return false;
		}

		$now  = new \DateTime;
		$ago  = new \DateTime( $datetime );
		$diff = $now->diff( $ago );

		$diff->w = floor( $diff->d / 7 );
		$diff->d -= $diff->w * 7;

		$string = array(
			'y' => 'year',
			'm' => 'month',
			'w' => 'week',
			'd' => 'day',
			'h' => 'hour',
			'i' => 'minute',
			's' => 'second',
		);

		foreach ( $string as $k => &$v ) {
			if ( $diff->$k ) {
				$v = $diff->$k . ' ' . $v . ( $diff->$k > 1 ? 's' : '' );
			} else {
				unset( $string[ $k ] );
			}
		}

		if ( ! $full ) {
			$string = array_slice( $string, 0, 1 );
		}

		return $string ? implode( ', ', $string ) . ' ago' : 'just now';
	}

	/**
	 * Sanitizes user input
	 *
	 * @since 1.0.0
	 *
	 * @param $input
	 *
	 * @return string
	 */
	public function sanitize_input( $input ) {

		$input = strip_tags( $input );
		$input = str_replace( '%', '', $input );

		if ( function_exists( 'mb_strtolower' ) && seems_utf8( $input ) ) {
			$input = mb_strtolower( $input, 'UTF-8' );
		}

		$input = strtolower( $input );
		$input = preg_replace( '/\s+/', '-', $input );
		$input = trim( $input, '-' );
		$input = remove_accents( $input );

		return $input;
	}

	/**
	 * Reset all our options
	 *
	 * @since 1.0.0
	 *
	 * @var array $options
	 *
	 * @return void
	 */
	public function reset_all_options() {

		// phpcs:ignore
		if ( ! wp_verify_nonce( $_POST['reset_nonce'], 'rpr-options-reset' )
		|| ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		$options = Activator::default_settings( true );

		// Set the options to the defaults from the '$options' array.
		$updated = update_option( 'recipepress_settings', $options );

		if ( $updated ) {
			wp_send_json_success();
		}

		wp_send_json_error();
	}

	/**
	 * Remove an array item by value in a multi-dimensional array
	 *
	 * @since 1.0.0
	 *
	 * @param array $arr The array we are working with
	 * @param mixed $val The value we want to remove
	 *
	 * @return array
	 */
	public function remove_element_by_value( $arr, $val ) {

		$out = array();

		if ( ! is_array( $arr ) ) {
			return $arr;
		}

		foreach ( $arr as $k => $v ) {
			if ( is_array( $v ) ) {
				$out[ $k ] = $this->remove_element_by_value( $v, $val ); // Recursion
				continue;
			}
			if ( $v === $val ) {
				continue;
			}

			$out[ $k ] = $v;
		}

		return $out;
	}

	/**
	 * Splits a recipe instruction string in an array
	 *
	 * We are trying to extract any word(s) before a `:` character at the front of the instruction
	 * to use as the `name` field for Google's guided recipe JSON-LD schema markup.
	 *
	 * @since 1.0.0
	 *
	 * @param string $input The full recipe instruction text
	 * @param string $piece The section of the recipe instruction we want
	 *
	 * @return string|bool|array
	 */
	public function parse_instruction( $input, $piece ) {

		if ( 'instruction' === $piece && false === strpos( $input, ':' )  ) {
			return $input;
		}

		if ( 'name' === $piece && false === strpos( $input, ':' )  ) {
            return false;
		}

		$arr   = array();
		$split = explode( ':', $input );

		foreach ( $split as $str) {
			$arr[] = trim( $str );
		}

		if ( 'name' === $piece ) {
            return $arr[0];
		}

		if ( 'instruction' === $piece ) {
            return $arr[1];
		}

		return $input;
	}

	/**
	 * Display a string of HTML stars based on counts
	 *
	 * @since 1.3.0
	 *
	 * @param int  $count The number of stars to display
	 * @param bool $empty Display stars for a 0 count
	 *
	 * @return string
	 */
	public function html_stars( $count, $empty ) {
		switch ( $count ) {
			case 5:
				return '&starf;&starf;&starf;&starf;&starf;';
			case 4:
				return '&starf;&starf;&starf;&starf;&star;';
			case 3:
				return '&starf;&starf;&starf;&star;&star;';
			case 2:
				return '&starf;&starf;&star;&star;&star;';
			case 1:
				return '&starf;&star;&star;&star;&star;';
			default:
				return ! $empty ? '&star;&star;&star;&star;&star;': '';
		}
	}

    /**
     * This generates an array of terms for each taxonomy attached to a specific term.
     * This function is based on the WP `get_body_class` function.
     *
     * @since 1.0.0
     *
     * @see https://goo.gl/QrCqnd
     *
     * @param \WP_Post $recipe The recipe.
     * @param string   $output The return type of our output `string|array`
     * @param array    $skip   An array of custom taxonomies to skip
     *
     * @return string|array
     */
    public function rpr_recipe_html_classes( $recipe, $output, $skip ) {

        if ( ! $recipe instanceof \WP_Post ) {
            return '';
        }

        $classes   = array();
        $classes[] = 'recipe-' . $recipe->ID;

        // All public taxonomies.
        $taxonomies = get_taxonomies( array( 'public' => true ) );

        if ( $skip && is_array( $skip )) {
            foreach ( $skip as $item ) {
                unset( $taxonomies[ $item ] );
            }
        }

        foreach ( (array) $taxonomies as $taxonomy ) {
            if ( is_object_in_taxonomy( $recipe->post_type, $taxonomy ) ) {
                foreach ( (array) get_the_terms( $recipe->ID, $taxonomy ) as $term ) {
                    if ( empty( $term->slug ) ) {
                        continue;
                    }
                    $term_class = sanitize_html_class( $term->slug, $term->term_id );
                    if ( is_numeric( $term_class ) || ! trim( $term_class, '-' ) ) {
                        $term_class = $term->term_id;
                    }
                    $classes[] = $term_class;
                }
            }
        }

        $classes = apply_filters( 'rpr/frontend/template/html_classes', $classes );

        if ( 'array' === $output ) {

            return array_unique( array_map( 'esc_attr', $classes ) );
        }

        return implode( ' ', array_unique( array_map( 'esc_attr', $classes ) ) );
    }

    /**
     * Get our custom taxonomies
     *
     * @since 2.0.0
     *
     * @return void
     */
    public function get_taxonomies() {
        $taxonomies = explode( ',', strtolower( Options::get_option( 'rpr_taxonomy_selection', '' ) ) );

        foreach ( $taxonomies as $taxonomy ) {
            $this->taxonomies[ $taxonomy ] = get_terms(
                array(
                    'taxonomy'   => 'rpr_' . $taxonomy,
                    'hide_empty' => true,
                )
            );
        }
    }

    /**
     * Mask input strings
     *
     * @since 2.0.0
     *
     * @param string $input Our input
     *
     * @return string
     */
    public function mask_input( $input ){

        return str_repeat( '*', strlen( $input )-4 ) . substr( $input, -4 );
    }

	/**
	 * Checks that a value is not an empty string,
	 * a `0` or `null`
	 * @since 2.4.2
	 *
	 * @param mixed $var
	 *
	 * @return bool
	 */
	public function not_empty_string_or_zero_or_null( $var ) {
		return ! empty( $var ) || ( is_numeric( $var ) && (int) $var === 0 );
	}

	/**
	 * Get our nutrition fields
	 *
	 * @since 2.3.0
	 *
	 * @return string[]|string[][]
	 */
	public function get_nutrition_fields( $output = 'all' ) {

		if ( 'default' === $output ) {
			return array(
				'rpr_recipe_calorific_value',
				'rpr_recipe_protein',
				'rpr_recipe_fat',
				'rpr_recipe_carbohydrate',
				'rpr_recipe_nutrition_per',
			);
		}

		if ( 'additional' === $output ) {
			return array_map(
				function( $item ) {
					return array( $item => 'rpr_recipe_' . str_replace( ' ', '_', strtolower( $item ) ) );
				},
				explode( ',', Options::get_option( 'rpr_additional_nutrition', '' ) )
			);
		}

		if ( 'all' === $output ) {
			return array_merge(
				array(
					'rpr_recipe_calorific_value',
					'rpr_recipe_protein',
					'rpr_recipe_fat',
					'rpr_recipe_carbohydrate',
					'rpr_recipe_nutrition_per',
				),
				array_map(
					function( $item ) {
						return 'rpr_recipe_' . str_replace( ' ', '_', strtolower( $item ) );
					},
					explode( ',', Options::get_option( 'rpr_additional_nutrition', '' ) )
				)
			);
		}
	}

	/**
	 * Searches for a key in an multidimensional arrays
	 *
	 * @since 2.7.0
	 *
	 * @param  array  $arr
	 * @param  string  $key
	 *
	 * @return bool
	 */
	public function multi_key_exists( array $arr, $key ) {
		if ( array_key_exists( $key, $arr ) ) {
			return true;
		}

		foreach ( $arr as $element ) {
			if ( is_array( $element ) && $this->multi_key_exists( $element, $key ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if a key exists in a multidimensional array and if it's truthy
	 *
	 * @since 2.7.0
	 *
	 * @param $key
	 * @param $array
	 *
	 * @return bool
	 */
	public function key_exists_and_truthy( $key, $array ) {
		foreach ( $array as $k => $v ) {
			if ( $key === $k ) {
				return (bool) $v;
			}
			if ( is_array( $v ) ) {
				if ( $this->key_exists_and_truthy( $key, $v ) ) {
					return true;
				}
			}
		}

		return false;
	}


}
