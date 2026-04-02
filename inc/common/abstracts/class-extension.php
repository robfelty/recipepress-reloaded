<?php

namespace Recipepress\Inc\Common\Abstracts;

use const Recipepress\PLUGIN_VERSION;

/**
 * The abstract extension class.
 *
 * @since 1.0.0
 *
 * @package    Recipepress
 * @author     Kemory Grubb <kemory@wzymedia.com>
 */
abstract class Extension {

	/**
	 * Extension version
	 *
	 * @var string
	 */
	public $version = PLUGIN_VERSION;

	/**
	 * Extension ID
	 *
	 * @var string
	 */
	public $id = '';

	/**
	 * Active Indicator
	 *
	 * @var boolean
	 */
	public $active = false;

	/**
	 * Integration Image
	 *
	 * @var string
	 */
	public $image = '';

	/**
	 * Integration Title
	 *
	 * @var string
	 */
	public $title = '';

	/**
	 * Integration Description
	 *
	 * @var string
	 */
	public $desc = '';

	/**
	 * Integration Settings page
	 *
	 * @var bool
	 */
	public $settings = false;

	/**
	 * Enable the extension
	 *
	 * This provides an easy to disable problematic or
	 * WIP extensions.
	 *
	 * @var bool
	 */
	public $enable = true;

	/**
	 * The label of the "Settings" button
	 *
	 * Sometimes we may need to call our "settings" label
     * by another term
	 *
	 * @var string
	 */
	public $settings_label = 'Settings';

	/**
	 * Load method used to create hooks to extend or apply new features
	 * This method will be called only on active extensions
	 */
	public function load() {}


	/**
	 * Buttons to be shown on the Extensions screen
	 *
	 * @since 1.0.0
	 *
	 * @param  array $extension Array of active extensions.
	 *
	 * @return void
	 */
	public function buttons( $extension ) {
		if ( isset( $extension[ $this->id ] ) ) { ?>
			<button type="button" data-extension="<?php echo esc_attr( $this->id ); ?>"
					class="button button-default button-extension-deactivate">
				<?php esc_html_e( 'Disable', 'recipepress-reloaded' ); ?>
			</button>
			<?php if ( $this->settings ) { ?>
				<button type="button" data-extension="<?php echo esc_attr( $this->id ); ?>"
						class="button button-primary <?php echo esc_attr( $this->id ); ?>-settings"
						data-micromodal-trigger="<?php echo esc_attr( $this->id ); ?>">
                    <?php
                        /* translators: %s: The label of the "Settings" button */
                        printf( __( '%s', 'recipepress-reloaded' ), $this->settings_label );
                    ?>
				</button>
			<?php } ?>
		<?php } else { ?>
			<button type="button" data-extension="<?php echo esc_attr( $this->id ); ?>"
				class="button button-primary button-extension-activate">
				<?php esc_html_e( 'Enable', 'recipepress-reloaded' ); ?>
			</button>
			<?php if ( $this->settings ) { ?>
				<button type="button" data-extension="<?php echo esc_attr( $this->id ); ?>"
						class="button button-primary <?php echo esc_attr( $this->id ); ?>-settings"
						data-micromodal-trigger="<?php echo esc_attr( $this->id ); ?>"
						style="display: none">
					<?php
					    /* translators: %s: The label of the "Settings" button */
					    printf( __( '%s', 'recipepress-reloaded' ), $this->settings_label );
					?>
				</button>
			<?php } ?>
		<?php } ?>

		<?php
	}

	/**
	 * Adds the extension to the list of registered extensions.
	 *
	 * @since 1.0.0
	 *
	 * @param array $extensions The currently registered extensions.
	 *
	 * @return array
	 */
	public function add_extension( $extensions ) {
		$extensions[ $this->id ] = get_class( $this );

		return $extensions;
	}

}
