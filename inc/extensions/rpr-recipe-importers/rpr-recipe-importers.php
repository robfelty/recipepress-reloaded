<?php

use Recipepress\Inc\Common\Abstracts\Extension;
use Recipepress\Inc\Core\Importers;
use Recipepress\Inc\Importers\WPUR;

/**
 * Class RPR_Recipe_Importers
 *
 * This class is a part of the plugin's "extension" feature.
 *
 * @since 2.2.0
 *
 * @author Kemory Grubb
 */
class RPR_Recipe_Importers extends Extension {

	/**
	 * Import WP Ultimate Recipes
	 *
	 * @since 2.2.0
	 *
	 * @access public
	 * @var    Importers The class instance.
	 */
	public $importer;

	/**
	 * Social_Media_Sharing constructor.
	 *
	 * @since 1.0.0
	 *
	 * @var string $id       The internal ID of the extension. Must match class name.
	 * @var string $image    An image used as an icon on the extensions page - 500x500.
	 * @var string $title    The title of the extension as displayed on the extensions page.
	 * @var string $desc     The description of the extension as displayed on the extensions page.
	 * @var string $settings Does the extension use a settings page.
	 */
	public function __construct() {
		$this->id             = 'rpr_recipe_importers';
		$this->image          = plugin_dir_url( __FILE__ ) . 'assets/images/icon.png';
		$this->title          = __( 'Recipe Importers', 'recipepress-reloaded' );
		$this->desc           = __( 'Import recipes from 3rd party recipe plugins into RecipePress.', 'recipepress-reloaded' );
		$this->settings       = true;
		$this->enable         = false;
		$this->settings_label = __( 'Import Recipes', 'recipepress-reloaded' );

		$this->importer = new Importers( 'recipepress-reloaded', '2.2.0' );
	}

	/**
	 * All methods that we want to be called by the class goes here.
	 *
	 * @since 2.2.0
	 *
	 * return void
	 */
	public function load() {
		if ( $this->enable ) {
			$this->add_actions();
		}
	}


	/**
	 * Add WordPress actions to be called here.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	private function add_actions() {
		add_action( 'rpr/extensions/settings_page/footer', array( $this, 'render_settings_page' ) );
	}

	/**
	 * The hidden markup that is rendered by the Thickbox modal window.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function render_settings_page() {
		include __DIR__ . '/includes/settings-page.php';
	}

}
