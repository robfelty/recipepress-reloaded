<?php
/**
 * The ingredient metabox view of the plugin.
 *
 * @since 1.0.0
 *
 * @var \WP_Post                                     $recipe
 * @var \Recipepress\Inc\Admin\Metaboxes\Information $this
 *
 * @package    recipepress-reloaded
 */

use Recipepress\Inc\Core\Options;

$servings      = get_post_meta( $recipe->ID, 'rpr_recipe_servings', true );
$servings_type = get_post_meta( $recipe->ID, 'rpr_recipe_servings_type', true );
$prep_time     = get_post_meta( $recipe->ID, 'rpr_recipe_prep_time', true );
$cook_time     = get_post_meta( $recipe->ID, 'rpr_recipe_cook_time', true );
$passive_time  = get_post_meta( $recipe->ID, 'rpr_recipe_passive_time', true );

$this->create_nonce();
?>

<script type="text/javascript">
  window.rpr = window.rpr || {}
  rpr.rprServingUnitsList = <?php echo wp_json_encode( explode( ',', Options::get_option( 'rpr_serving_unit_list', '' ) ) ); ?>;
</script>

<div
    class="rpr_general_information_metabox"
    data-controller="rpr-information"
    data-rpr-information-use-serving-units-list-value="<?php echo Options::get_option( 'rpr_use_serving_unit_list' ) ? 'true' : 'false'; ?>"
>
	<div class="recipe_details_row rpr_servings">
		<label
            for="rpr_recipe_servings"
        >
            <?php esc_attr_e( 'Servings/Yield', 'recipepress-reloaded' ); ?>:
        </label>
		<input
            type="number"
            min="1"
            name="rpr_recipe_servings"
            id="rpr_recipe_servings"
			value="<?php echo esc_attr( $servings ); ?>" placeholder="4"
        />
        <input
            type="text"
            name="rpr_recipe_servings_type"
            id="rpr_recipe_servings_type"
            value="<?php echo esc_attr( $servings_type ); ?>"
            placeholder="<?php esc_attr_e( 'Portions', 'recipepress-reloaded' ); ?> "
            data-action="focusin->rpr-information#fetchServingUnitsList focusout->rpr-information#destroyServingUnitsList"
        />
		<div
            class="recipe-general-form-notes"
            id="rpr_recipe_servings_note"
        >
			<?php esc_html_e( 'e.g. 2 servings, 3 loafs', 'recipepress-reloaded' ); ?>
		</div>
	</div>

	<div class="rpr_times">
		<div class="recipe_details_row">
			<label
                for="rpr_recipe_prep_time"
            >
                <?php esc_attr_e( 'Prep Time', 'recipepress-reloaded' ); ?>:
            </label>
			<input
                type="number"
                min="0"
                name="rpr_recipe_prep_time"
                class="rpr_time"
                id="rpr_recipe_prep_time"
				value="<?php echo esc_attr( $prep_time ); ?>"
                placeholder="10"
            />
			<span class="recipe-general-form-notes">
                <?php esc_html_e( 'minutes', 'recipepress-reloaded' ); ?>
            </span>
		</div>
		<div class="recipe_details_row">
			<label
                for="rpr_recipe_cook_time"
            >
                <?php esc_attr_e( 'Cook Time', 'recipepress-reloaded' ); ?>:
            </label>
			<input
                type="number"
                min="0"
                name="rpr_recipe_cook_time"
                class="rpr_time"
                id="rpr_recipe_cook_time"
				value="<?php echo esc_attr( $cook_time ); ?>"
                placeholder="10"
            />
			<span class="recipe-general-form-notes">
                <?php esc_html_e( 'minutes', 'recipepress-reloaded' ); ?>
            </span>
		</div>
		<div class="recipe_details_row">
			<label
                for="rpr_recipe_passive_time"
            >
                <?php esc_attr_e( 'Passive Time', 'recipepress-reloaded' ); ?>:
            </label>
			<input
                type="number"
                min="0"
                name="rpr_recipe_passive_time"
                class="rpr_time"
                id="rpr_recipe_passive_time"
				value="<?php echo esc_attr( $passive_time ); ?>"
                placeholder="10"
            />
			<span class="recipe-general-form-notes">
                <?php esc_html_e( 'minutes', 'recipepress-reloaded' ); ?>
            </span>
		</div>
	</div>

    <?php do_action( 'rpr/metabox/information', $recipe, $this ); ?>
</div>
