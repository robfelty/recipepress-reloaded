<?php

namespace Recipepress\Inc\Common\Traits;

use Recipepress\Inc\Core\Options;

/**
 * Trait Custom_Taxonomies
 */
trait Custom_Taxonomies {

	/**
	 * Get the registered custom taxonomies related to our recipes
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
			$taxonomies[] = array(
				'tax_settings' => array(
					'settings_key'  => Options::get_option( 'rpr_' . strtolower( $key ) . '_key', strtolower( $key ) ),
					'label'         => Options::get_option( 'rpr_' . strtolower( $key ) . '_label', $key ),
					'slug'          => Options::get_option( 'rpr_' . strtolower( $key ) . '_slug', strtolower( $key ) ),
					'hierarchy'     => Options::get_option( 'rpr_' . strtolower( $key ) . '_hierarchical' ),
					'show_in_table' => Options::get_option( 'rpr_' . strtolower( $key ) . '_show', true ),
					'show_on_front' => Options::get_option( 'rpr_' . strtolower( $key ) . '_show_front', true ),
				),
			);
		}

		return apply_filters( 'rpr_custom_taxonomies', $taxonomies );
	}
}
