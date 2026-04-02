<?php
/**
 * Setting page/modal of the Recipepress author box extension
 *
 * @package Recipepress
 */

?>

<div id="<?php echo esc_attr( $this->id ); ?>" class="settings-page wrap modal slide" aria-hidden="true">
	<div class="modal__overlay" tabindex="-1" data-micromodal-close>
		<div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="settings-page-wrap-title">
			<header class="modal__header">
				<h2 id="settings-page-wrap-title" class="modal__title">
					<?php printf( '%s %s', esc_html( $this->title ), esc_html__( 'Settings', 'recipepress-reloaded' ) ); ?>
				</h2>
				<button class="modal__close" aria-label="Close modal" data-micromodal-close></button>
			</header>

			<div id="settings-page-wrap-content" class="modal__content">
				<form method="post" action="options.php">
					<?php settings_fields( 'rpr-favorites' ); ?>
					<?php do_settings_sections( 'rpr-favorites' ); ?>
					<div class="form-table">
						<div>
							<p><?php _e( 'Create a new page then add the [rpr-favorites] shortcode to it. Then enter its URL in the field below.', 'recipepress-reloaded' ); ?></p>
						</div>

						<div>
							<label for="favorites-page-url"><?php esc_html_e( 'Favorites page URL', 'recipepress-reloaded' ); ?></label>
							<div>
								<input type="text"
									name="rpr_favorites_options[favorites_page_url]"
									id="favorites-page-url"
									class="regular-text"
									value="<?php echo esc_attr( $this->get_setting( 'favorites_page_url' ) ); ?>"
								/>
							</div>
						</div>
					</div>
					<footer class="modal__footer">
						<?php submit_button(); ?>
					</footer>
				</form>
			</div>

		</div>
	</div>
</div>

<style>
	.rpr-settings-text-group {
		display: flex;
		flex-wrap: wrap;
		justify-content: start;
	}

	.rpr-settings-text-wrapper .minicolors {
		width: 50px;
	}

	.rpr-settings-text-group input {
		width: 100%;
	}

	.rpr-settings-text-wrapper:nth-child(2) {
		flex: 0 1 66%;
	}

	.rpr-settings-text-wrapper:nth-child(1) {
		flex: 0 1 30%;
		margin-right: 10px;
	}

</style>
