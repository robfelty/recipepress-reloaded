<?php

namespace Recipepress\Inc\Admin\Taxonomies;

use Recipepress as NS;
use Recipepress\Inc\Common\Abstracts\Taxonomy;
use Recipepress\Inc\Core\Options;
use Recipepress\Inc\Libraries\Pluralizer\Pluralizer;

/**
 * Handles the recipe's ingredient custom taxonomy.
 *
 * @package    Recipepress
 *
 * @author     Kemory Grubb <kemory@wzymedia.com>
 */
class Ingredient extends Taxonomy {

	use NS\Inc\Common\Traits\Utilities;

	/**
	 * Custom post type slug.
	 *
	 * @since    1.0.0
	 *
	 * @access   public
	 * @var      string $cpt_name The custom post type slug from the settings.
	 */
	public $cpt_name;

	/**
	 * The plural label of our "recipe" custom post type
	 *
	 * @since    1.0.0
	 *
	 * @access   public
	 * @var      string $cpt_plural The plural label of our "recipe" custom post type
	 */
	public $cpt_plural;

	/**
	 * The internal name of the "ingredient" taxonomy
	 *
	 * @since    1.0.0
	 *
	 * @access   public
	 * @var      string $tax_name This will always be `rpr_ingredient`
	 */
	public $tax_name;

	/**
	 * The slug used in URL for the "ingredient" taxonomy
	 *
	 * @since    1.0.0
	 *
	 * @access   public
	 * @var      string $tax_slug This is usually `ingredient`
	 */
	public $tax_slug;

	/**
	 * Ingredient singular label.
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 * @var    string $singular The singular form of the taxonomy label
	 */
	private $singular;

	/**
	 * Ingredient pluralized label.
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 * @var    string $plural The plural form of the taxonomy label
	 */
	private $plural;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 *
	 * @param string $plugin_name The ID of this plugin.
	 * @param string $version     The current version of this plugin.
	 *
	 * @return void
	 */
	public function __construct( $plugin_name, $version ) {

		parent::__construct( $plugin_name, $version );

		$this->cpt_name   = 'rpr_recipe';
		$this->cpt_plural = Options::get_option( 'rpr_recipe_labels', array( 'singular' => 'Recipe', 'plural' => 'Recipes' ) )['plural']
		                    ?: __( 'Recipes', 'recipepress-reloaded' );
		$this->tax_name   = 'rpr_ingredient';
		//$this->tax_slug   = Options::get_option( 'rpr_ingredient_slug', 'ingredient' );
		$this->singular   = Options::get_option( 'rpr_ingredient_labels', array( 'singular' => 'Ingredient', 'plural' => 'Ingredients' ) )['singular']
			                ?: __( 'Ingredient', 'recipepress-reloaded' );
		$this->plural     = Options::get_option( 'rpr_ingredient_labels', array( 'singular' => 'Ingredient', 'plural' => 'Ingredients' ) )['plural']
			                ?: __( 'Ingredients', 'recipepress-reloaded' );
	}

	/**
	 * Creates and register the ingredient taxonomy attached to our recipe.
	 *
	 * @since 1.0.0
	 *
	 * @uses  register_taxonomy()
	 * @return void
	 */
	public function register_taxonomy() {

		$opts['hierarchical']          = false;
		$opts['meta_box_cb']           = false;
		$opts['public']                = true;
		$opts['query_var']             = $this->tax_name;
		$opts['show_admin_column']     = false;
		$opts['show_in_nav_menus']     = true;
		$opts['show_tag_cloud']        = true;
		$opts['show_ui']               = true;
		$opts['sort']                  = '';
		$opts['show_in_rest']          = true;
		$opts['rest_base']             = 'rpr/' . $this->sanitize_input( $this->plural );
		$opts['rest_controller_class'] = 'WP_REST_Terms_Controller';

		/**
		 * Note: If you want to ensure that your custom taxonomy behaves like a tag,
		 * you must add the option 'update_count_callback' => '_update_post_term_count'.
		 * Not doing so will result in multiple comma-separated items added at once being saved as a single value,
		 * not as separate values. This can cause undue stress when using get_the_term_list and other term display functions.
		 */
		$opts['update_count_callback']        = '_update_post_term_count';
		$opts['capabilities']['assign_terms'] = 'edit_posts';
		$opts['capabilities']['delete_terms'] = 'manage_categories';
		$opts['capabilities']['edit_terms']   = 'manage_categories';
		$opts['capabilities']['manage_terms'] = 'manage_categories';

		$opts['labels']['add_new_item']               = sprintf( __( 'Add New %1$s', 'recipepress-reloaded' ), $this->singular );
		$opts['labels']['add_or_remove_items']        = sprintf( __( 'Add or remove %1$s', 'recipepress-reloaded' ), strtolower( $this->plural ) );
		$opts['labels']['all_items']                  = $this->plural;
		$opts['labels']['choose_from_most_used']      = sprintf( __( 'Choose from most used %1$s', 'recipepress-reloaded' ), strtolower( $this->plural ) );
		$opts['labels']['edit_item']                  = sprintf( __( 'Edit %1$s', 'recipepress-reloaded' ), strtolower( $this->singular ) );
		$opts['labels']['menu_name']                  = $this->plural;
		$opts['labels']['name']                       = $this->plural;
		$opts['labels']['new_item_name']              = sprintf( __( 'New %1$s Name', 'recipepress-reloaded' ), $this->singular );
		$opts['labels']['not_found']                  = sprintf( __( 'No %1$s Found', 'recipepress-reloaded' ), $this->plural );
		$opts['labels']['parent_item']                = sprintf( __( 'Parent %1$s', 'recipepress-reloaded' ), $this->singular );
		$opts['labels']['parent_item_colon']          = sprintf( __( 'Parent %1$s', 'recipepress-reloaded' ), $this->singular );
		$opts['labels']['popular_items']              = sprintf( __( 'Popular %1$s', 'recipepress-reloaded' ), $this->plural );
		$opts['labels']['search_items']               = sprintf( __( 'Search %1$s', 'recipepress-reloaded' ), $this->plural );
		$opts['labels']['separate_items_with_commas'] = sprintf( __( 'Separate %1$s with commas', 'recipepress-reloaded' ), $this->plural );
		$opts['labels']['singular_name']              = $this->singular;
		$opts['labels']['update_item']                = sprintf( __( 'Update %1$s', 'recipepress-reloaded' ), $this->singular );
		$opts['labels']['view_item']                  = sprintf( __( 'View %1$s', 'recipepress-reloaded' ), $this->singular );
		$opts['labels']['back_to_items']              = sprintf( __( '← Back to %1$s', 'recipepress-reloaded' ), $this->plural );
		$opts['rewrite']['ep_mask']                   = EP_NONE;
		$opts['rewrite']['hierarchical']              = false;
		$opts['rewrite']['slug']                      = $this->sanitize_input( $this->cpt_plural ) . '/' . $this->sanitize_input( $this->singular );
		$opts['rewrite']['with_front']                = false;

		$opts = apply_filters( 'rpr/taxonomy/ingredient/options', $opts );

		register_taxonomy( $this->tax_name, $this->cpt_name, $opts );
	}

	/**
	 * Adds new form fields to the end of the taxonomy edit screen
	 * This methods hooks into the `{$taxonomy}_edit_form` action
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_term $tag Current taxonomy term object.
	 *
	 * @return void
	 */
	public function edit_rpr_ingredients_taxonomy_custom_fields( $tag ) {

		// Check for existing taxonomy meta for the term you're editing.
		$term_id   = is_object( $tag ) ? $tag->term_id : null; // Get the ID of the term you're editing.
		$term_meta = get_term_meta( $term_id, 'ingredient_custom_meta', true );
		?>
		<table class="form-table">
			<tbody>

			<tr class="form-field term-link-wrap">
				<th scope="row" valign="top">
					<label for="ingredient_custom_meta[link]"><?php esc_html_e( 'Link', 'recipepress-reloaded' ); ?></label>
				</th>
				<td>
					<input type="text" name="ingredient_custom_meta[link]" id="ingredient_custom_meta[link]" size="25"
						   style="width:90%;" value="<?php echo isset( $term_meta['link'] ) ? esc_attr( $term_meta['link'] ) : ''; ?>"><br/>
					<span class="description"><?php esc_html_e( 'An external link you can add to this ingredient. Useful for Amazon referral links.', 'recipepress-reloaded' );
					?></span>
				</td>
			</tr>

			<tr class="form-field term-plural_name-wrap">
				<th scope="row" valign="top">
					<label for="ingredient_custom_meta[plural_name]"><?php esc_html_e( 'Plural name', 'recipepress-reloaded' ); ?></label>
				</th>
				<td>
					<input type="text" name="ingredient_custom_meta[plural_name]" id="ingredient_custom_meta[plural_name]" size="25"
						style="width:60%;" value="<?php echo isset( $term_meta['plural_name'] ) ? esc_attr( $term_meta['plural_name'] ) : ''; ?>"><br/>
					<span class="description"><?php esc_html_e( 'The plural name of the ingredient. If empty the default pluralization "s" will be used.', 'recipepress-reloaded' ); ?></span>
				</td>
			</tr>

			<tr class="form-field term-thumbnail_image-wrap">
				<th scope="row" valign="top">
					<label for="ingredient_custom_meta[thumbnail_image][url]"><?php esc_html_e( 'Thumbnail image', 'recipepress-reloaded' ); ?></label>
				</th>
				<td>
                    <div class="ingredient_custom_meta__image"
                         style="width:250px; height:auto; aspect-ratio:1; background-color:#ccc; background-image: url(<?php echo ! empty( $term_meta['thumbnail_image']['url'] ) ? esc_url( $term_meta['thumbnail_image']['url'] ) : ''; ?>); cursor:pointer; background-size:cover; border-radius: 5px;"></div>
					<span class="description"><?php esc_html_e( 'Photo of this ingredient.', 'recipepress-reloaded' ); ?></span>
                    <input type="hidden" name="ingredient_custom_meta[thumbnail_image][url]" id="ingredient_custom_meta[thumbnail_image][url]"
                           value="<?php echo ! empty( $term_meta['thumbnail_image']['url'] ) ? esc_url( $term_meta['thumbnail_image']['url'] ) : '';?>">
					<input type="hidden" name="ingredient_custom_meta[thumbnail_image][id]" id="ingredient_custom_meta[thumbnail_image][id]"
						value="<?php echo ! empty( $term_meta['thumbnail_image']['id'] ) ? esc_attr( $term_meta['thumbnail_image']['id'] ) : ''; ?>">
				</td>
			</tr>

			<tr class="form-field term-use_in_listings-wrap">
				<th scope="row" valign="top">
					<label for="ingredient_custom_meta[use_in_listings]"><?php esc_html_e( 'Use in listings', 'recipepress-reloaded' ); ?></label>
				</th>
				<td>
					<input type="checkbox" name="ingredient_custom_meta[use_in_listings]" id="ingredient_custom_meta[use_in_listings]" size="40"
						value="1" <?php isset( $term_meta['use_in_listings'] ) ? checked( $term_meta['use_in_listings'], 1 ) : ''; ?> ><br/>
					<span class="description"><?php esc_html_e( 'Disable, if you don\'t want this ingredient to appear in ingredient listing. You probably don\'t want to have a list of all recipes using salt, sugar, etc.', 'recipepress-reloaded' ); ?></span>
				</td>
			</tr>
			</tbody>
		</table>

		<?php
		wp_nonce_field( 'rpr_ingredients', 'rpr_ingredient_taxonomy_metadata' );
	}

	/**
	 * Adds new form fields to the end of the new taxonomy screen
	 *
	 * This methods hooks into the `{$taxonomy}_add_form_fields` action
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function new_rpr_ingredients_taxonomy_custom_fields() { ?>

		<div class="form-field term-link-wrap">
			<label for="ingredient_custom_meta[link]"><?php esc_html_e( 'Link', 'recipepress-reloaded' ); ?></label>
			<input type="text" name="ingredient_custom_meta[link]" id="ingredient_custom_meta[link]" size="25" style="width:90%;" value=""><br/>
			<span class="description"><?php esc_html_e( 'An external link you can add to this ingredient. Useful for Amazon referral links.', 'recipepress-reloaded' ); ?></span>
		</div>

		<div class="form-field term-plural_name-wrap">
			<label for="ingredient_custom_meta[plural_name]"><?php esc_html_e( 'Plural name', 'recipepress-reloaded' ); ?></label>
			<input type="text" name="ingredient_custom_meta[plural_name]" id="ingredient_custom_meta[plural_name]" size="25"
				style="width:60%;" value=""><br/>
			<span class="description"><?php esc_html_e( 'The plural name of the ingredient. If empty the default pluralization "s" will be used.', 'recipepress-reloaded' ); ?></span>
		</div>

		<div class="form-field term-thumbnail_image-wrap">
			<label for="ingredient_custom_meta[thumbnail_image][url]"><?php esc_html_e( 'Thumbnail image', 'recipepress-reloaded' ); ?></label>
            <div class="ingredient_custom_meta__image" style="width:250px; height:auto; aspect-ratio:1; background-color:#ccc; cursor:pointer; background-size:cover; border-radius: 5px;"></div>
            <input type="hidden" name="ingredient_custom_meta[thumbnail_image][url]" id="ingredient_custom_meta[thumbnail_image][url]" value="">
			<input type="hidden" name="ingredient_custom_meta[thumbnail_image][id]"	id="ingredient_custom_meta[thumbnail_image][id]" value="">
			<span class="description"><?php esc_html_e( 'Photo of this ingredient.', 'recipepress-reloaded' ); ?></span>
		</div>

		<div class="form-field term-use_in_listings-wrap">
			<label for="ingredient_custom_meta[use_in_listings]"><?php esc_html_e( 'Use in listings', 'recipepress-reloaded' ); ?></label>
			<input type="checkbox" name="ingredient_custom_meta[use_in_listings]" id="ingredient_custom_meta[use_in_listings]" size="40"
				value="1" checked="checked" ><br/>
			<span class="description"><?php esc_html_e( 'Disable, if you don\'t want this ingredient to appear in ingredient listing. You probably don\'t want to have a list of all recipes using salt, sugar, etc.', 'recipepress-reloaded' ); ?></span>
		</div>

		<?php
		wp_nonce_field( 'rpr_ingredients', 'rpr_ingredient_taxonomy_metadata' );
	}

	/**
	 * Handles saving the custom meta
	 *
	 * This method uses the `create_{$taxonomy}` action and is fired after a new taxonomy term
	 * is created or edited.
	 *
	 * @since 1.0.0
	 *
	 * @param int $term_id Term ID.
	 *
	 * @return bool|int|void|\WP_Error
	 */
	public function save_rpr_ingredients_taxonomy_custom_fields( $term_id ) {

		if ( ( ! empty( $_POST['action'] ) && 'editedtag' === $_POST['action'] ) || ( ! empty( $_POST['action'] ) && 'add-tag' === $_POST['action'] ) ) { // phpcs:ignore

			check_admin_referer( 'rpr_ingredients', 'rpr_ingredient_taxonomy_metadata' );

			$term_meta   = array();
			$meta_keys   = array( 'plural_name', 'thumbnail_image', 'use_in_listings', 'link' );
			$custom_meta = ! empty( $_POST['ingredient_custom_meta'] ) ? $this->array_walker( 'sanitize_text_field', $_POST['ingredient_custom_meta'] ) : array(); // phpcs:ignore

			foreach ( $custom_meta as $key => $value ) {
				if ( in_array( $key, $meta_keys, true ) ) {
					$term_meta[ $key ] = $value;
				}
			}

			return update_term_meta( $term_id, 'ingredient_custom_meta', $term_meta );
		}
	}

	/**
	 * Remove ingredients from the Gutenberg sidebar
	 *
	 * @since 1.7.0
	 *
	 * @param \WP_REST_Response $response The response object
	 * @param \WP_Taxonomy      $taxonomy The original taxonomy object
	 * @param \WP_REST_Request  $request  Request used to generate the response
	 *
	 * @return \WP_REST_Response
	 */
	public function remove_ingredients_gutenberg( $response, $taxonomy, $request ) {

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';

		// Context is edit in the editor
		if ( 'edit' === $context && 'rpr_ingredient' === $taxonomy->name ) {
			$data_response = $response->get_data();
			$data_response['visibility']['show_ui'] = false;
			$response->set_data( $data_response );
		}

		return $response;
	}

}
