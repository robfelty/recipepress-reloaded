<?php
/**
 * Provide a dashboard view for the plugin
 *
 * This file is used to markup the plugin settings page.
 *
 * @since 1.0.0
 *
 * @var string $active_tab
 * @var array  $tabs
 *
 * @package    Recipepress
 */

/**
 * Options Page
 *
 * Renders the settings page contents.
 *
 * @since 1.0.0
 */

use Recipepress\Inc\Common\Utilities\Icons;

?>
<div class="wrap rpr-settings__container" data-controller="rpr-settings">
	<h2 class="rpr-settings__heading"><?php Icons::the_icon( 'rpr-logo' ); ?>&nbsp;<?php echo esc_html( get_admin_page_title() ); ?></h2>

	<?php settings_errors( $this->plugin_name . '-notices' ); ?>

	<h2 class="nav-tab-wrapper">
		<?php
		foreach ( $tabs as $tab_slug => $tab_name ) {

			$tab_url = add_query_arg(
				array(
					'settings-updated' => false,
					'tab'              => $tab_slug,
				)
			);

			$active = $active_tab === $tab_slug ? ' nav-tab-active' : '';

			echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . esc_attr( $active ) . '">';
			echo esc_html( $tab_name );
			echo '</a>';
		}
		?>
	</h2>

	<div id="poststuff">
		<div id="post-body" class="metabox-holder">
			<div id="postbox-container" class="postbox-container rpr-settings__container">
				<?php do_meta_boxes( 'recipepress_settings_' . $active_tab, 'normal', $active_tab ); ?>
			</div><!-- #postbox-container-->
		</div><!-- #post-body-->
	</div><!-- #poststuff-->
</div><!-- .wrap -->
