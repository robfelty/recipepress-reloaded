<?php
/*
Author: wzyMedia
Author Mail: kemory@wzymedia.com
Author URL: https://wzymedia.com
Layout Name: Rpr Healthy
Version: 1.0.0
Description: A template for the HealthierSteps.com
*/

use Recipepress\Inc\Core\Options;
use Recipepress\Inc\Frontend\Template;

// Get the recipe ID.
$recipe_id   = ! empty( $GLOBALS['recipe_id'] ) ? (int) $GLOBALS['recipe_id'] : get_the_ID();
$print_class = str_replace( ',', ' ', Options::get_option( 'rpr_recipe_template_print_area', 'rpr-recipe' ) );
$template    = new Template( $this->plugin_name, $this->version );
$data        = $template->get_the_recipe_meta( $recipe_id );

wp_enqueue_style( 'rpr-healthy-style' );
wp_enqueue_script( 'rpr-healthy-script' );
is_singular() && wp_enqueue_style( 'rpr-healthy-print' );
?>

<?php do_action( 'rpr/template/start' ); ?>

<div id="rpr-recipe" class="rpr-recipe-container">

	<?php if ( $template->is_recipe_embedded() ) : ?>
		<h3 class="rpr-title">
			<a href="<?php echo esc_url( get_the_permalink( $recipe_id ) ); ?>">
				<?php
				// Displaying the recipe title is normally done by the theme as post_title().
				// However, if the recipe is embedded, we need to do it here.
				echo esc_html( get_the_title( $recipe_id ) );
				?>
			</a>
		</h3>
		<?php do_action( 'rpr/template/embedded_title/after' ); ?>
	<?php endif; ?>

	<div class="rpr-description-container">
		<?php
			// Display a description of the recipe as entered in the WP post editor,
			// there should always be one.
			$template->the_recipe_description( $recipe_id );
		?>
		<?php do_action( 'rpr/template/description/after' ); ?>
	</div>

	<div class="rpr-info-container">
		<div class="rpr-terms-container">
			<h3 class="rpr-info-heading"><?php _e( 'Categories', 'recipepress-reloaded' ); ?></h3>
			<ul class="rpr-terms">
				<?php $template->the_rpr_taxonomy_terms( $recipe_id, 'category', true, ', ' ); ?>
				<?php $template->the_rpr_taxonomy_terms( $recipe_id, 'course', true, ', ' ); ?>
				<?php $template->the_rpr_taxonomy_terms( $recipe_id, 'cuisine', true, ', ' ); ?>
				<?php $template->the_rpr_taxonomy_terms( $recipe_id, 'season', true, ', ' ); ?>
			</ul>
		</div>
		<?php if ( null !== $template->get_the_rpr_recipe_nutrition( $recipe_id ) ) : ?>
			<div class="rpr-nutrition-container">
				<div class="heading">
					<h3 class="rpr-info-heading"><?php _e( 'Nutrition', 'recipepress-reloaded' ); ?></h3>
					<small>  (<?php echo $template->get_the_rpr_recipe_nutrition_per( $data, '', 'span' ); ?>)</small>
				</div>
				<?php

				$template->the_rpr_recipe_nutrition( $recipe_id, '', false );
				?>
			</div>
		<?php endif; ?>
		<div class="rpr-times-container">
			<h3 class="rpr-info-heading"><?php _e( 'Cook Time', 'recipepress-reloaded' ); ?></h3>
			<?php $template->the_rpr_recipe_times( $recipe_id, array( 'icon-hourglass', 'icon-fire', 'icon-clock' ) ); ?>
			<div class="rpr-servings-container">
				<?php $template->the_rpr_recipe_servings( $recipe_id, 'icon-chart-pie' ); ?>
			</div>
		</div>

	</div>

	<div class="rpr-ingredients-container">
		<?php
			$template->the_rpr_recipe_headline( __( 'Ingredients', 'recipepress-reloaded' ), 'icon-shopping-basket' );

			$template->the_rpr_recipe_ingredients( $recipe_id, 'icon-circle', 'icon-circle-empty' );
		?>
		<?php do_action( 'rpr/template/ingredients/after' ); ?>
	</div>

	<div class="rpr-instruction-container">
		<?php
			$template->the_rpr_recipe_headline( __( 'Instructions', 'recipepress-reloaded' ), 'icon-book' );

			$template->the_rpr_recipe_instructions( $recipe_id, 'icon-circle' );
		?>
		<?php do_action( 'rpr/template/instructions/after' ); ?>
	</div>

	<?php if ( null !== $template->get_the_rpr_recipe_notes( $recipe_id ) ) : ?>
		<div class="rpr-notes-container">
			<?php
				$template->the_rpr_recipe_headline( __( 'Notes', 'recipepress-reloaded' ), 'icon-attach' );

				$template->the_rpr_recipe_notes( $recipe_id );
			?>
		</div>
		<?php do_action( 'rpr/template/notes/after' ); ?>
	<?php endif; ?>

	<?php
		if ( $template->is_recipe_embedded() ) {
			$template->the_rpr_recipe_schema( $recipe_id );
		}
	?>

</div>

<?php do_action( 'rpr/template/end' ); ?>
