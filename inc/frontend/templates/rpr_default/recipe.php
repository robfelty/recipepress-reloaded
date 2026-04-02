<?php
/*
Author: wzyMedia
Author Mail: kemory@wzymedia.com
Author URL: https://wzymedia.com
Layout Name: Rpr Default
Version: 1.1.0
Description: The default layout.
*/

/**
 * The recipe post object.
 *
 * @var \WP_Post $recipe The recipe post object.
 */

use Recipepress\Inc\Frontend\Template;

// Get the recipe ID.
$recipe_id  = ! empty( $GLOBALS['recipe_id'] ) ? (int) $GLOBALS['recipe_id'] : $recipe->ID;
$template   = new Template( $this->plugin_name, $this->version );
$customizer = get_option( 'rpr_template', array() ); // Get WP Customizer settings.

wp_enqueue_style( 'rpr-default-template-style' );
wp_enqueue_script( 'rpr-default-template-script' );
is_singular() && wp_enqueue_style( 'rpr-default-print-style' );
?>

<?php do_action( 'rpr/template/start' ); ?>

<div id="rpr-recipe" class="rpr-recipe-container">

	<?php if ( $template->is_recipe_embedded() ) : ?>
		<h2 class="rpr_title">
			<?php
				// Displaying the recipe title normally handled by the theme as post_title().
				// However, if the recipe is embedded, we need to do it here.
				echo esc_html( get_the_title( $recipe_id ) );
			?>
		</h2>
		<?php do_action( 'rpr/template/embedded_title/after' ); ?>
	<?php endif; ?>

	<div class="rpr-terms-container">
		<?php
			// I think it's always nice to have an overview of the taxonomies a recipe is
			// filed under at the top.
			$template->the_rpr_taxonomy_list( $recipe_id, 'tag', true, ', ' );
		?>
		<?php do_action( 'rpr/template/taxonomy_list/after' ); ?>
	</div>

	<?php if ( $template->get_the_recipe_print_button( '' ) || $template->get_the_rpr_recipe_jump_button( '' ) ) : ?>
		<div class="rpr-jump-print-container">
			<?php
				if ( isset( $customizer['default']['jump'] ) && $customizer['default']['jump'] ) {
					$template->the_rpr_recipe_jump_button( 'down-open', 'ingredients' );
				}
				if ( isset( $customizer['default']['print'] ) && $customizer['default']['print'] ) {
					$template->the_recipe_print_button( 'print', 'rpr_recipe' );
				}
			?>
		</div>
	<?php endif; ?>

	<div class="rpr-description-container">
		<?php
			// Display a description of the recipe as entered in the WP post editor,
			// there should always be one.
			$template->the_recipe_description( $recipe_id );
		?>
	</div>
	<?php do_action( 'rpr/template/description/after' ); ?>

	<?php if ( null !== $template->get_the_rpr_recipe_source( $recipe_id, 'link' ) ) : ?>
		<div class="rpr-source-container">
			<?php
				// Display source/citation information if available.
				$template->the_rpr_recipe_source( $recipe_id, 'link' );
			?>
		</div>
		<?php do_action( 'rpr/template/source/after' ); ?>
	<?php endif; ?>

    <?php if ( $template->get_rpr_equipment_list( $recipe_id ) ) : ?>
        <div class="rpr-equipment-container">

            <?php
            $template->the_rpr_recipe_headline( __( 'Equipment', 'recipepress-reloaded' ), 'attach' );
            $template->the_rpr_equipment_list( $recipe_id );
            ?>
        </div>
        <?php do_action( 'rpr/template/equipment/after' ); ?>
    <?php endif; ?>

	<div class="rpr-ingredients-container">
		<?php
			// Ingredients section of the recipe.
			// First: The headline.
			$template->the_rpr_recipe_headline( __( 'Ingredients', 'recipepress-reloaded' ), 'shopping-basket' );

			// Third: The ingredient list.
			$template->the_rpr_recipe_ingredients( $recipe_id, 'circle', '' );
		?>
		<?php do_action( 'rpr/template/ingredients/after' ); ?>
	</div>

	<?php if ( null !== $template->get_the_rpr_recipe_times( $recipe_id, array() ) ) : ?>
		<div class="rpr-times-container">
			<?php
                // Second: Serving size/yield.
                if ( null !== $template->get_the_rpr_recipe_servings( $recipe_id, 'chart-pie' ) ) {
                    $template->the_rpr_recipe_servings( $recipe_id, 'chart-pie' );
                }
				// Display the recipe times bar.
				$template->the_rpr_recipe_times( $recipe_id, array( 'hourglass', 'fire', 'clock' ) );
			?>
		</div>
		<?php do_action( 'rpr/template/times/after' ); ?>
	<?php endif; ?>

	<div class="rpr-instruction-container">
		<?php
			// Instructions section of the recipe.
			// First: the headline.
			$template->the_rpr_recipe_headline( __( 'Instructions', 'recipepress-reloaded' ), 'book' );

			// Second: the instructions list.
			$template->the_rpr_recipe_instructions( $recipe_id, 'circle' );
		?>
		<?php do_action( 'rpr/template/instructions/after' ); ?>
	</div>

	<?php if ( null !== $template->get_the_rpr_recipe_notes( $recipe_id ) ) : ?>
		<div class="rpr-notes-container">
			<?php
				// Notes section of the recipe
				// First: the headline.
				$template->the_rpr_recipe_headline( __( 'Notes', 'recipepress-reloaded' ), 'attach' );

				// Second: the actual notes.
				$template->the_rpr_recipe_notes( $recipe_id );
			?>
		</div>
		<?php do_action( 'rpr/template/notes/after' ); ?>
	<?php endif; ?>

	<?php if ( null !== $template->get_the_rpr_recipe_nutrition( $recipe_id ) ) : ?>
		<div class="rpr-nutrition-container">
			<?php
			// Display nutritional information if available.
			$template->the_rpr_recipe_nutrition( $recipe_id );
			?>
		</div>
		<?php do_action( 'rpr/template/nutrition/after' ); ?>
	<?php endif; ?>

	<?php
	if ( $template->is_recipe_embedded() ) {
		$template->the_rpr_recipe_schema( $recipe_id );
	}
	?>

</div>

<?php do_action( 'rpr/template/end' ); ?>

<?php echo '<!-- Recipepress Reloaded ' . Recipepress\PLUGIN_VERSION . ' -->'; ?>
