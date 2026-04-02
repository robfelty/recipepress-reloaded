<?php
/*
Author: wzyMedia
Author Mail: kemory@wzymedia.com
Author URL: https://wzymedia.com
Layout Name: Rpr Delicious
Version: 1.1.0
Description: The delicious layout.
*/

/**
 * The recipe post object.
 *
 * @var \WP_Post $recipe The recipe post object.
 */

use Recipepress\Inc\Core\Options;
use Recipepress\Inc\Frontend\Rating;
use Recipepress\Inc\Frontend\Template;
use Recipepress\Inc\Common\Utilities\Icons;

// Get the recipe ID.
$recipe_id  = ! empty( $GLOBALS['recipe_id'] ) ? (int) $GLOBALS['recipe_id'] : $recipe->ID;
$rating     = new Rating( 'recipepress-reloaded', '1.8.0' );
$customizer = get_option( 'rpr_template', array() );

$print_class = str_replace( ',', ' ', Options::get_option( 'rpr_recipe_template_print_area', 'rpr-recipe' ) );
$template    = new Template( $this->plugin_name, $this->version );

wp_enqueue_style( 'rpr-delicious-template-style' );
wp_enqueue_script( 'rpr-delicious-template-script' );
is_singular() && wp_enqueue_style( 'rpr-delicious-print-style' );
?>


<?php if ( $template->get_the_recipe_print_button( '' ) || $template->get_the_rpr_recipe_jump_button( '' ) ) : ?>
	<div class="rpr jump-print-container">
		<?php $template->the_rpr_recipe_jump_button( 'icon-down-open' ); ?>
	</div>
<?php endif; ?>

<?php
// Display a description of the recipe as entered in the WP post editor,
// there should always be one.
$template->the_recipe_description( $recipe_id, false );
?>

<?php do_action( 'rpr/template/description/after' ); ?>

<?php do_action( 'rpr/template/start' ); ?>

<div id="rpr-recipe" class="<?php echo esc_attr( str_replace( '.', '', $print_class ) ); ?> rpr recipe-container">

	<?php if ( has_post_thumbnail( $recipe_id ) ) : ?>
		<div class="rpr thumbnail-container" style="background-image: url(<?php echo get_the_post_thumbnail_url( $recipe_id, 'medium' ); ?>)"></div>
	<?php endif; ?>

	<div class="rpr meta-container">

		<div class="rpr title-container">
			<h2 class="rpr title"><?php echo esc_html( get_the_title( $recipe_id ) ); ?></h2>
		</div>

		<?php do_action( 'rpr_recipe_after_title' ); ?>

		<?php if ( $rating->rating_info( 'count', $recipe_id ) ) : ?>
			<div class="rpr rating-container">
				<?php $rating->the_rating( $recipe_id ); ?>
			</div>
		<?php endif; ?>

		<div class="rpr terms-container">
			<?php
				// I think it's always nice to have an overview of the taxonomies a recipe is
				// filed under at the top.
				$template->the_rpr_taxonomy_list( $recipe_id, 'icon-tags', true, ', ' );

				do_action( 'rpr_recipe_after_taxonomy_list' );
			?>
			<?php do_action( 'rpr_recipe_after_taxonomy_list' ); ?>
		</div>

		<?php if ( null !== $template->get_the_rpr_recipe_times( $recipe_id, array() ) ) : ?>
			<div class="rpr times-container">
				<?php
				// Display the recipe times bar.
				$template->the_rpr_recipe_times( $recipe_id, array( 'icon-hourglass', 'icon-fire', 'icon-clock' ) );
				?>
			</div>
			<?php do_action( 'rpr_recipe_after_times' ); ?>
		<?php endif; ?>

		<div class="rpr servings-source-container">
			<?php if ( null !== $template->get_the_rpr_recipe_servings( $recipe_id, 'icon-chart-pie' ) ) : ?>
				<div class="rpr servings-container">
					<?php $template->the_rpr_recipe_servings( $recipe_id, 'icon-chart-pie' ); ?>
				</div>
			<?php endif; ?>

			<?php if ( null !== $template->get_the_rpr_recipe_source( $recipe_id, 'icon-link' ) ) : ?>
				<div class="rpr source-container">
					<?php $template->the_rpr_recipe_source( $recipe_id, 'icon-link' ); ?>
				</div>
			<?php endif; ?>

			<?php $template->the_recipe_print_button( 'icon-print' ); ?>
		</div>
	</div>

	<?php if ( $recipe->post_excerpt && ! empty( $customizer['delicious']['excerpt'] ) && apply_filters( 'rpr_delicious_display_excerpt', true ) ) : ?>
		<div class="rpr excerpt-container">
			<?php echo esc_html( $recipe->post_excerpt ); ?>
		</div>
	<?php endif; ?>

	<div class="rpr ingredients-container">
		<?php
			// Ingredients section of the recipe.
			// First: The headline.
			$template->the_rpr_recipe_headline( __( 'Ingredients', 'recipepress-reloaded' ), 'icon-shopping-basket' );

			// Third: The ingredient list.
			$template->the_rpr_recipe_ingredients( $recipe_id, 'icon-circle', 'icon-circle-empty' );
		?>
		<?php do_action( 'rpr/template/ingredients/after' ); ?>
	</div>

	<div class="rpr instruction-container">
		<?php
			// Instructions section of the recipe.
			// First: the headline.
			$template->the_rpr_recipe_headline( __( 'Instructions', 'recipepress-reloaded' ), 'icon-book' );

			// Second: the instructions list.
			$template->the_rpr_recipe_instructions( $recipe_id, 'icon-circle' );
		?>
		<?php do_action( 'rpr/template/instructions/after' ); ?>
	</div>

	<?php if ( null !== $template->get_the_rpr_recipe_notes( $recipe_id ) ) : ?>
		<div class="rpr notes-container">
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

	<?php if ( null !== $template->get_the_rpr_recipe_nutrition( $recipe_id ) ) : ?>
		<div class="rpr nutrition-container">
			<h3><?php _e( 'Nutritional Information', 'recipepress-reloaded' ); ?></h3>
			<?php
			// Display nutritional information if available.
			$template->the_rpr_recipe_nutrition( $recipe_id );
			?>
		</div>
		<?php do_action( 'rpr_recipe_after_nutrition' ); ?>
	<?php endif; ?>

	<?php if ( isset( $customizer['delicious']['share_enable'] ) && $customizer['delicious']['share_enable'] ) : ?>
		<div class="rpr ig-share-container no-print">
			<div class="rpr-icon instagram">
				<?php Icons::the_icon( 'instagram' ); ?>
			</div>
			<div>
				<?php
					printf(
						'<h5>%s</h5>',
						! empty( $customizer['delicious']['share_heading'] )
							? $customizer['delicious']['share_heading']
							: __( 'Did You Make This Recipe?', 'recipepress-reloaded' )
					);
					printf(
						'<p>%s</p>',
						! empty( $customizer['delicious']['share_body'] )
							? $customizer['delicious']['share_body']
							: __( 'Tag us on Instagram with a photo your recipe and how it turned out', 'recipepress-reloaded' )
					);
				?>
			</div>
		</div>
	<?php endif; ?>

	<?php
	if ( $template->is_recipe_embedded() ) {
		$template->the_rpr_recipe_schema( $recipe_id );
	}
	?>

</div>

<?php do_action( 'rpr/template/end' ); ?>

<?php echo '<!-- Recipepress Reloaded ' . Recipepress\PLUGIN_VERSION . ' -->'; ?>
