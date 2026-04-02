<?php
/**
 * The setting page of our extension
 *
 * $var $this The extension class
 */

?>

<div id="<?php echo esc_attr( $this->id ); ?>" class="settings-page wrap modal slide" aria-hidden="true">
	<div class="modal__overlay" tabindex="-1" data-micromodal-close>
		<div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="settings-page-wrap-title">
			<header class="modal__header">
				<h2 id="settings-page-wrap-title" class="modal__title">
					<?php printf( '%s %s', esc_attr( $this->title ), esc_html__( 'Settings', 'recipepress-reloaded' ) ); ?>
				</h2>
				<button class="modal__close" aria-label="Close modal" data-micromodal-close></button>
			</header>

			<div id="settings-page-wrap-content" class="modal__content">
				<form method="post" action="options.php">
					<?php settings_fields( 'rpr-call-to-action' ); ?>
					<?php do_settings_sections( 'rpr-call-to-action' ); ?>
					<div class="rpr-cta-form">

						<div class="container">
							<div class="title">
								<label for="cta-title"><?php esc_html_e( 'CTA Title', 'recipepress-reloaded' ); ?></label>
							</div>
							<div class="body">
								<input type="text"
									id="cta-title"
									style="width: 100%"
									name="rpr_call_to_action_options[cta_title]"
									class="cta-title"
									value="<?php echo esc_html( $this->get_setting( 'cta_title' ) ); ?>"
								/>
							</div>
						</div>

						<div class="container">
							<div class="title">
								<label for="cta-text"><?php esc_html_e( 'CTA Text', 'recipepress-reloaded' ); ?></label>
							</div>
							<div class="body">
								<?php
								wp_editor(
									$this->get_setting( 'cta_text' ),
									'cta-text',
									array(
										'textarea_rows' => 20,
										'media_buttons' => false,
										'teeny'         => true,
										'textarea_name' => 'rpr_call_to_action_options[cta_text]',
									)
								);
								?>
							</div>
						</div>

						<div class="container">
							<div class="title">
								<?php esc_html_e( 'CTA Position', 'recipepress-reloaded' ); ?>
							</div>
							<div class="body">
								<span class="radio-button-wrapper">
									<input type="radio"
										id="top-position"
										name="rpr_call_to_action_options[cta_position]"
										class="cta-position"
										value="1"
										<?php checked( $this->get_setting( 'cta_position' ), 1 ); ?>
									/>
									<label for="top-position"><?php esc_html_e( 'Below Description', 'recipepress-reloaded' ); ?></label>
								</span>

								<span class="radio-button-wrapper">
									<input type="radio"
										id="bottom-position"
										name="rpr_call_to_action_options[cta_position]"
										class="cta-position"
										value="2"
										<?php checked( $this->get_setting( 'cta_position' ), 2 ); ?>
									/>
									<label for="bottom-position"><?php esc_html_e( 'Below Recipe', 'recipepress-reloaded' ); ?></label>
								</span>

								<span class="radio-button-wrapper">
									<input type="radio"
										id="top-bottom-position"
										name="rpr_call_to_action_options[cta_position]"
										class="cta-position"
										value="3"
										<?php checked( $this->get_setting( 'cta_position' ), 3 ); ?>
									/>
									<label for="top-bottom-position"><?php esc_html_e( 'Shortcode', 'recipepress-reloaded' ); ?></label>
									<small style="display:none;"> [rpr-cta]</small>
								</span>

								<span class="radio-button-wrapper">
									<input type="radio"
										id="none-position"
										name="rpr_call_to_action_options[cta_position]"
										class="cta-position"
										value="4"
										<?php checked( $this->get_setting( 'cta_position' ), 4 ); ?>
									/>
									<label for="none-position"><?php esc_html_e( 'None', 'recipepress-reloaded' ); ?></label>
								</span>
							</div>
						</div>

						<div class="container">
							<div class="title"><?php esc_html_e( 'CTA Colors', 'recipepress-reloaded' ); ?></div>
							<div class="body" style="display: flex; flex-wrap: wrap;">
								<div class="color">
									<input type="text"
										name="rpr_call_to_action_options[cta_background_color]"
										class="cta-color" id="cta_background_color"
										value="<?php echo esc_attr( $this->get_setting( 'cta_background_color' ) ); ?>"
									/>
									<label for="cta_background_color"><?php esc_html_e( 'Background', 'recipepress-reloaded' ); ?></label>
								</div>
								<div class="color">
									<input type="text"
										name="rpr_call_to_action_options[cta_border_color]"
										class="cta-color" id="cta_border_color"
										value="<?php echo esc_attr( $this->get_setting( 'cta_border_color' ) ); ?>"
									/>
									<label for="cta_border_color"><?php esc_html_e( 'Border', 'recipepress-reloaded' ); ?></label>
								</div>
								<div class="color">
									<input type="text"
										name="rpr_call_to_action_options[cta_text_color]"
										class="cta-color" id="cta_text_color"
										value="<?php echo esc_attr( $this->get_setting( 'cta_text_color' ) ); ?>"
									/>
									<label for="cta_text_color"><?php esc_html_e( 'Text', 'recipepress-reloaded' ); ?></label>
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
	.rpr-cta-form .container {
		margin: 0 0 2em 0;
	}

	.rpr-cta-form .title {
		margin: 1em 0;
		font-size: 1.2em;
		font-weight: bold;
		color: #636363;
	}

	.rpr-cta-form .container .body .color{
		margin: 0 10px 10px 0;
		flex: 0 1 30%;
	}

</style>
