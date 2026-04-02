<?php
/**
 * Provide a meta box view for the settings page
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @var string $active_tab
 *
 * @package    Recipepress
 * @subpackage Recipepress/common/settings/views
 */

/**
 * Meta Box
 *
 * Renders a single meta box.
 *
 * @since 1.0.0
 */
?>

<form class="rpr-settings__form" action="options.php" method="POST">
	<?php settings_fields( 'recipepress_settings' ); ?>
	<?php do_settings_sections( 'recipepress_settings_' . $active_tab ); ?>
	<?php submit_button( null, 'button-primary' ); ?>
	<button class="button rpr-options-reset">
		<?php esc_html_e( 'Reset Settings', 'recipepress-reloaded' ); ?>
	</button>
</form>
<br class="clear" />
