<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wzymedia.com
 * @since             1.0.0
 * @package           Recipepress
 *
 * @wordpress-plugin
 * Plugin Name:       Recipepress Reloaded
 * Plugin URI:        https://wzymedia.com
 * Description:       The swiss army knife for your food blog. A tool to add nicely formatted recipes that are SEO friendly to your blog and to manage your recipe collection.
 * Version:           2.7.1
 * Author:            wzy Media
 * Author URI:        https://wzymedia.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       recipepress-reloaded
 * Domain Path:       /languages
 * Requires PHP:      5.6.0
 * Requires at least: 4.0
 * Tested up to:      6.1
 */

namespace Recipepress;

// If this file is called directly, abort.
defined( 'WPINC' ) || exit;

/**
 * Define Constants
 */
const PLUGIN_NAME        = 'recipepress-reloaded';
const PLUGIN_VERSION     = '2.7.1';
const PLUGIN_TEXT_DOMAIN = 'recipepress-reloaded';

define( __NAMESPACE__ . '\PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( __NAMESPACE__ . '\PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( __NAMESPACE__ . '\ADMIN_DIR', plugin_dir_path( __FILE__ ) . 'inc/admin/' );
define( __NAMESPACE__ . '\ASSETS_DIR', plugin_dir_path( __FILE__ ) . 'assets/' );
define( __NAMESPACE__ . '\ASSETS_URL', plugin_dir_url( __FILE__ ) . 'assets/' );
define( __NAMESPACE__ . '\ADMIN_ASSET_DIR', plugin_dir_path( __FILE__ ) . 'inc/admin/assets/' );
define( __NAMESPACE__ . '\ADMIN_ASSET_URL', plugin_dir_url( __FILE__ ) . 'inc/admin/assets/' );
define( __NAMESPACE__ . '\PUB_ASSET_DIR', plugin_dir_path( __FILE__ ) . 'inc/frontend/assets/' );
define( __NAMESPACE__ . '\PUB_ASSET_URL', plugin_dir_url( __FILE__ ) . 'inc/frontend/assets/' );
define( __NAMESPACE__ . '\IMPORTERS_DIR', plugin_dir_path( __FILE__ ) . 'inc/importers/' );
define( __NAMESPACE__ . '\EXT_DIR', plugin_dir_path( __FILE__ ) . 'inc/extensions/' );
define( __NAMESPACE__ . '\EXT_URL', plugin_dir_url( __FILE__ ) . 'inc/extensions/' );
define( __NAMESPACE__ . '\LIB_DIR', plugin_dir_path( __FILE__ ) . 'inc/libraries/' );
define( __NAMESPACE__ . '\PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Autoload Classes
 */
require_once __DIR__ . '/inc/libraries/autoloader.php';

/**
 * Register Activation and Deactivation Hooks
 * This action is documented in inc/core/class-activator.php
 */
register_activation_hook( __FILE__, array( __NAMESPACE__ . '\Inc\Core\Activator', 'activate' ) );

/**
 * The code that runs during plugin deactivation.
 * This action is documented inc/core/class-deactivator.php
 */
register_deactivation_hook( __FILE__, array( __NAMESPACE__ . '\Inc\Core\Deactivator', 'deactivate' ) );


/**
 * Plugin Singleton Container
 *
 * Maintains a single copy of the plugin Recipepress object.
 *
 * @since    1.0.0
 */
class Recipepress {

	/**
	 * The instance of the plugin.
	 *
	 * @since    1.0.0
	 * @var      Recipepress $init Instance of the plugin.
	 */
	private static $init;
	/**
	 * Loads the plugin
	 *
	 * @access    public
	 */
	public static function init() {

		if ( null === self::$init ) {
			self::$init = new Inc\Core\Init();
			self::$init->run();
		}

		return self::$init;
	}

}

/**
 * Begins execution of the plugin
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * Also returns copy of the app object so 3rd party developers
 * can interact with the plugin's hooks contained within.
 **/
function recipepress_init() {
	return Recipepress::init();
}

$min_php = '5.6.0';

// Check the minimum required PHP version and run the plugin.
if ( version_compare( PHP_VERSION, $min_php, '>=' ) ) {
	recipepress_init();
}
