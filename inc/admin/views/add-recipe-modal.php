<?php
/**
 * The shortcode overlay view (aka the dialog itself) to insert recipe shortcodes.
 *
 * @since   1.0.0
 *
 * @package Recipepress
 * @author  wzyMedia <wzy@outlook.com>
 */

?>

<div id="rpr-modal-backdrop-scr" style="display: none"></div>
<div id="rpr-modal-wrap-scr" class="wp-core-ui search-panel-visible" style="display: none">
	<form id="rpr-modal-form-scr" tabindex="-1">
		<?php wp_nonce_field( 'recipe-button-ajax-nonce', 'rpr_recipe_button_ajax_nonce', false ); ?>
		<div id="rpr-modal-title-scr">
			<?php esc_html_e( 'Insert recipe', 'recipepress-reloaded' ); ?>
			<button type="button" id="rpr-modal-close-scr"><span
					class="screen-reader-text"><?php esc_html_e( 'Close', 'recipepress-reloaded' ); ?></span></button>
		</div>
		<div id="rpr-modal-panel-scr">
			<input id="recipe-id-field" type="hidden" name="recipeid"/>
			<input id="recipe-title-field" type="hidden" name="recipetitle"/>

			<p class="howto">
				<?php esc_html_e( 'Choose the recipe you want to include from the list below or search for it.', 'recipepress-reloaded' ); ?>
			</p>

			<div class="link-search-wrapper">
				<label>
					<span class="search-label"><?php esc_html_e( 'Search', 'recipepress-reloaded' ); ?></span>
					<input type="search" id="rpr-search-field" class="link-search-field" autocomplete="off"/>
					<span class="spinner"></span>
				</label>
			</div>

			<div id="rpr-search-results" class="query-results" tabindex="0">
				<ul></ul>
				<div class="river-waiting">
					<span class="spinner"></span>
				</div>
			</div>
			<div id="rpr-most-recent-results" class="query-results" tabindex="0">
				<div class="query-notice" id="query-notice-message">
					<em class="query-notice-default"><?php esc_html_e( 'No search term specified. Showing recent items.', 'recipepress-reloaded' ); ?></em>
					<em class="query-notice-hint screen-reader-text"><?php esc_html_e( 'Search or use up and down arrow keys to select an item.', 'recipepress-reloaded' ); ?></em>
				</div>
				<ul></ul>
				<div class="river-waiting">
					<span class="spinner"></span>
				</div>
			</div>
			<?php esc_html_e( 'Display options', 'recipepress-reloaded' ); ?>
			<div id="rpr-modal-scr-options-panel">
				<b><?php esc_html_e( 'Display options', 'recipepress-reloaded' ); ?>: </b>
				<ul id="rpr-modal-scr-options-list">
					<li>
						<input type="checkbox" id="rpr-embed-excerpt" name="embed-excerpt" value="embed-excerpt"/>
						<label for="rpr-embed-excerpt"><span><?php esc_html_e( 'Embed excerpt only', 'recipepress-reloaded' ); ?></span></label>
					</li>
					<li>
						<input type="checkbox" id="rpr-embed-nodesc" name="embed-nodesc" value="embed-nodesc"/>
						<label for="rpr-embed-nodesc">
							<span>
								<?php esc_html_e( 'Embed without description', 'recipepress-reloaded' ); ?>
							</span>
						</label>
					</li>
				</ul>
			</div>

			<div id="rpr-modal-scr-new-recipe-panel">
			<a href="<?php echo admin_url(); ?>/post-new.php?post_type=rpr_recipe" target="_new">
					<i class="fa fa-plus-circle"></i>
					<?php	_e( 'Create a new recipe.', 'recipepress-reloaded' ); ?>
				</a>
				<span>
					<?php	_e( '(This will open a new tab and you will need to return here for including the recipe)', 'recipepress-reloaded' ); ?>
				</span>
			</div>
		</div>

		<div class="submitbox">
			<div id="rpr-modal-cancel-scr">
				<a class="submitdelete deletion" href="#"><?php _e( 'Cancel', 'recipepress-reloaded' ); ?></a>
			</div>
			<div id="rpr-modal-update-scr">
				<input type="submit" value="<?php esc_attr_e( 'Include Shortcut', 'recipepress-reloaded' ); ?>"
					class="button button-primary" id="rpr-modal-submit-scr" name="rpr-link-submit">
			</div>
		</div>
	</form>
</div>
