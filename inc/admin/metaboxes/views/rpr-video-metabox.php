<?php
/**
 * The video URL metabox view of the plugin.
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

$recipe_video_data = get_post_meta( $recipe->ID, 'rpr_recipe_video_data', true );
$has_link          = ( $recipe_video_data && $recipe_video_data['video_url'] ) ? 'has-link' : '';

$this->create_nonce();
?>

<div data-controller="rpr-video" class="rpr-video-url-container">
	<div class="rpr-video-input-container">
		<label class="screen-reader-text" for="rpr_recipe_video_url">
			<?php esc_html_e( 'Video URL', 'recipepress-reloaded' ); ?>
		</label>
		<input
            data-rpr-video-target="videoURL"
			type="text"	name="rpr_recipe_video_data[video_url]"
			id="rpr_recipe_video_url"
			class="rpr rpr-recipe-video-url"
			value="<?php echo '' !== $recipe_video_data ? esc_url( $recipe_video_data['video_url'] ) : ''; ?>"
			placeholder="<?php esc_attr_e( 'Video URL', 'recipepress-reloaded' ); ?>"/>
		<input
            data-rpr-video-target="videoTitle"
			type="hidden" name="rpr_recipe_video_data[video_title]"
			id="rpr_recipe_video_title"
			value="<?php echo '' !== $recipe_video_data ? esc_attr( $recipe_video_data['video_title'] ) : ''; ?>" />
		<input
            data-rpr-video-target="videoDescription"
			type="hidden" name="rpr_recipe_video_data[video_description]"
			id="rpr_recipe_video_description"
			value="<?php echo '' !== $recipe_video_data ? esc_attr( $recipe_video_data['video_description'] ) : ''; ?>" />
		<input
            data-rpr-video-target="videoDate"
			type="hidden" name="rpr_recipe_video_data[video_date]"
			id="rpr_recipe_video_date"
			value="<?php echo '' !== $recipe_video_data ? esc_attr( $recipe_video_data['video_date'] ) : ''; ?>" />
		<input
            data-rpr-video-target="videoThumbnail0"
			type="hidden" name="rpr_recipe_video_data[video_thumb][0]"
			id="rpr_recipe_video_thumb_0"
			value="<?php echo '' !== $recipe_video_data ? esc_url( $recipe_video_data['video_thumb'][0] ) : ''; ?>" />
		<input
            data-rpr-video-target="videoThumbnail1"
			type="hidden" name="rpr_recipe_video_data[video_thumb][1]"
			id="rpr_recipe_video_thumb_1"
			value="<?php echo '' !== $recipe_video_data ? esc_url( $recipe_video_data['video_thumb'][1] ) : ''; ?>" />
		<input
            data-rpr-video-target="videoThumbnail2"
			type="hidden" name="rpr_recipe_video_data[video_thumb][2]"
			id="rpr_recipe_video_thumb_2"
			value="<?php echo '' !== $recipe_video_data ? esc_url( $recipe_video_data['video_thumb'][2] ) : ''; ?>" />

        <button
            data-action="click->rpr-video#fetchVideo"
            data-rpr-video-target="fetchButton"
            class="rpr rpr-source-fetch-data dashicons dashicons-controls-play button <?php echo $has_link ? 'rpr-hidden' : ''; ?>"
            title="<?php esc_attr_e( 'Fetch Video', 'recipepress-reloaded' ); ?>"></button>
        <button
            data-action="click->rpr-video#removeVideo"
            data-rpr-video-target="removeButton"
            class="rpr rpr-source-del-data dashicons dashicons-trash button <?php echo ! $has_link ? 'rpr-hidden' : ''; ?>"
            title="<?php esc_attr_e( 'Remove Video', 'recipepress-reloaded' ); ?>"></button>

	</div>

	<div
        data-action="click->rpr-video#goToVideo"
        data-rpr-video-target="videoContainer"
        class="rpr-video-thumb-container">
		<?php if ( $recipe_video_data && '' !== $recipe_video_data['video_thumb'][1] ) : ?>
			<img src="<?php echo esc_url( $recipe_video_data['video_thumb'][1] ); ?>"
				 title="<?php echo esc_attr( $recipe_video_data['video_title'] ); ?>"
				 alt="<?php esc_attr_e( 'The recipe\'s video if there is one.', 'recipepress-reloaded' ); ?>"/>
		<?php endif; ?>
	</div>
</div>

<style>
	.rpr-video-input-container {
		display: flex;
		align-items: center;
	}
	.rpr-video-input-container input.rpr {
		flex: 0 1 100%;
	}
	.rpr-video-input-container button.rpr {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		flex: 0 0 30px;
		margin: 0 0 0 5px;
		min-height: 30px;
		padding: 0;
	}
	.rpr-video-thumb-container {
		margin: 10px 0 0 0;
		width: 100%;
		word-break: break-word;
	}
	.rpr-video-thumb-container p {
		font-size: 12px;
		line-height: 1.2;
		padding: 5px;
		background-color: #ffe9e9;
	}
	.rpr-video-thumb-container img {
		width: 100%;
	}

	@media screen and (max-width: 782px) {
		.rpr-video-input-container button.rpr {
			flex: 0 0 40px;
			margin: 0 0 0 10px;
			min-height: 40px;
		}
	}

</style>
