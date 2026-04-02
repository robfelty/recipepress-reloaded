<div id="rpr_social_media_sharing" class="settings-page wrap modal slide" aria-hidden="true">
	<div class="modal__overlay" tabindex="-1" data-micromodal-close>
		<div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="settings-page-wrap-title">
			<header class="modal__header">
				<h2 id="settings-page-wrap-title" class="modal__title">
					<?php printf( '%s %s', $this->title, __( 'Settings', 'recipepress-reloaded' ) ); ?>
				</h2>
				<button class="modal__close" aria-label="Close modal" data-micromodal-close></button>
			</header>

			<div id="settings-page-wrap-content" class="modal__content">
				<form method="post" action="options.php">
					<?php settings_fields( 'rpr-social-media-sharing' ); ?>
					<?php do_settings_sections( 'rpr-social-media-sharing' ); ?>
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php _e( 'Share title', 'recipepress-reloaded' ); ?></th>
							<td>
								<input type="text"
									name="rpr_social_media_sharing_options[share_buttons_title]"
									class="regular-text"
									value="<?php echo esc_attr( $this->get_setting( 'share_buttons_title' ) ); ?>"
								/>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><?php _e( 'Social media sites', 'recipepress-reloaded' ); ?></th>
							<td>
								<input type="text"
									name="rpr_social_media_sharing_options[social_media_sites]"
									class="regular-text social-media-sites"
									value="<?php echo esc_attr( $this->get_setting( 'social_media_sites' ) ); ?>"
								/>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><?php _e( 'Buttons position', 'recipepress-reloaded' ); ?></th>
							<td>
								<span class="radio-button-wrapper">
									<input type="radio"
										id="top-position"
										name="rpr_social_media_sharing_options[buttons_position]"
										class="buttons-position"
										value="1"
										<?php checked( $this->get_setting( 'buttons_position' ), 1 ); ?>
									/>
									<label for="top-position"><?php _e( 'Top', 'recipepress-reloaded' ); ?></label>
								</span>

								<span class="radio-button-wrapper">
									<input type="radio"
										id="bottom-position"
										name="rpr_social_media_sharing_options[buttons_position]"
										class="buttons-position"
										value="2"
										<?php checked( $this->get_setting( 'buttons_position' ), 2 ); ?>
									/>
									<label for="bottom-position"><?php _e( 'Bottom', 'recipepress-reloaded' ); ?></label>
								</span>

								<span class="radio-button-wrapper">
									<input type="radio"
										id="top-bottom-position"
										name="rpr_social_media_sharing_options[buttons_position]"
										class="buttons-position"
										value="3"
										<?php checked( $this->get_setting( 'buttons_position' ), 3 ); ?>
									/>
									<label for="top-bottom-position"><?php _e( 'Both', 'recipepress-reloaded' ); ?></label>
								</span>

								<span class="radio-button-wrapper">
									<input type="radio"
										id="none-position"
										name="rpr_social_media_sharing_options[buttons_position]"
										class="buttons-position"
										value="4"
										<?php checked( $this->get_setting( 'buttons_position' ), 4 ); ?>
									/>
									<label for="none-position"><?php _e( 'Shortcode', 'recipepress-reloaded' ); ?></label>
									<small style="display:none;"> [rpr-sharing]</small>
								</span>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><?php _e( 'Buttons color', 'recipepress-reloaded' ); ?></th>
							<td>
								<input type="text"
									name="rpr_social_media_sharing_options[buttons_color]"
									class="regular-text buttons-color"
									value="<?php echo esc_attr( $this->get_setting( 'buttons_color' ) ); ?>"
								/>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><?php _e( 'Twitter username', 'recipepress-reloaded' ); ?></th>
							<td>
								<input type="text"
									name="rpr_social_media_sharing_options[twitter_username]"
									class="regular-text"
									value="<?php echo esc_attr( $this->get_setting( 'twitter_username' ) ); ?>"
								/>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><?php _e( 'Display on home page', 'recipepress-reloaded' ); ?></th>
							<td>
								<input type="checkbox"
									name="rpr_social_media_sharing_options[display_on_homepage]"
									class="regular-checkbox"
									value="1"
									<?php checked( $this->get_setting( 'display_on_homepage' ), 1 ); ?>
								/>
							</td>
						</tr>

					</table>
					<footer class="modal__footer">
						<?php submit_button(); ?>
					</footer>
				</form>
			</div>

		</div>
	</div>
</div>
