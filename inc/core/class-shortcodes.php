<?php
/**
 * Define the plugin's shortcodes functionalities
 *
 * @since      1.0.0
 *
 * @package    Recipepress
 */

namespace Recipepress\Inc\Core;

use Recipepress as NS;
use Recipepress\Inc\Frontend;

/**
 * Define the plugin's shortcode functionality
 *
 * @since      1.0.0
 *
 * @package    Recipepress
 * @author     wzyMedia <wzy@outlook.com>
 */
class Shortcodes {

	/**
	 * The ID of this plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since  1.0.0
	 *
	 * @access private
	 * @var    string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * An instance of the Shortcodes\Recipe class
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 * @var    Frontend\Shortcodes\Recipe $recipe The recipe shortcode class
	 */
	public $recipe;

	/**
	 * An instance of the Shortcodes\Index class
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 * @var    Frontend\Shortcodes\Index $index The recipe-index shortcode class
	 */
	public $index;

	/**
	 * An instance of the Shortcodes\Taxonomy_List class
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 * @var    Frontend\Shortcodes\Taxonomy_List $tax_list The `tax-list` shortcode class
	 */
	public $tax_list;

	/**
	 * An instance of the Shortcodes\Recipe_List class
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 * @var    Frontend\Shortcodes\Recipe_List $rpr_list The `rpr-list` shortcode class
	 */
	public $rpr_list;

	/**
	 * An instance of the Shortcodes\Ingredients class
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 * @var    Frontend\Shortcodes\Ingredients $ingredients The `rpr-ingredients` shortcode class
	 */
	public $ingredients;

	/**
	 * An instance of the Shortcodes\Recipe_Collection class
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 * @var    Frontend\Shortcodes\Recipe_Collection $rpr_collection The `rpr-collection` shortcode class
	 */
	public $rpr_collection;

    /**
     * An instance of the Shortcodes\Filterable class
     *
     * @since  2.0.0
     *
     * @access public
     * @var    Frontend\Shortcodes\Filterable $rpr_filterable The `rpr-filterable` shortcode class
     */
    public $rpr_filterable;

    /**
     * An instance of the Shortcodes\Ratings class
     *
     * @since  2.0.0
     *
     * @access public
     * @var    Frontend\Shortcodes\Ratings $ratings The class instance
     */
    public $ratings;

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

		$this->recipe         = new Frontend\Shortcodes\Recipe( $this->plugin_name, $this->version );
		$this->index          = new Frontend\Shortcodes\Index( $this->plugin_name, $this->version );
		$this->tax_list       = new Frontend\Shortcodes\Taxonomy_List( $this->plugin_name, $this->version );
		$this->rpr_list       = new Frontend\Shortcodes\Recipe_List( $this->plugin_name, $this->version );
		$this->ingredients    = new Frontend\Shortcodes\Ingredients( $this->plugin_name, $this->version );
		$this->rpr_collection = new Frontend\Shortcodes\Recipe_Collection( $this->plugin_name, $this->version );
		$this->rpr_filterable = new Frontend\Shortcodes\Filterable( $this->plugin_name, $this->version );
		$this->ratings        = new Frontend\Shortcodes\Ratings( $this->plugin_name, $this->version );
	}

	/**
	 * Loads the scripts needed for some of our shortcodes
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_shortcode_scripts() {

		// Register now, enqueue later.
		wp_register_script( 'rpr-hideseek', NS\PUB_ASSET_URL . 'js/rpr-hideseek.min.js', array( 'jquery' ), '0.8.0', true );
	}

	/**
	 * Adds a button for the shortcode dialog above the post editor
	 *
	 * Similar to default "Add Media" button
	 *
	 * @since 1.0.0
	 *
	 * @global string $post type
	 *
	 * @param string $editor_id The post editor's ID.
	 *
	 * @return void
	 */
	public function editor_add_recipe_button( $editor_id = 'content' ) {

		global $post_type;

		if ( ! in_array( $post_type, array( 'page', 'post' ), true ) ) {
			return;
		}

		printf(
			'<a href="#" id="rpr-add-recipe-button" class="rpr-icon button" data-editor="%s" title="%s">%s</a>',
			esc_attr( $editor_id ),
			esc_attr__( 'Add Recipe', 'recipepress-reloaded' ),
			esc_html__( 'Add Recipe', 'recipepress-reloaded' )
		);
	}

	/**
	 * Load the "Add Recipe" modal overlay in the footer
	 *
	 * @since 1.0.0
	 *
	 * @global string $post_type
	 *
	 * @return void
	 */
	public function add_recipe_button_modal() {

		global $post_type;

		if ( ! in_array( $post_type, array( 'page', 'post' ), true ) ) {
			return;
		}

		require plugin_dir_path( __DIR__ ) . 'admin/views/add-recipe-modal.php';
	}

	/**
	 * Load the "Add Recipe" modal overlay scripts
	 *
	 * Needed for the AJAX functions of the shortcode dialog box.
	 *
	 * @since 1.0.0
	 *
	 * @global string $post_type
	 *
	 * @param string $hook The post editors admin screen hook.
	 *
	 * @return void
	 */
	public function add_recipe_button_ajax_scripts( $hook ) {

		global $post_type;

		// Only load on pages where it is necessary.
		if ( ! in_array( $post_type, array( 'page', 'post' ), true ) ) {
			return;
		}

		wp_enqueue_style( 'rpr-add-recipe-modal', plugin_dir_url( __DIR__ ) . 'admin/assets/css/rpr-add-recipe-modal.css', array(), $this->version, 'all' );

		wp_enqueue_script( 'add_recipe_button_ajax', plugin_dir_url( __DIR__ ) . 'admin/assets/js/rpr-add-recipe-button-ajax.js', array( 'jquery' ), $this->version, true );
		wp_localize_script(
			'add_recipe_button_ajax',
			'add_recipe_button_ajax_vars',
			array(
				'rpr_recipe_button_ajax_nonce' => wp_create_nonce( 'recipe-button-ajax-nonce' ),
			)
		);
		wp_localize_script(
			'add_recipe_button_ajax',
			'rprRecipeScL10n',
			array(
				'noTitle' => __( 'No title', 'recipepress-reloaded' ),
				'recipe'  => __( 'Recipe', 'recipepress-reloaded' ),
				'save'    => __( 'Insert', 'recipepress-reloaded' ),
				'update'  => __( 'Insert', 'recipepress-reloaded' ),
			)
		);
	}

	/**
	 * Process the data from the shortcode include dialog
	 *
	 * @since 1.0.0
	 *
	 * @uses \check_admin_referer()
	 * @uses \get_posts()
	 * @uses \wp_send_json()
	 *
	 * @return void
	 */
	public function process_add_recipe_button_ajax() {

		check_ajax_referer( 'recipe-button-ajax-nonce', 'rpr_recipe_button_ajax_nonce' );

		$args = array();
		$json = array();

		if ( isset( $_POST['search'] ) ) {
			$args['s'] = sanitize_text_field( wp_unslash( $_POST['search'] ) );
		} else {
			$args['s'] = '';
		}

		$args['pagenum'] = ! empty( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

		$query           = array(
			'posts_per_page' => 10,
		);
		$query['offset'] = $args['pagenum'] > 1 ? $query['posts_per_page'] * ( $args['pagenum'] - 1 ) : 0;

		$recipes = get_posts(
			array(
				's'              => $args['s'],
				'post_type'      => 'rpr_recipe',
				'posts_per_page' => $query['posts_per_page'],
				'offset'         => $query['offset'],
				'orderby'        => 'post_date',
			)
		);

		foreach ( $recipes as $recipe ) {
			$json[] = array(
				'id'    => $recipe->ID,
				'title' => $recipe->post_title,
			);
		}

		wp_send_json( $json );
	}


}
