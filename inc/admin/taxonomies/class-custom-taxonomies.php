<?php

namespace Recipepress\Inc\Admin\Taxonomies;

use Recipepress as NS;
use Recipepress\Inc\Common\Abstracts\Taxonomy;
use Recipepress\Inc\Core\Options;

/**
 * Handles the recipe's custom taxonomies
 *
 * @package    Recipepress
 * @subpackage Recipepress/inc/admin/taxonomies
 * @author     Kemory Grubb <kemory@wzymedia.com>
 */
class Custom_Taxonomies extends Taxonomy {

	use NS\Inc\Common\Traits\Utilities;

	/**
	 * The internal identifier of our post type
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string $cpt_name This should be `rpr_recipe`.
	 */
	public $cpt_name;

	/**
	 * Custom post type slug.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string $cpt_slug The custom post type slug from the settings.
	 */
	public $cpt_slug;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_name The ID of this plugin.
	 * @param string $version     The current version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		parent::__construct( $plugin_name, $version );

		$this->cpt_name = 'rpr_recipe';
		$this->cpt_slug = $this->sanitize_input(
			Options::get_option( 'rpr_recipe_labels', array( 'singular' => 'Recipe', 'plural' => 'Recipes' ) )['plural']
			?: __( 'Recipes', 'recipepress-reloaded' )
		);
	}

	/**
	 * Creates and register the custom taxonomies attached to our recipe.
	 *
	 * @since   1.0.0
	 * @uses    register_taxonomy()
	 * @return  void
	 */
	public function register_taxonomy() {

		$custom_taxonomies = $this->get_custom_taxonomies();

		foreach ( $custom_taxonomies as $tax ) {
			$plural   = $tax['tax_settings']['labels']['plural'];
			$single   = $tax['tax_settings']['labels']['singular'];
			$tax_name = 'rpr_' . $this->sanitize_input( $single );

			$opts['hierarchical']          = $tax['tax_settings']['hierarchy'];
			$opts['public']                = true;
			$opts['query_var']             = $tax_name;
			$opts['show_admin_column']     = $tax['tax_settings']['show_in_table'];
			$opts['show_in_nav_menus']     = true;
			$opts['show_tag_cloud']        = true;
			$opts['show_ui']               = true;
			$opts['sort']                  = '';
			$opts['show_in_rest']          = true;
			$opts['rest_base']             = 'rpr/' . $this->sanitize_input( $plural );
			$opts['rest_controller_class'] = 'WP_REST_Terms_Controller';

			/**
			 * Note: If you want to ensure that your custom taxonomy behaves like a tag,
			 * you must add the option 'update_count_callback' => '_update_post_term_count'.
			 * Not doing so will result in multiple comma-separated items added at once being saved as a single value,
			 * not as separate values. This can cause undue stress when using get_the_term_list and other term display functions.
			 */
			$opts['update_count_callback'] = $tax['tax_settings']['hierarchy'] ? '_update_post_term_count' : '';

			$opts['capabilities']['assign_terms'] = 'edit_posts';
			$opts['capabilities']['delete_terms'] = 'manage_categories';
			$opts['capabilities']['edit_terms']   = 'manage_categories';
			$opts['capabilities']['manage_terms'] = 'manage_categories';

			$opts['labels']['add_new_item']               = sprintf( __( 'Add New %1$s', 'recipepress-reloaded' ), $single );
			$opts['labels']['add_or_remove_items']        = sprintf( __( 'Add or remove %1$s', 'recipepress-reloaded' ), $plural );
			$opts['labels']['all_items']                  = $plural;
			$opts['labels']['choose_from_most_used']      = sprintf( __( 'Choose from most used %1$s', 'recipepress-reloaded' ), $plural );
			$opts['labels']['edit_item']                  = sprintf( __( 'Edit %1$s', 'recipepress-reloaded' ), $single );
			$opts['labels']['menu_name']                  = $plural;
			$opts['labels']['name']                       = $plural;
			$opts['labels']['new_item_name']              = sprintf( __( 'New %1$s Name', 'recipepress-reloaded' ), $single );
			$opts['labels']['not_found']                  = sprintf( __( 'No %1$s Found', 'recipepress-reloaded' ), $plural );
			$opts['labels']['parent_item']                = sprintf( __( 'Parent %1$s', 'recipepress-reloaded' ), $single );
			$opts['labels']['parent_item_colon']          = sprintf( __( 'Parent %1$s', 'recipepress-reloaded' ), $single );
			$opts['labels']['popular_items']              = sprintf( __( 'Popular %1$s', 'recipepress-reloaded' ), $plural );
			$opts['labels']['search_items']               = sprintf( __( 'Search %1$s', 'recipepress-reloaded' ), $plural );
			$opts['labels']['back_to_items']              = sprintf( __( 'Back to %1$s', 'recipepress-reloaded' ), $plural );
			$opts['labels']['separate_items_with_commas'] = sprintf( __( 'Separate %1$s with commas', 'recipepress-reloaded' ), $plural );
			$opts['labels']['singular_name']              = $single;
			$opts['labels']['update_item']                = sprintf( __( 'Update %1$s', 'recipepress-reloaded' ), $single );
			$opts['labels']['view_item']                  = sprintf( __( 'View %1$s', 'recipepress-reloaded' ), $single );

			$opts['rewrite']['ep_mask']      = EP_NONE;
			$opts['rewrite']['hierarchical'] = false;
			$opts['rewrite']['slug']       = $this->cpt_slug . '/' . $this->sanitize_input( $plural );
			$opts['rewrite']['with_front'] = false;

			$opts = apply_filters( 'rpr_custom_taxonomy_options', $opts );

			register_taxonomy( $tax_name, $this->cpt_name, $opts );
		}
	}

	/**
	 * Add a checkbox to choose default term
	 *
	 * Uses the `{$taxonomy}_edit_form` action to add a checkbox to each
	 * taxonomy's terms
	 *
	 * @since 1.3.0
	 *
	 * @uses \add_action()
	 *
	 * @return void
	 */
	public function default_term_checkbox() {

		$custom_taxonomies = $this->get_custom_taxonomies();

		foreach ( $custom_taxonomies as $tax ) {
			$single   = $tax['tax_settings']['labels']['singular'];
			$tax_name = 'rpr_' . $this->sanitize_input( $single );

			// add_action( $tax_name . '_add_form_fields', array( $this, 'form_fields' ), 99, 2 );
			// add_action( $tax_name . '_edit_form_fields', array( $this, 'form_fields' ), 99, 2 );
			add_action( $tax_name . '_edit_form', array( $this, 'form_fields' ), 10, 2 );
		}
	}

	/**
	 * Add a checkbox to choose default term
	 *
	 * @since 1.3.0
	 *
	 * @param \WP_Term $tag      Current taxonomy term object.
	 * @param string   $taxonomy Current taxonomy slug.
	 *
	 * @return void
	 */
	public function form_fields( $tag , $taxonomy ) {
			$rpr_default_term = (bool) get_term_meta( (int) $tag->term_id, 'rpr_default_term', true ) ?: false;
		?>
		<table class="form-table">
			<tbody>
				<tr class="form-field term-use_in_listings-wrap">
					<th scope="row" valign="top">
						<label for="rpr_default_term"><?php printf( __( 'Default %s', 'recipepress-reloaded' ), ucfirst( str_replace( 'rpr_', '', $taxonomy ) ) ) ; ?></label>
					</th>
					<td>
						<input type="hidden" name="rpr_default_term" value="0">
						<input type="checkbox" name="rpr_default_term" id="rpr_default_term" size="40"
							   value="1" <?php checked( $rpr_default_term, 1 ); ?> ><br/>
						<span class="description"><?php esc_html_e( 'Set this taxonomy term as the default for recipes without a selected term.', 'recipepress-reloaded' ); ?></span>
					</td>
				</tr>
			</tbody>
			<?php wp_nonce_field( 'rpr_default_term', 'update_rpr_default_term' ); ?>
		</table>
	<?php }

	/**
	 * Saves the default term
	 *
	 * Each taxonomy can have 1 default term. This is used to fill out the
	 * required data for recipe JSON-LD markup.
	 *
	 * @since 1.3.0
	 *
	 * @param int    $term_id
	 * @param int    $tt_id
	 * @param string $taxonomy
	 *
	 * @return int|void
	 */
	public function save_default_taxonomy( $term_id = null, $tt_id = null, $taxonomy = null ) {

		global $wpdb;

		// We are limiting this to the edit term page that has `rpr_default_term` set.
		if ( isset( $_POST['action'], $_POST['rpr_default_term'] ) && 'editedtag' === $_POST['action'] ) { // phpcs:ignore

			check_admin_referer( 'rpr_default_term', 'update_rpr_default_term' );

			$default_term = (bool) $_POST['rpr_default_term'] ?: null; // phpcs:ignore

			if ( null === $default_term ) {
				return;
			}

			if ( true === $default_term ) {

				$results = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT meta_id
						FROM $wpdb->termmeta
						JOIN $wpdb->term_taxonomy ON $wpdb->term_taxonomy.term_id = $wpdb->termmeta.term_id
						WHERE meta_key = 'rpr_default_term' AND taxonomy = %s",
						$taxonomy
					)
				);

				if ( empty( $results ) ) {

					return $wpdb->query( $wpdb->prepare( "INSERT INTO $wpdb->termmeta ( term_id, meta_key, meta_value ) VALUES ( %d, %s, %s )", $term_id, 'rpr_default_term', '1' ) );
				}

				foreach ( $results as $result ) {
					$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->termmeta WHERE meta_id = %d", $result->meta_id ) );
				}

				return $wpdb->query( $wpdb->prepare( "INSERT INTO $wpdb->termmeta ( term_id, meta_key, meta_value ) VALUES ( %d, %s, %s )", $term_id, 'rpr_default_term', '1' ) );
			}

			if ( false === $default_term ) {

				return $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->termmeta WHERE term_id = %d AND meta_key = %s", $term_id, 'rpr_default_term' ) );
			}
		}
	}

	/**
	 * Indicates the default term
	 *
	 * Adds a marker to indicate the default term for a taxonomy
	 * in the WP admin terms table. There can only be 1 default.
	 *
	 * @since 1.3.0
	 *
	 * @param string   $tag_name The term name
	 * @param \WP_Term $tag      Term object
	 *
	 * @return string
	 */
	public function default_term_marker( $tag_name, $tag ) {
		global $wpdb;

		if (  $tag instanceof \WP_Term ) {
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->termmeta WHERE meta_key = 'rpr_default_term' AND term_id = %d", $tag->term_id ) );

			if ( ! empty( $results ) ) {

				return $tag_name
					   . '<span title="'
					   . __( 'Default', 'recipepress-reloaded' )
					   . '" style="height: 10px; width: 10px; background-color: #0aa525; border-radius: 50%; display: inline-block; margin-left: 10px;"></span>';
			}
		}

		return $tag_name;
	}

	/**
	 * @param int      $recipe_id
	 * @param \WP_Post $recipe
	 * @param array    $data
	 *
	 * @return mixed
	 */
	public function save_default_taxonomy_terms( $recipe_id, $recipe, $data ) {
		$w = $recipe_id;
		$x = $recipe;
		$y = $data;

		if ( 'publish' === $recipe->post_status && 'rpr_recipe' === $recipe->post_type ) {

			$defaults = array( 'your_taxonomy_id' => array( 'your_term_slug' ) );
			$taxonomies = get_object_taxonomies( $recipe->post_type );

			foreach ( (array) $taxonomies as $taxonomy ) {
				$terms = wp_get_post_terms( $recipe_id, $taxonomy );
				if ( empty( $terms ) && array_key_exists( $taxonomy, $defaults ) ) {
					wp_set_object_terms( $recipe_id, $defaults[$taxonomy], $taxonomy );
				}
			}
		}

		return $w;
	}

}
