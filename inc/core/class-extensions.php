<?php
/**
 * Define the plugin's extension functionality
 *
 * @link       https://wzymedia.com
 * @since      1.0.0
 *
 * @package    Recipepress
 */

namespace Recipepress\Inc\Core;

use Recipepress as NS;
use Recipepress\Inc\Common\Utilities\Icons;

/**
 * Define the plugin's extension functionality
 *
 * @since      1.0.0
 *
 * @package    Recipepress
 * @author     wzyMedia <wzy@outlook.com>
 */
class Extensions {

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

		$this->include_extensions();
	}

	/**
	 * Includes the files needed for our extension classes. Then we instantiate
	 * the class and run the 'add_extension' method via a filter.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function include_extensions() {

		$dirs = array_filter( glob( NS\EXT_DIR . '*' ), 'is_dir' );

		foreach ( $dirs as $ext ) {

			$file = $ext . DIRECTORY_SEPARATOR . basename( $ext ) . '.php';

			if ( is_file( $file ) ) {

				include $file;
				// Break folder name into array on '-', uppercase 1st letter, then combine using '_'.
				$class     = implode( '_', array_map( 'ucfirst', explode( '-', basename( $ext ) ) ) );
				$extension = new $class();

				if ( $extension->enable ) {
					// Only show an extension if it hasn't been explicitly disabled.
					add_filter( 'rpr/extensions/list', array( $extension, 'add_extension' ) );
				}
			}
		}
	}

	/**
	 * Get all the registered extensions through a filter.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_extensions() {
		return apply_filters( 'rpr/extensions/list', array() );
	}

	/**
	 * Get all activated extensions.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_active_extensions() {
		return Options::get_option( 'rpr_recipe_active_extensions', array() );
	}

	/**
	 * Enable the 'Extensions' menu option on the settings page
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_extensions_page() {

		add_submenu_page(
			'edit.php?post_type=rpr_recipe',
			'Recipepress Reloaded Extensions',
			__( 'Extensions', 'recipepress-reloaded' ),
			'manage_options',
			'rpr_extensions',
			array( $this, 'render_extensions_page' )
		);
	}

	/**
	 * Renders the "Extensions" settings page
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function render_extensions_page() {

		// Get all extensions.
		$all_extensions = $this->get_extensions();

		// Get active extensions.
		$active_extensions = $this->get_active_extensions();
		?>
		<div class="wrap" data-controller="rpr-extensions">
			<h2 class="rpr-settings__heading"><?php Icons::the_icon( 'rpr-logo' ); ?>&nbsp;<?php echo get_admin_page_title(); // phpcs:ignore ?></h2>
			<?php settings_errors(); ?>
			<h4><?php esc_html_e( 'All Recipepress extensions. Choose which you want to use, then activate it.', 'recipepress-reloaded' ); ?></h4>

			<?php if ( 0 === count( $all_extensions ) ) : ?>
				<div class="wp-list-table widefat plugin-install">
					<h2 style="text-align: center; margin: 5em 0 0 0; font-size: 30px">
						<?php esc_html_e( 'No extensions are installed or activated.', 'recipepress-reloaded' ); ?>
					</h2>
				</div>
			<?php endif; ?>

			<div class="wp-list-table widefat plugin-install">
				<div id="the-list">
					<?php
					if ( $all_extensions ) {
						foreach ( $all_extensions as $slug => $class ) {
							if ( ! class_exists( $class ) ) {
								continue;
							}
							// Instantiate each extension.
							$extension_object = new $class();
							// We will use this object to get the title, description and image of the extension.
							?>
							<div class="plugin-card plugin-card-<?php echo esc_attr( $slug ); ?>">
								<div class="plugin-card-top">
									<div class="name column-name">
										<h3>
											<?php echo esc_html( $extension_object->title ); ?>
											<img src="<?php echo esc_attr( $extension_object->image ); ?>"
												class="plugin-icon"
												alt="<?php echo esc_attr( $extension_object->id ); ?>">
										</h3>
									</div>
									<div class="desc column-description">
										<p><?php echo esc_html( $extension_object->desc ); ?></p>
									</div>
								</div>
								<div class="plugin-card-bottom">
									<?php
									// Use the buttons from our Abstract class to create the buttons
									// Can be overwritten by each extension if needed.
									$extension_object->buttons( $active_extensions );
									?>
								</div>
							</div>
							<?php
						}
					}
					?>
				</div>
			</div>
		</div>
		<?php
		do_action( 'rpr/extensions/settings_page/footer' );
	}

	/**
	 * Add our required scripts on the Extensions settings page
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook_suffix The current admin page hook.
	 *
	 * @return mixed
	 */
	public function extension_admin_scripts( $hook_suffix ) {

		if ( 'rpr_recipe_page_rpr_extensions' !== $hook_suffix ) {
			return false;
		}

		wp_enqueue_script( 'rpr-micromodal-script', NS\ADMIN_ASSET_URL . 'js/micromodal.min.js', array(), '0.3.2', true );
		wp_enqueue_script(
			'rpr-extensions-admin-script',
			NS\ADMIN_ASSET_URL . 'js/rpr-extension-admin.js',
			array( 'jquery' ),
			$this->version,
			true
		);
		wp_localize_script(
			'rpr-extensions-admin-script',
			'rpr_extension_admin',
			array(
				'ajax_url'  => admin_url( 'admin-ajax.php' ),
				'ext_nonce' => wp_create_nonce( 'rpr-extension-admin-nonce' ),
				'text'      => array(
					'activate'   => __( 'Enable', 'recipepress-reloaded' ),
					'deactivate' => __( 'Disable', 'recipepress-reloaded' ),
				),
			)
		);
		wp_enqueue_style( 'rpr-micromodal-styles', NS\ADMIN_ASSET_URL . 'css/micromodal.css', array(), $this->version );

		return true;
	}

	/**
	 * Activating the Extension through AJAX
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function rpr_activate_extension_ajax() {

		// Check if there is a nonce and if it is, verify it. Otherwise, throw an error.
		if ( ! isset( $_POST['ext_nonce'] )
		    || ! wp_verify_nonce( $_POST['ext_nonce'], 'rpr-extension-admin-nonce' ) ) { // phpcs:ignore
			wp_send_json_error( array( 'message' => __( 'Something went wrong!', 'recipepress-reloaded' ) ) );
		}

		// If we don't have an extension id, don't process any further.
		if ( ! isset( $_POST['extension'] ) ) {
			wp_send_json_error( array( 'message' => __( 'No extension data was sent', 'recipepress-reloaded' ) ) );
		}

		// The extension to activate.
		$extension         = sanitize_text_field( $_POST['extension'] );  // phpcs:ignore
		$active_extensions = $this->get_active_extensions();

		// If that extension is already active, don't process it further.
		// If the extension is not active yet, let's try to activate it.
		if ( ! isset( $active_extensions[ $extension ] ) ) {
			// Let's get all the registered extensions.
			$extensions = $this->get_extensions();
			// Check if we have that extensions registered.
			if ( isset( $extensions[ $extension ] ) ) {
				// Put it in the active extensions array.
				$active_extensions[ $extension ] = $extensions[ $extension ];
				// Trigger an action so some plugins can also process some data here.
				do_action( 'rpr/extensions/' . $extension . '/activated', $extension, $extensions );
				// Update the active extensions.
				Options::update_option( 'rpr_recipe_active_extensions', $active_extensions );

				wp_send_json_success( array( 'message' => __( 'Activated', 'recipepress-reloaded' ) ) );
			}
		} else {
			// Our extension is already active.
			wp_send_json_success( array( 'message' => __( 'Already activated', 'recipepress-reloaded' ) ) );
		}
		// Extension might not be registered.
		wp_send_json_error( array( 'message' => __( 'Nothing happened', 'recipepress-reloaded' ) ) );
	}

	/**
	 * Deactivating the Integration through AJAX
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function rpr_deactivate_extension_ajax() {

		// Check if there is a nonce and if it is, verify it. Otherwise, throw an error.
		if ( ! isset( $_POST['ext_nonce'] )
		    || ! wp_verify_nonce( $_POST['ext_nonce'], 'rpr-extension-admin-nonce' ) ) { // phpcs:ignore
			wp_send_json_error( array( 'message' => __( 'Something went wrong!', 'recipepress-reloaded' ) ) );
		}

		// If we don't have an extension id, don't process any further.
		if ( ! isset( $_POST['extension'] ) ) {
			wp_send_json_error( array( 'message' => __( 'No extension data sent', 'recipepress-reloaded' ) ) );
		}
		// The extension to activate.
		$extension         = sanitize_text_field( $_POST['extension'] ); // phpcs:ignore
		$active_extensions = $this->get_active_extensions();

		// If that extension is already deactivated, don't process it further.
		// If the extension is active, let's try to deactivate it.
		if ( isset( $active_extensions[ $extension ] ) ) {
			// Remove the extension from the active extensions.
			unset( $active_extensions[ $extension ] );

			do_action( 'rpr/extensions/' . $extension . '/deactivated', $extension, $active_extensions );
			// Update the active extensions.
			Options::update_option( 'rpr_recipe_active_extensions', $active_extensions );

			wp_send_json_success( array( 'message' => __( 'Deactivated', 'recipepress-reloaded' ) ) );
		}

		wp_send_json_error( array( 'message' => __( 'Not activated', 'recipepress-reloaded' ) ) );
	}

	/**
	 * Load our extensions
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function load_extensions() {

		$active_extensions = $this->get_active_extensions();

		if ( $active_extensions ) {
			foreach ( $active_extensions as $slug => $extension ) {
				if ( ! class_exists( $extension ) ) {
					continue;
				}

				$extension = new $extension();
				$extension->load();
			}
		}
	}

}
