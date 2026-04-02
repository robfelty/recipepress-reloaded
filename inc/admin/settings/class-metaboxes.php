<?php

namespace Recipepress\Inc\Admin\Settings;

/**
 * Handles the generation of metaboxes used on the settings page
 *
 * @since 1.0.0
 *
 * @package    Recipepress
 *
 * @author     Kemory Grubb <kemory@wzymedia.com>
 */
class Metaboxes {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The array of settings tabs
	 *
	 * @since    1.0.0
	 * @access  private
	 * @var    array $options_tabs The array of settings tabs
	 */
	private $options_tabs;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of this plugin.
	 */
	public function __construct( $plugin_name ) {

		$this->plugin_name  = $plugin_name;
		$this->options_tabs = Definitions::get_tabs();
	}

	/**
	 * A work-around to get i18n to work on the settings page
	 *
	 * @since 1.0.0
	 *
	 * @see https://github.com/wphuman/WordPress-Settings-Module/issues/9
	 *
	 * @throws \Exception
	 */
	public function set_tabs() {
		$this->options_tabs = Definitions::get_tabs();
	}

	/**
	 * Register the meta boxes on settings page.
	 *
	 * @since    1.0.0
	 */
	public function add_meta_boxes() {

		foreach ( $this->options_tabs as $tab_id => $tab_name ) {
			add_meta_box(
				$tab_id,                           // Meta box ID.
				$tab_name,                         // Meta box Title.
				array( $this, 'render_meta_box' ), // Callback defining the plugin's innards.
				'recipepress_settings_' . $tab_id, // Screen to which to add the meta box.
				'normal'                           // Context.
			);
		}
	}

	/**
	 * Print the meta box on settings page.
	 *
	 * @since     1.0.0
	 *
	 * @param string $active_tab The active tab.
	 *
	 * @return void
	 */
	public function render_meta_box( $active_tab ) {

		require_once plugin_dir_path( __DIR__ ) . 'settings/views/metabox-display.php';
	}
}
