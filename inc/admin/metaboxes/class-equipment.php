<?php

namespace Recipepress\Inc\Admin\Metaboxes;

use Recipepress\Inc\Common\Abstracts\Metadata;
use Recipepress\Inc\Admin\Taxonomies\Equipment as Taxonomy;
use Recipepress\Inc\Core\Options;

/**
 * Class Equipment
 *
 * Creates our recipe equipment feature.
 *
 * @since 2.0.0
 *
 * @author Kemory Grubb
 */
class Equipment extends Metadata {

	/**
	 * An instance of our equipment taxonomy
	 *
	 * @since 2.0.0
	 *
	 * @var Taxonomy
	 */
	public $taxonomy;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   2.0.0
	 *
	 * @param   string $plugin_name The ID of this plugin.
	 * @param   string $version The current version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->taxonomy = new Taxonomy( $plugin_name, $version );
		parent::__construct( $plugin_name, $version, 'rpr_equipment_metabox', 'rpr_recipe_equipment', __DIR__, false, false );
	}

	/**
	 * Add a metabox to the WP post edit screen
	 *
	 * @since 2.0.0
	 *
	 * @uses  add_meta_box
	 *
	 * @return void
	 */
	public function add_metabox() {

		if ( ! Options::get_option( 'rpr_recipe_equipment' ) ) {
			return;
		}

		add_meta_box(
			$this->metabox_id,
			__( 'Equipment', 'recipepress-reloaded' ),
			array( $this, 'render_metabox' ),
			'rpr_recipe',
			'normal',
			'high'
		);
	}

	/**
	 * Should we display this metabox.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	protected function display_metabox() {

		return true;
	}

    /**
     * Check the presence of, sanitizes then saves
     * the recipe equipment
     *
     * @since 2.0.0
     *
     * @uses  update_post_meta()
     * @uses  wp_set_post_terms()
     * @uses  sanitize_text_field()
     *
     * @param int      $recipe_id The post ID of the recipe post.
     * @param array    $data      The data passed from the post custom metabox.
     * @param \WP_Post $recipe    The recipe object this data is being saved to.
     *
     * @return bool|\WP_Error
     */
    public function save_metabox_metadata( $recipe_id, $data, $recipe ) {

        if ( ! $this->check_nonce( $data ) ) {
	        return false;
        }

        $equipment = isset( $data['rpr_recipe_equipment'] ) ? $data['rpr_recipe_equipment'] : array();

        // A new array to contain all non-empty line from the form.
        $non_empty = array();

        // An array of all ingredient term_ids to create a relation to the recipe.
        $equipment_taxonomy = array();

        foreach ( (array) $equipment as $equip ) {

                if ( $equip['name'] ) {
	                // Adding a key for future work with Gutenberg/React
	                $equip['key'] = empty( $equip['key'] ) ? substr( md5( mt_rand() ), 0, 9 ) : $equip['key'];
                    // We need to find the term_id of the equipment and add a taxonomy relation to the recipe.
                    $term = term_exists( $equip['name'], 'rpr_equipment' );

                    if ( 0 === $term || null === $term ) {
                        // Equipment is not an existing term, create it.
                        $term = wp_insert_term( sanitize_text_field( $equip['name'] ), 'rpr_equipment' );
                    }

                    if ( is_wp_error( $term ) ) {
                        return new \WP_Error( 'invalid_term', 'Invalid equipment name: ' . $equip['name'] );
                    }

                    // Now we have a valid term id!
                    $term_id = (int) $term['term_id'];

                    // This means it's a new equipment being created.
                    if ( '' === get_term_meta( $term_id, 'rpr_equipment_term_meta', true ) ) {
                        update_term_meta( $term_id, 'rpr_equipment_term_meta', [ 'notes' => $equip['notes'] ] );
                    }

                    // Set it to the equipment array.
                    $equip['equipment_id'] = $term_id;

                    // Add it to the taxonomy list.
                    $equipment_taxonomy[] = $term_id;

                    // Add it to the save list.
                    $non_empty[] = array_map( 'sanitize_text_field', $equip );
                }

        }
        // Save the recipe <-> ingredient taxonomy relationship.
        wp_set_post_terms( $recipe_id, $equipment_taxonomy, 'rpr_equipment' );

        // Save the new metadata array.
        update_post_meta( $recipe_id, 'rpr_recipe_equipment', $non_empty );
    }

    /**
     * Return an array of all our recipe equipment
     *
     * @since 2.0.0
     *
     * @return string[]
     */
    public function equipment_list() {
        $equipment_list = array();

        $equipment = get_terms(
            'rpr_equipment',
            array(
                'orderby' => 'name',
                'order'   => 'ASC',
            )
        );

        if ( is_wp_error( $equipment ) ) {
            return $equipment_list;
        }

        foreach ( $equipment as $equip ) {
            $equipment_list[] = $equip->name;
        }

        return $equipment_list;
    }

    /**
     * Adds a new column to the rpr_equipment taxonomy table
     *
     * @since 2.0.0
     *
     * @see https://www.smashingmagazine.com/2015/12/how-to-use-term-meta-data-in-wordpress/
     *
     * @param array $columns The column header labels keyed by column ID
     *
     * @return array
     */
    public function add_equipment_image_column( $columns ) {
        unset( $columns['slug'] ); // Remove the slug column

        return array_slice( $columns, 0, 1, true)
            + array( 'rpr_equipment_image' => __( 'Image', 'recipepress-reloaded' ) )
            + array_slice( $columns, 1, count( $columns ) - 1, true);
    }

    /**
     * Adds content to the new column
     *
     * @since 2.0.0
     *
     * @param string $content     The content of the column
     * @param string $column_name Name of the column
     * @param int    $term_id     The term ID
     *
     * @return string
     */
    public function add_equipment_image_column_content( $content, $column_name, $term_id ) {
        //$term = get_term( $term_id, 'rpr_equipment' );
        if ( 'rpr_equipment_image' === $column_name) {
            $content = '%%%%';
        }

        return $content;
    }

	/**
	 * Register this meta information
	 *
	 * This is used in the WP REST API's `meta` field
	 *
	 * @since 2.1.0
	 *
	 * @return void
	 */
	public function register_meta() {
		// TODO: Implement register_meta() method.
	}

	/**
	 * Remove equipment from the Gutenberg sidebar
	 *
	 * @since 2.1.0
	 *
	 * @param \WP_REST_Response $response The response object
	 * @param \WP_Taxonomy      $taxonomy The original taxonomy object
	 * @param \WP_REST_Request  $request  Request used to generate the response
	 *
	 * @return \WP_REST_Response
	 */
	public function remove_equipment_gutenberg( $response, $taxonomy, $request ) {

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';

		// Context is edit in the editor
		if ( 'edit' === $context && 'rpr_equipment' === $taxonomy->name ) {
			$data_response = $response->get_data();
			$data_response['visibility']['show_ui'] = false;
			$response->set_data( $data_response );
		}

		return $response;
	}
}
