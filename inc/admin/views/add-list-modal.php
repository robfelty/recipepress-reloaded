<?php
/**
 * The shortcode overlay view (aka the dialog itself) to insert recipe-list shortcodes.
 *
 * @since   1.0.0
 *
 * @package Recipepress
 * @author  wzyMedia <wzy@outlook.com>
 */

use Recipepress\Inc\Core\Options;

?>

<div id="rpr-modal-backdrop-scl" style="display: none"></div>

<div id="rpr-modal-wrap-scl" class="wp-core-ui search-panel-visible" style="display: none">
	<form id="rpr-modal-form-scl" tabindex="-1">
		<?php wp_nonce_field( 'rpr-ajax-nonce', 'rpr_ajax_nonce', false ); ?>
		<div id="rpr-modal-title-scl">
			<?php esc_html_e( 'Create a Recipe List Page', 'recencio-book-reviews' ) ?>
			<button type="button" id="rpr-modal-close-scl"><span
					class="screen-reader-text"><?php echo esc_html( 'Close' ); ?></span></button>
		</div>
		<div id="rpr-modal-panel-scl">
			<ul id="rpr-modal-scl-mode">
				<li>
					<input type="radio" selected="selected" value="rpr-tax-list" id="rpr-modal-scl-mode-tax"
						   name="rpr-modal-scl-mode"/>
					<label for="rpr-modal-scl-mode-tax"><b><?php esc_html_e( 'Taxonomy Index', 'recencio-book-reviews' ); ?></b></label>
					<div id="rpr-taxonomy-panel">
						<select id="review-taxonomy" title="<?php esc_html_e( 'Select your required taxonomy here', 'recencio-book-reviews' ); ?>">
							<?php
							$taxonomies = Options::get_option( 'rpr_taxonomy_selection' );
							if ( $taxonomies ) {
								$keys = explode( ',', $taxonomies );
								foreach ( $keys as $key ) { ?>
									<option value="<?php echo 'rpr_' . strtolower( $key ) ?>"><?php echo esc_html( $key ); ?></option>
								<?php }
							}
							?>
						</select>
					</div>
				</li>
				<li>
					<input type="radio" value="rpr-index" name="rpr-modal-scl-mode"
						   id="rpr-modal-scl-mode-ind"/>
					<label for="rpr-modal-scl-mode-ind"><b><?php esc_html_e( 'Recipe Index', 'recencio-book-reviews' ); ?></b></label>
				</li>
				<li>
					<input type="radio" value="rpr-ingredients" name="rpr-modal-scl-mode"
						   id="rpr-modal-scl-mode-grid"/>
					<label for="rpr-modal-scl-mode-grid"><b><?php esc_html_e( 'Ingredients Index', 'recencio-book-reviews' ); ?></b></label>
				</li>
			</ul>
		</div>
		<div class="submitbox">
			<div id="rpr-modal-cancel-scl">
				<a class="submitdelete deletion" href="#"><?php esc_html_e( 'Cancel', 'recencio-book-reviews' ); ?></a>
			</div>
			<div id="rpr-modal-update-scl">
				<input type="submit" class="button button-primary" id="rpr-modal-submit-scl"
					   name="rpr-modal-submit-scl" value="<?php esc_attr_e( 'Insert', 'recencio-book-reviews' ); ?>">
			</div>
		</div>
	</form>
</div>
