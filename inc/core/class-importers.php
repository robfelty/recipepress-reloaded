<?php
/**
 * Define background processing chores
 *
 * @link       https://wzymedia.com
 *
 * @since      1.0.0
 *
 * @package    Recipepress
 */

namespace Recipepress\Inc\Core;

use Recipepress as NS;
use Recipepress\Inc\Importers\WPUR;

/**
 * Handles the background tasks the plugin may need to run
 *
 * @since      1.0.0
 *
 * @package    Recipepress
 * @author     wzyMedia <wzy@outlook.com>
 */
class Importers {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Recipe Importers
	 *
	 * @since  2.2.0
	 *
	 * @access public
	 * @var    array $importers An array of recipe importers
	 */
	public $importers = [];

    /**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->importers[] = new WPUR();
	}


	/**
	 * Run our background tasks
	 */
	public function run_recipe_importers() {

		// phpcs:ignore
		if ( ! wp_verify_nonce( $_POST['import_recipes_nonce'], 'import-recipes-nonce' ) ) {

			wp_send_json_error( new \WP_Error( '000', 'Nonce check failed.' ), 403 );
		}

		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json_error( new \WP_Error( '001', 'Insufficient user privileges' ), 403 );
		}


		// if ( isset( $_POST['import_recipe_target'] ) && 'rpr_wpur_to_rpr' === $_POST['import_recipe_target'] ) {
		if ( isset( $_POST['import_recipe_target'] ) ) {
			foreach ( $this->importers as $importer ) {

				// Import WP Ultimate Recipe
				if ( 'rpr_wpur_to_rpr' === $_POST['import_recipe_target'] ) {
					$recipes = $importer->items_to_process();
					foreach ( $recipes as $recipe ) {
						$importer->push_to_queue( $recipe );
					}
					$importer->save()->dispatch();
				}

			}
		}
	}

}
