<?php
/**
 * The nutritional informational metabox view of the plugin.
 *
 * @since 1.0.0
 *
 * @var \WP_Post $recipe
 * @var \Recipepress\Inc\Admin\Metaboxes\Nutrition $this
 *
 * @package Recipepress
 */

use Recipepress\Inc\Core\Options;
use Recipepress\Inc\Admin\Metaboxes\Nutrition;

$this->create_nonce();

$calorific_value = get_post_meta( $recipe->ID, 'rpr_recipe_calorific_value', true );
$protein         = get_post_meta( $recipe->ID, 'rpr_recipe_protein', true );
$fat             = get_post_meta( $recipe->ID, 'rpr_recipe_fat', true );
$carbohydrate    = get_post_meta( $recipe->ID, 'rpr_recipe_carbohydrate', true );
$per             = get_post_meta( $recipe->ID, 'rpr_recipe_nutrition_per', true );

$nutrition_fields = array_map(
    function( $item ) use ( $recipe ) {
        foreach( $item as $k => $v ) {
	        return array(
		        $k => array( $v => get_post_meta( $recipe->ID, $v, true ) )
	        );
        }
    },
    $this->get_nutrition_fields( 'additional' )
);

$edamam_keys = array(
    'appId'  => Options::get_option( 'rpr_edamam_app_id' ),
    'appKey' => Options::get_option( 'rpr_edamam_app_key' ),
);
?>

<div data-controller="rpr-nutrition"
     data-rpr-nutrition-edamam-keys-value='<?php echo wp_json_encode( $edamam_keys ); ?>'
     class="rpr_nutrition_metabox">
	<div class="recipe_details_row">
		<div class="rpr_nutrition_row">
			<label for="rpr_recipe_calorific_value"><?php esc_attr_e( 'Calories', 'recipepress-reloaded' ); ?>:</label>
			<input data-rpr-nutrition-target="calories"
                   data-action="change->rpr-nutrition#calculateCalories"
                   type="number" min="0" step="any" name="rpr_recipe_calorific_value" id="rpr_recipe_calorific_value"
                   value="<?php echo esc_attr( $calorific_value ); ?>" placeholder="0"/>
			<span class="recipe-general-form-notes"><?php esc_attr_e( 'kcal', 'recipepress-reloaded' ); ?></span>
		</div>
		<div class="rpr_nutrition_row">
			<label for="rpr_recipe_calorific_value_kj"><?php esc_attr_e( 'Joules', 'recipepress-reloaded' ); ?>:</label>
			<input data-rpr-nutrition-target="joules"
                   data-action="change->rpr-nutrition#calculateCalories"
                   type="number" min="0" step="any" name="rpr_recipe_calorific_value_kj" id="rpr_recipe_calorific_value_kj"
                   value="<?php echo $calorific_value ? esc_attr( round( 4.18 * (int) $calorific_value ) ) : ''; ?>"
                   placeholder="0"/>
			<span class="recipe-general-form-notes"><?php esc_attr_e( 'kJ', 'recipepress-reloaded' ); ?></span>
		</div>
		<div class="rpr_nutrition_row">
			<label for="rpr_recipe_nutrition_per"><?php esc_attr_e( 'Per', 'recipepress-reloaded' ); ?>:</label>
			<select data-rpr-nutrition-target="servings" name="rpr_recipe_nutrition_per" id="rpr_recipe_nutrition_per">
				<option value="per_serving" <?php selected( $per, 'per_serving' ); ?>>
					<?php esc_attr_e( 'serving', 'recipepress-reloaded' ); ?>
				</option>
				<option value="per_recipe" <?php selected( $per, 'per_recipe' ); ?>>
					<?php esc_attr_e( 'recipe', 'recipepress-reloaded' ); ?>
				</option>
				<option value="per_portion" <?php selected( $per, 'per_portion' ); ?>>
					<?php esc_attr_e( 'portion', 'recipepress-reloaded' ); ?>
				</option>
				<option value="per_100g" <?php selected( $per, 'per_100g' ); ?>>
					<?php esc_attr_e( '100g', 'recipepress-reloaded' ); ?>
				</option>
			</select>
            <span class="recipe-general-form-notes"><?php esc_attr_e( 'quantity', 'recipepress-reloaded' ); ?></span>
		</div>
	</div>

	<div class="recipe_details_row">
		<div class="rpr_nutrition_row rpr_protein">
			<label for="rpr_recipe_protein"><?php esc_attr_e( 'Protein', 'recipepress-reloaded' ); ?>:</label>
			<input data-rpr-nutrition-target="protein"
                   type="number" min="0" step="any" name="rpr_recipe_protein" id="rpr_recipe_protein"
                   value="<?php echo esc_attr( $protein ); ?>" placeholder="0"/>
			<span class="recipe-general-form-notes"><?php esc_attr_e( 'grams', 'recipepress-reloaded' ); ?></span>
		</div>
		<div class="rpr_nutrition_row rpr_fat">
			<label for="rpr_recipe_fat"><?php esc_attr_e( 'Fat', 'recipepress-reloaded' ); ?>:</label>
			<input data-rpr-nutrition-target="fat"
                   type="number" min="0" step="any" name="rpr_recipe_fat" id="rpr_recipe_fat"
                   value="<?php echo esc_attr( $fat ); ?>" placeholder="0"/>
			<span class="recipe-general-form-notes"><?php esc_attr_e( 'grams', 'recipepress-reloaded' ); ?></span>
		</div>
		<div class="rpr_nutrition_row rpr_carbohydrate">
			<label for="rpr_recipe_carbohydrate"><?php esc_attr_e( 'Carbs', 'recipepress-reloaded' ); ?>:</label>
			<input data-rpr-nutrition-target="carbohydrate"
                   type="number" min="0" step="any" name="rpr_recipe_carbohydrate" id="rpr_recipe_carbohydrate"
                   value="<?php echo esc_attr( $carbohydrate ); ?>" placeholder="0"/>
			<span class="recipe-general-form-notes"><?php esc_attr_e( 'grams', 'recipepress-reloaded' ); ?></span>
		</div>
	</div>

    <div class="recipe_details_row additional_nutrition" style="display: none;">
		<?php foreach( $nutrition_fields as $field ) { ?>
			<?php foreach( $field as $k => $v ) { ?>
				<?php if( $k ) { ?>
                    <div class="rpr_nutrition_row">
                        <label for="<?php echo array_keys( $v )[0] ?>"><?php echo esc_attr( Nutrition::$additional_nutrition_keys[ $k ] ); ?>:</label>
                        <input data-rpr-nutrition-target="<?php echo array_keys( $v )[0] ?>"
                               type="number" min="0" step="any" name="<?php echo array_keys( $v )[0] ?>" id="<?php echo array_keys( $v )[0] ?>"
                               value="<?php echo esc_attr( array_values( $v )[0] ); ?>" placeholder="0"/>
                        <span class="recipe-general-form-notes"><?php esc_attr_e( 'grams', 'recipepress-reloaded' ); ?></span>
                    </div>
				<?php } ?>
			<?php } ?>
		<?php } ?>
    </div>

    <?php if( $edamam_keys['appId'] && $edamam_keys['appKey'] ) { ?>
        <div class="recipe_details_row" style="margin-top: 1rem;">
            <button data-action="click->rpr-nutrition#fetchNutrition" class="button wide">
                <?php _e( 'Fetch Nutrition Data', 'recipepress-reloaded'); ?>
            </button>
        </div>
    <?php } ?>

    <?php if( Options::get_option( 'rpr_additional_nutrition' ) ) { ?>
        <div class="recipe_expand_row">
            <span class="hr"></span>
            <button type="button"
                    data-action="click->rpr-nutrition#expandNutrition"
                    class="rpr-expand-nutrition"
                    aria-disabled="false"
                    aria-describedby="rpr_nutrition_metabox-expand-button-description"
            >
                <span class="screen-reader-text">More</span>
                <span class="dashicons dashicons-arrow-down-alt2 expand-indicator" aria-hidden="true"></span>
            </button>
            <span class="hr"></span>

            <span class="hidden" id="rpr_nutrition_metabox-expand-button-description">
            <?php _e( 'Expand the nutrition box', 'recipepress-reloaded'); ?>
        </span>
        </div>
    <?php } ?>

    <?php do_action( 'rpr/metabox/nutrition', $recipe, $this ); ?>
</div>
