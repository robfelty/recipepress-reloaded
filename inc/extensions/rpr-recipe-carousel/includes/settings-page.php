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
					<?php settings_fields( 'rpr-recipe-carousel' ); ?>
					<?php do_settings_sections( 'rpr-recipe-carousel' ); ?>
					<div class="form-table">

						<div>
							<label for="author-box-title"><?php esc_html_e( 'Author box title', 'recipepress-reloaded' ); ?></label>
							<div>
								<input type="text"
									name="rpr_recipe_carousel_options[author_box_title]"
									id="author-box-title"
									class="regular-text"
									value="<?php echo esc_attr( $this->get_setting( 'author_box_title' ) ); ?>"
								/>
							</div>
						</div>

						<div>
							<div>
								<p>
									<?php echo 'Visit your user <a href="' . esc_url( get_edit_user_link() ) . '">profile page</a> to add your social media account information'; ?>
								</p>
							</div>
						</div>

						<div>
							<label for="facebook-color"><?php esc_html_e( 'Facebook', 'recipepress-reloaded' ); ?></label>
							<div>
								<div class="rpr-settings-text-group">
									<div class="rpr-settings-text-wrapper">
										<input type="text"
											name="rpr_recipe_carousel_options[facebook_color]"
											id="facebook-color"
											class="rpr-settings-text rpr-color-select"
											value="<?php echo esc_attr( $this->get_setting( 'facebook_color', '#3B5998' ) ); ?>"
										/>
									</div>
								</div>
							</div>
						</div>

						<div>
							<label for="twitter-color"><?php esc_html_e( 'Twitter', 'recipepress-reloaded' ); ?></label>
							<div>
								<div class="rpr-settings-text-group">
									<div class="rpr-settings-text-wrapper">
										<input type="text"
											name="rpr_recipe_carousel_options[twitter_color]"
											id="twitter-color"
											class="rpr-settings-text rpr-color-select"
											value="<?php echo esc_attr( $this->get_setting( 'twitter_color', '#4099FF' ) ); ?>"
										/>
									</div>
								</div>
							</div>
						</div>

						<div>
							<label for="pinterest-color"><?php esc_html_e( 'Pinterest', 'recipepress-reloaded' ); ?></label>
							<div>
								<div class="rpr-settings-text-group">
									<div class="rpr-settings-text-wrapper">
										<input type="text"
											name="rpr_recipe_carousel_options[pinterest_color]"
											id="pinterest-color"
											class="rpr-settings-text rpr-color-select"
											value="<?php echo esc_attr( $this->get_setting( 'pinterest_color', '#C61800' ) ); ?>"
										/>
									</div>
								</div>
							</div>
						</div>

						<div>
							<label for="instagram-color"><?php esc_html_e( 'Instagram', 'recipepress-reloaded' ); ?></label>
							<div>
								<div class="rpr-settings-text-group">
									<div class="rpr-settings-text-wrapper">
										<input type="text"
											name="rpr_recipe_carousel_options[instagram_color]"
											id="instagram-color"
											class="rpr-settings-text rpr-color-select"
											value="<?php echo esc_attr( $this->get_setting( 'instagram_color', '#34526F' ) ); ?>"
										/>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div>
						<label for="yummly-color"><?php esc_html_e( 'Yummly', 'recipepress-reloaded' ); ?></label>
						<div>
							<div class="rpr-settings-text-group">
								<div class="rpr-settings-text-wrapper">
									<input type="text"
										name="rpr_recipe_carousel_options[yummly_color]"
										id="yummly-color"
										class="rpr-settings-text rpr-color-select"
										value="<?php echo esc_attr( $this->get_setting( 'yummly_color', '#0E76A8' ) ); ?>"
									/>
								</div>
							</div>
						</div>
					</div>

					<div>
						<label for="linkedin-color"><?php esc_html_e( 'LinkedIn', 'recipepress-reloaded' ); ?></label>
						<div>
							<div class="rpr-settings-text-group">
								<div class="rpr-settings-text-wrapper">
									<input type="text"
										name="rpr_recipe_carousel_options[linkedin_color]"
										id="linkedin-color"
										class="rpr-settings-text rpr-color-select"
										value="<?php echo esc_attr( $this->get_setting( 'linkedin_color', '#0E76A8' ) ); ?>"
									/>
								</div>
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
