<?php
/*
Author: wzyMedia
Author Mail: kemory@wzymedia.com
Author URL: https://wzymedia.com
Layout Name: Rpr Default
Version: 1.0.0
Description: The default layout.
*/

use Recipepress\Inc\Core\Options;
use Recipepress\Inc\Frontend\Template;

// Get the recipe ID.
$recipe_id = ( isset( $GLOBALS['recipe_id'] ) && '' !== $GLOBALS['recipe_id'] ) ? $GLOBALS['recipe_id'] : get_the_ID();

$print_class = str_replace( ',', ' ', Options::get_option( 'rpr_recipe_template_print_area', 'rpr-recipe' ) );
$template    = new Template( $this->plugin_name, $this->version );
?>

<?php do_action( 'rpr/template/start' ); ?>

<div class="<?php echo esc_attr( str_replace( '.', '', $print_class ) ); ?> rpr-recipe-container">

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

	<div class="rpr-excerpt-container">
		<?php
		echo get_the_post_thumbnail( $recipe_id, 'full', array( 'class' => 'rpr-excerpt-thumbnail size-full' ) );
		// Display a description of the recipe as entered in the WP post editor,
		// there should always be one.
		$template->the_recipe_excerpt( $recipe_id );
		?>
		<?php do_action( 'rpr_recipe_after_excerpt' ); ?>
	</div>

	<?php
		if ( $template->is_recipe_embedded() ) {
			$template->the_rpr_recipe_schema( $recipe_id );
		}
	?>

</div>

<?php do_action( 'rpr/template/end' ); ?>
