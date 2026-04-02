<?php
/**
 * The recipe source metabox view of the plugin.
 *
 * @link  http://tech.cbjck.de/wp/rpr
 * @since 1.0.0
 *
 * @var \WP_Post $recipe
 * @var \Recipepress\Inc\Admin\Metaboxes\Video $this
 *
 * @package    recipepress-reloaded
 * @subpackage recipepress-reloaded/admin/views
 */

$recipe_source = get_post_meta( $recipe->ID, 'rpr_recipe_source', true );
$has_link      = ! empty( $recipe_source['link'] ) ? 'has-link' : null;

$this->create_nonce();
?>

<div data-controller="rpr-source"
     data-action="rpr-link:data@window->rpr-source#getData"
     class="rpr-video-source-container">
	<label class="screen-reader-text" for="rpr_recipe_source"><?php _e( 'Recipe source', 'recipepress-reloaded' ); ?></label>
	<input
        data-rpr-source-target="linkText"
		type="text"
		name="rpr_recipe_source[name]"
		id="rpr_recipe_source"
		class="rpr rpr-recipe-source"
		title="<?php echo '' !== $recipe_source ? esc_url( $recipe_source['link'] ) : ''; ?>"
		value="<?php echo '' !== $recipe_source ? esc_attr( $recipe_source['name'] ) : ''; ?>"
		placeholder="<?php esc_attr_e( 'Recipe source', 'recipepress-reloaded' ); ?>"/>

	<input
        data-rpr-source-target="linkUrl"
		type="hidden"
		name="rpr_recipe_source[link]"
		class="rpr rpr-recipe-source-link"
		value="<?php echo '' !== $recipe_source ? esc_url( $recipe_source['link'] ) : null; ?>" />

    <input
        data-rpr-source-target="linkBehavior"
        type="hidden"
        name="rpr_recipe_source[open]"
        class="rpr rpr-recipe-source-open"
        value="<?php echo isset( $recipe_source['open'] ) ? $recipe_source['open'] : ''; ?>" />

	<button data-action="click->rpr-source#addLink"
        title="<?php echo $has_link ? esc_attr__( 'Edit Link', 'recipepress-reloaded' ) : esc_attr__( 'Add Link', 'recipepress-reloaded' ); ?>"
        class="rpr rpr-source-add-link dashicons dashicons-admin-links button <?php echo $has_link ?: ''; ?>"></button>

    <button data-action="click->rpr-source#removeLink" <?php echo $has_link ? 'style="display:block"' : 'style="display:none"'  ?>
        title="<?php esc_attr_e( 'Remove Link', 'recipepress-reloaded' ); ?>"
        class="rpr rpr-source-del-link dashicons dashicons-editor-unlink button"></button>



</div>

<style>
	.rpr-video-source-container {
		display: flex;
		align-items: center;
	}
	.rpr-video-source-container input.rpr {
		flex: 0 1 100%;
	}
	.rpr-video-source-container button.rpr {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		flex: 0 0 30px;
		margin: 0 0 0 5px;
		min-height: 30px;
		padding: 0;
	}

	@media screen and (max-width: 782px) {
		.rpr-video-source-container button.rpr {
			flex: 0 0 40px;
			margin: 0 0 0 10px;
			min-height: 40px;
		}
	}
</style>
