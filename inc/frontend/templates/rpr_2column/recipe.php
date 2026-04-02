<?php
/*
Author: wzyMedia
Author Mail: kemory@wzymedia.com
Author URL: https://wzymedia.com
Layout Name: Rpr 2Column
Version: 1.1.0
Description: The 2 column layout.
*/

/**
 * The recipe post object.
 *
 * @var \WP_Post $recipe The recipe post object.
 */

use Recipepress as NS;
use Recipepress\Inc\Frontend\Template;

// Get the recipe ID.
$recipe_id  = ! empty( $GLOBALS['recipe_id'] ) ? (int) $GLOBALS['recipe_id'] : $recipe->ID;
$template   = new Template( $this->plugin_name, $this->version );
$taxonomies = $template->get_custom_taxonomies();
?>

<?php do_action( 'rpr/template/start' ); ?>

<div id="rpr-recipe" class="rpr-recipe-container">

	<?php if ( $template->is_recipe_embedded() ) : ?>
		<h2 class="rpr_title">
			<?php
				// Displaying the recipe title is normally done by the theme as post_title().
				// However, if the recipe is embedded, we need to do it here.
				echo esc_html( get_the_title( $recipe_id ) );
			?>
		</h2>
		<?php do_action( 'rpr/template/embedded_title/after' ); ?>
	<?php endif; ?>

	<?php if ( $template->get_the_rpr_recipe_jump_button( '' ) ) : ?>
		<div class="rpr-jump-container">
			<?php $template->the_rpr_recipe_jump_button( 'icon-down-open', 'ingredients' ); ?>
		</div>
	<?php endif; ?>

	<div class="rpr-description-container no-print">
		<?php
			// Display a description of the recipe as entered in the WP post editor,
			// there should always be one.
			$template->the_recipe_description( $recipe_id );
			//is_single() ? $template->rating->the_rating( $recipe_id ) : null;
		?>
		<?php do_action( 'rpr/template/description/after' ); ?>

		<?php if ( $template->get_the_rpr_recipe_source( $recipe_id, 'icon-link' ) ) : ?>
			<div class="rpr-source-container">
				<?php
				// Display source/citation information if available.
				// $template->the_rpr_recipe_source( $recipe_id, 'icon-link' );
				?>
			</div>
			<?php do_action( 'rpr/template/source/after' ); ?>
		<?php endif; ?>
	</div>

	<div class="rpr-columns-container">

		<div class="rpr-column-1">
			<div class="rpr-ingredients-container">
				<?php
				// Ingredients section of the recipe.
				// First: The headline.
				$template->the_rpr_recipe_headline( __( 'Ingredients', 'recipepress-reloaded' ), 'icon-shopping-basket' );

				// Third: The ingredient list.
				$template->the_rpr_recipe_ingredients( $recipe_id, 'icon-circle', 'icon-circle-empty' );
				?>
				<?php do_action( 'rpr/template/ingredients/after' ); ?>
			</div>
		</div>

		<div class="rpr-column-2">
			<div class="rpr-instruction-container">
				<?php
				// Instructions section of the recipe.
				// First: the headline.
				$template->the_rpr_recipe_headline( __( 'Instructions', 'recipepress-reloaded' ), 'icon-book' );

				// Second: the instructions list.
				$template->the_rpr_recipe_instructions( $recipe_id, 'icon-circle' );
				?>
				<?php do_action( 'rpr/template/instructions/after' ); ?>
			</div>

			<?php if ( $template->get_the_recipe_print_button( '' ) ) : ?>
				<div class="rpr-print-container">
					<?php $template->the_recipe_print_button( 'icon-print', 'rpr_recipe' ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

    <div class="rpr-columns-container">

        <?php if ( null !== $template->get_the_rpr_recipe_notes( $recipe_id ) ) : ?>
            <div class="rpr-notes-container">
                <?php
                    // Notes section of the recipe
                    // First: the headline.
                    $template->the_rpr_recipe_headline( __( 'Notes', 'recipepress-reloaded' ), 'icon-attach' );

                    // Second: the actual notes.
                    $template->the_rpr_recipe_notes( $recipe_id );
                ?>
            </div>
            <?php do_action( 'rpr/template/notes/after' ); ?>
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

    </div>

	<div class="rpr-columns-container-2">
		<div class="rpr-terms-container">
			<?php
			foreach ( $taxonomies as $custom_tax ) {

				if ( $template->get_the_rpr_taxonomy_terms( $recipe_id, $custom_tax['tax_settings']['settings_key'], false, '/' ) ) {

					$template->the_rpr_recipe_headline( $custom_tax['tax_settings']['labels']['singular'], 'icon-tags' );
					// I think it's always nice to have an overview of the taxonomies a recipe is
					// filed under at the top.
					$template->the_rpr_taxonomy_terms( $recipe_id, $custom_tax['tax_settings']['settings_key'], false, ', ', '', true, 'p' );
				}
			}
			?>
			<?php do_action( 'rpr/template/taxonomies/after' ); ?>
		</div>

		<?php if ( null !== $template->get_the_rpr_recipe_nutrition( $recipe_id ) ) : ?>
			<div class="rpr-nutrition-container">
				<?php
				$template->the_rpr_recipe_headline( __( 'Nutrition', 'recipepress-reloaded' ), 'icon-fast-food' );
				// Display nutritional information if available.
				$template->the_rpr_recipe_nutrition( $recipe_id, '', false );
				?>

				<?php if ( null !== $template->get_the_rpr_recipe_servings( $recipe_id, 'icon-chart-pie' ) ) : ?>
                    <div class="rpr-servings-container">
						<?php
						$template->the_rpr_recipe_headline( __( 'Servings', 'recipepress-reloaded' ), 'icon-chart-pie' );
						$template->the_rpr_recipe_servings( $recipe_id, '' );
						?>
                    </div>
				<?php endif; ?>
			</div>
			<?php do_action( 'rpr/template/nutrition/after' ); ?>
		<?php endif; ?>

		<?php if ( null !== $template->get_the_rpr_recipe_times( $recipe_id, array() ) ) : ?>
			<div class="rpr-times-container">
				<?php
				$template->the_rpr_recipe_headline( __( 'Time', 'recipepress-reloaded' ), 'icon-clock' );
				// Display the recipe times bar.
				$template->the_rpr_recipe_times( $recipe_id, array() );
				?>
			</div>
			<?php do_action( 'rpr/template/times/after' ); ?>
		<?php endif; ?>


	</div>

	<?php
		if ( $template->is_recipe_embedded() ) {
			$template->the_rpr_recipe_schema( $recipe_id );
		}
	?>

</div>

<?php do_action( 'rpr/template/end' ); ?>

<?php echo '<!-- Recipepress Reloaded ' . NS\PLUGIN_VERSION . ' -->'; ?>
