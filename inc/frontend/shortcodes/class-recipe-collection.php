<?php
/**
 * Extends the Shortcode abstract class to create new shortcodes
 *
 * @package Recipepress
 */

namespace Recipepress\Inc\Frontend\Shortcodes;

use Recipepress as NS;
use Recipepress\Inc\Common\Abstracts\Shortcode;
use Recipepress\Inc\Frontend\Template;

/**
 * The class handling the `rpr-collection` shortcode
 *
 * @link       https://wzymedia.com
 * @since      1.0.0
 *
 * @author    Kemory Grubb
 */
class Recipe_Collection extends Shortcode {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_name The ID of this plugin.
	 * @param string $version The current version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		parent::__construct( $plugin_name, $version );
	}

	/**
	 * Creates the shortcode for use by WordPress
	 *
	 * @since 1.0.0
	 *
	 * @uses \do_shortcode()
	 *
	 * @param array $options The shortcode options.
	 *
	 * @return string
	 */
	public function the_shortcode( $options ) {

		// Set default values for options not set explicitly.
		$options = shortcode_atts(
			array(
				'ids'      => 'n/a',
				'numbered' => '0',
			),
			$options
		);

		$output = $this->render_shortcode( $options );

		return do_shortcode( $output );
	}

	/**
	 * Renders the markup of our shortcode content
	 *
	 * @since 1.0.0
	 *
	 * @param array $options The shortcode options.
	 *
	 * @return string
	 */
	public function render_shortcode( $options ) {

		$output   = '';
		$recipes  = array();
		$data     = array();
		// $template = new Template( $this->plugin_name, $this->version );
		$num      = (bool) $options['numbered'];

		if ( 'n/a' !== $options['ids'] ) {

			$ids = explode( ',', $options['ids'] );

			foreach ( $ids as $id ) {
				$recipes[] = get_post( (int) $id );
			}

			$data['@context'] = 'http://schema.org';
			$data['@type']    = 'ItemList';

			foreach ( $recipes as $i => $recipe ) {
				if ( null !== $recipe && 'rpr_recipe' === $recipe->post_type ) {
					$url = get_the_permalink( $recipe->ID );
					$count = $i + 1;

					$data['itemListElement'][] = array(
						'@type'    => 'ListItem',
						'position' => $count,
						'url'      => $url,
					);

					$output .= '<div class="rpr-collection">';
					$output .= '<a href="' . $url . '" rel="noopener">';
					$output .= get_the_post_thumbnail( $recipe->ID, 'full', array( 'class' => 'size-full' ) );
					$output .= '</a>';
					$output .= '<a href="' . $url . '" rel="noopener">';
					$output .= '<h2>' . ( $num ? ( $count . '. ' ) : null ) . $recipe->post_title . '</h2>';
					$output .= '</a>';
					$output .= '<p>' . $recipe->post_excerpt . '</p>';
					// @see https://www.seroundtable.com/google-recipe-rich-results-listicles-32046.html
					//$output .= '<script type="application/ld+json">' . wp_json_encode( $template->get_the_rpr_recipe_schema( $recipe->ID ) ) . '</script>';
					$output .= '</div>';
				}
			}
		}

		$output .= '<style>
					.rpr-collection {margin: 0 0 3em 0;} 
					.rpr-collection h2 {margin: 10px 0; line-height: 1;} 
					.rpr-collection .size-full {width: 100%; border-radius: 5px;}
					</style>';

		$output .= '<script type="application/ld+json">' . wp_json_encode( $data ) . '</script>';

		return $output;
	}

	/**
	 * Adds a button for the shortcode dialog above the post editor
	 *
	 * Similar to default "Add List" button
	 *
	 * @since 1.0.0
	 *
	 * @global string $post type
	 *
	 * @param string $editor_id The post editor's ID.
	 *
	 * @return void
	 */
	public function editor_add_button( $editor_id = 'content' ) {

		global $post_type;

		if ( ! in_array( $post_type, array( 'page', 'post' ), true ) ) {
			return;
		}

		printf(
			'<a href="#" id="rpr-add-recipe-list" class="rpr-icon button" data-editor="%s" title="%s">%s</a>',
			esc_attr( $editor_id ),
			esc_attr__( 'Add Collection', 'recipepress-reloaded' ),
			esc_html__( 'Add Collection', 'recipepress-reloaded' )
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
	public function editor_add_modal() {

		global $post_type;

		if ( ! in_array( $post_type, array( 'page', 'post' ), true ) ) {
			return;
		}

		require NS\ADMIN_DIR . '/views/add-collection-modal.php';
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
	public function add_button_scripts( $hook ) {

		global $post_type;

		// Only load on pages where it is necessary.
		if ( ! in_array( $post_type, array( 'page', 'post' ), true ) ) {
			return;
		}

		wp_enqueue_style( 'rpr-add-list-modal', NS\ADMIN_ASSET_URL . 'css/rpr-add-collection-modal.css', array(), $this->version, 'all' );

		wp_enqueue_script( 'rpr-add-list-ajax', NS\ADMIN_ASSET_URL . 'js/rpr-add-collection-ajax.js', array( 'jquery' ), $this->version, true );

		wp_localize_script(
			'rpr-add-collection-ajax',
			'add_collection_vars',
			array(
				'rpr_collection_nonce' => wp_create_nonce( 'rpr-collection-nonce' ),
			)
		);
		wp_localize_script(
			'rpr-add-collection-ajax',
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
	public function process_button_ajax() {

		check_ajax_referer( 'rpr-collection-nonce', 'rpr_collection_nonce' );

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
