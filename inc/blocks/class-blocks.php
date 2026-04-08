<?php


namespace Recipepress\Inc\Blocks;

defined( 'ABSPATH' ) || exit;

use Recipepress as NS;
use Recipepress\Inc\Core\Options;
use Recipepress\Inc\Frontend\Template;

/**
 * Registers and manages the Gutenberg blocks for RecipePress Reloaded.
 *
 * @since 2.8.0
 */
class Blocks {

	/** @var string */
	private $plugin_name;

	/** @var string */
	private $version;

	/**
	 * Meta keys to archive when a recipe is first saved as blocks.
	 * These are renamed to <key>_archived for safe-keeping (undo support).
	 */
	private const ARCHIVE_KEYS = array(
		'rpr_recipe_ingredients',
		'rpr_recipe_instructions',
	);

	/**
	 * Meta key used as the canonical "this recipe uses blocks" flag.
	 */
	public const USES_BLOCKS_KEY = 'rpr_recipe_uses_blocks';

	public function __construct( string $plugin_name, string $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	// ──────────────────────────────────────────────────────────────────────────
	// Hooks
	// ──────────────────────────────────────────────────────────────────────────

	public function register_hooks() {
		add_action( 'init',                        array( $this, 'register_block_types' ) );
		add_action( 'init',                        array( $this, 'register_meta' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		add_action( 'wp_enqueue_scripts',          array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'wp_after_insert_post',        array( $this, 'sync_blocks_to_meta' ), 10, 4 );
		add_action( 'add_meta_boxes',              array( $this, 'suppress_metaboxes_in_block_editor' ), 999 );
		add_action( 'rest_api_init',               array( $this, 'register_rest_routes' ) );
	}

	// ──────────────────────────────────────────────────────────────────────────
	// Block type registration
	// ──────────────────────────────────────────────────────────────────────────

	public function register_block_types() {
		// Container blocks: real render callbacks that use the Template class.
		$container_blocks = array(
			'ingredients' => array( $this, 'render_ingredients' ),
			'instructions'=> array( $this, 'render_instructions' ),
			'recipe-info' => array( $this, 'render_recipe_info' ),
			'recipe-notes'=> array( $this, 'render_recipe_notes' ),
		);

		foreach ( $container_blocks as $block => $callback ) {
			$json = plugin_dir_path( __FILE__ ) . "../../blocks/src/{$block}/block.json";
			if ( file_exists( $json ) ) {
				register_block_type( $json, array( 'render_callback' => $callback ) );
			}
		}

		// Leaf / data blocks: no frontend output (parent renders from meta).
		$leaf_blocks = array( 'ingredient', 'ingredient-group', 'instruction', 'instruction-group' );

		foreach ( $leaf_blocks as $block ) {
			$json = plugin_dir_path( __FILE__ ) . "../../blocks/src/{$block}/block.json";
			if ( file_exists( $json ) ) {
				register_block_type( $json, array( 'render_callback' => '__return_empty_string' ) );
			}
		}
	}

	// ──────────────────────────────────────────────────────────────────────────
	// Block render callbacks
	// Each container block renders its section using the existing Template class,
	// so all styling, icons, pluralisation, and term-meta links still apply.
	// ──────────────────────────────────────────────────────────────────────────

	public function render_ingredients( $attributes, $content, $block ) {
		$post_id  = get_the_ID();
		$template = new Template( $this->plugin_name, $this->version );

		ob_start();
		echo '<div class="rpr-ingredients-container">';
		$template->the_rpr_recipe_headline( __( 'Ingredients', 'recipepress-reloaded' ), 'shopping-basket' );
		$template->the_rpr_recipe_ingredients( $post_id, 'circle', '' );
		echo '</div>';
		return ob_get_clean();
	}

	public function render_instructions( $attributes, $content, $block ) {
		$post_id  = get_the_ID();
		$template = new Template( $this->plugin_name, $this->version );

		ob_start();
		echo '<div class="rpr-instruction-container">';
		$template->the_rpr_recipe_headline( __( 'Instructions', 'recipepress-reloaded' ), 'book' );
		$template->the_rpr_recipe_instructions( $post_id, 'circle' );
		echo '</div>';
		return ob_get_clean();
	}

	public function render_recipe_info( $attributes, $content, $block ) {
		$post_id  = get_the_ID();
		$template = new Template( $this->plugin_name, $this->version );

		if ( null === $template->get_the_rpr_recipe_times( $post_id, array() ) ) {
			return '';
		}

		ob_start();
		echo '<div class="rpr-times-container">';
		if ( null !== $template->get_the_rpr_recipe_servings( $post_id, 'chart-pie' ) ) {
			$template->the_rpr_recipe_servings( $post_id, 'chart-pie' );
		}
		$template->the_rpr_recipe_times( $post_id, array( 'hourglass', 'fire', 'clock' ) );
		echo '</div>';
		return ob_get_clean();
	}

	public function render_recipe_notes( $attributes, $content, $block ) {
		$post_id  = get_the_ID();
		$template = new Template( $this->plugin_name, $this->version );

		if ( null === $template->get_the_rpr_recipe_notes( $post_id ) ) {
			return '';
		}

		ob_start();
		echo '<div class="rpr-notes-container">';
		$template->the_rpr_recipe_headline( __( 'Notes', 'recipepress-reloaded' ), 'attach' );
		$template->the_rpr_recipe_notes( $post_id );
		echo '</div>';
		return ob_get_clean();
	}

	// ──────────────────────────────────────────────────────────────────────────
	// Block detection helper (used by the frontend Recipe class)
	// ──────────────────────────────────────────────────────────────────────────

	/**
	 * Returns true if this recipe has been saved as Gutenberg blocks.
	 * The flag is written by sync_blocks_to_meta() on the first REST save.
	 */
	public static function post_uses_recipe_blocks( int $post_id ): bool {
		return (bool) get_post_meta( $post_id, self::USES_BLOCKS_KEY, true );
	}

	// ──────────────────────────────────────────────────────────────────────────
	// Post meta registration
	// ──────────────────────────────────────────────────────────────────────────

	public function register_meta() {
		$auth = function () {
			return current_user_can( 'edit_posts' );
		};

		$string_keys = array(
			'rpr_recipe_prep_time',
			'rpr_recipe_cook_time',
			'rpr_recipe_passive_time',
			'rpr_recipe_servings',
			'rpr_recipe_servings_type',
			'rpr_recipe_notes',
			'rpr_recipe_source',
			self::USES_BLOCKS_KEY,
		);

		foreach ( $string_keys as $key ) {
			if ( ! registered_meta_key_exists( 'post', $key, 'rpr_recipe' ) ) {
				register_post_meta( 'rpr_recipe', $key, array(
					'show_in_rest'  => true,
					'single'        => true,
					'type'          => 'string',
					'auth_callback' => $auth,
				) );
			}
		}
	}

	// ──────────────────────────────────────────────────────────────────────────
	// REST API – undo/restore endpoint
	// ──────────────────────────────────────────────────────────────────────────

	public function register_rest_routes() {
		register_rest_route( 'rpr-blocks/v1', '/restore-meta/(?P<id>\d+)', array(
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'rest_restore_meta' ),
			'permission_callback' => function ( $request ) {
				return current_user_can( 'edit_post', (int) $request['id'] );
			},
			'args' => array(
				'id' => array( 'required' => true, 'type' => 'integer' ),
			),
		) );
	}

	/**
	 * REST handler: restore archived meta and clear the blocks flag.
	 * Called when the user wants to revert a recipe to classic-editor mode.
	 */
	public function rest_restore_meta( \WP_REST_Request $request ) {
		$post_id = (int) $request['id'];

		if ( ! self::post_uses_recipe_blocks( $post_id ) ) {
			return new \WP_Error(
				'rpr_not_blocks',
				__( 'This recipe is not using blocks.', 'recipepress-reloaded' ),
				array( 'status' => 400 )
			);
		}

		foreach ( self::ARCHIVE_KEYS as $key ) {
			$archived = get_post_meta( $post_id, $key . '_archived', true );
			if ( $archived !== '' && $archived !== false ) {
				update_post_meta( $post_id, $key, $archived );
				delete_post_meta( $post_id, $key . '_archived' );
			}
		}

		// Restore pre-block post_content so the classic editor is empty of blocks.
		$archived_content = get_post_meta( $post_id, 'rpr_recipe_post_content_archived', true );
		wp_update_post( array(
			'ID'           => $post_id,
			'post_content' => $archived_content !== false ? $archived_content : '',
		) );
		delete_post_meta( $post_id, 'rpr_recipe_post_content_archived' );

		delete_post_meta( $post_id, self::USES_BLOCKS_KEY );

		return array( 'success' => true );
	}

	// ──────────────────────────────────────────────────────────────────────────
	// Block → Meta sync (fired after REST save)
	// ──────────────────────────────────────────────────────────────────────────

	public function sync_blocks_to_meta( $post_id, $post, $update, $post_before ) {
		if ( 'rpr_recipe' !== $post->post_type ) {
			return;
		}

		if ( ! has_blocks( $post->post_content ) ) {
			return;
		}

		$blocks = parse_blocks( $post->post_content );

		// Only proceed if at least one RPR recipe block is present.
		$has_recipe_blocks = false;
		foreach ( $blocks as $block ) {
			if ( in_array( $block['blockName'], array( 'rpr/ingredients', 'rpr/instructions' ), true ) ) {
				$has_recipe_blocks = true;
				break;
			}
		}

		if ( ! $has_recipe_blocks ) {
			return;
		}

		// On the very first block-editor save: archive the original meta and pre-block post_content.
		if ( ! self::post_uses_recipe_blocks( $post_id ) ) {
			$this->archive_meta( $post_id, $post_before );
		}

		// Sync each container block's inner blocks → post meta.
		foreach ( $blocks as $block ) {
			switch ( $block['blockName'] ) {
				case 'rpr/ingredients':
					$this->sync_ingredients( $post_id, $block['innerBlocks'] );
					break;
				case 'rpr/instructions':
					$this->sync_instructions( $post_id, $block['innerBlocks'] );
					break;
			}
		}
	}

	/**
	 * Archive the original meta keys and set the "uses blocks" flag.
	 * Safe to call multiple times — checks the flag first.
	 */
	private function archive_meta( int $post_id, ?\WP_Post $post_before = null ): void {
		foreach ( self::ARCHIVE_KEYS as $key ) {
			if ( metadata_exists( 'post', $post_id, $key ) ) {
				$value = get_post_meta( $post_id, $key, true );
				update_post_meta( $post_id, $key . '_archived', $value );
				// Don't delete — sync_ingredients/instructions will overwrite it.
			}
		}

		// Archive the pre-block post_content so revert restores the classic editor content.
		// $post_before is the post state before this save — i.e. before blocks were added.
		$original_content = $post_before ? $post_before->post_content : '';
		update_post_meta( $post_id, 'rpr_recipe_post_content_archived', $original_content );

		update_post_meta( $post_id, self::USES_BLOCKS_KEY, '1' );
	}

	private function sync_ingredients( int $post_id, array $inner_blocks ): void {
		$ingredients = array();
		$term_ids    = array();
		$sort        = 1;

		foreach ( $inner_blocks as $block ) {
			$attrs = $block['attrs'] ?? array();

			if ( 'rpr/ingredient-group' === $block['blockName'] ) {
				$ingredients[] = array(
					'grouptitle' => sanitize_text_field( $attrs['title'] ?? '' ),
					'sort'       => $sort++,
					'key'        => sanitize_text_field( $attrs['blockKey'] ?? wp_generate_password( 9, false ) ),
				);
				continue;
			}

			if ( 'rpr/ingredient' !== $block['blockName'] ) {
				continue;
			}

			$name = sanitize_text_field( $attrs['ingredient'] ?? '' );
			$id   = absint( $attrs['ingredientId'] ?? 0 );

			// Look up or create the taxonomy term.
			if ( $name && ! $id ) {
				$term = get_term_by( 'name', $name, 'rpr_ingredient' );
				if ( $term && ! is_wp_error( $term ) ) {
					$id = $term->term_id;
				} elseif ( $name ) {
					$new = wp_insert_term( $name, 'rpr_ingredient' );
					if ( ! is_wp_error( $new ) ) {
						$id = $new['term_id'];
					}
				}
			}

			if ( $id ) {
				$term_ids[] = $id;
			}

			$link = esc_url_raw( remove_accents( urldecode( $attrs['link'] ?? '' ) ) );

			$ingredients[] = array(
				'amount'        => sanitize_text_field( $attrs['amount'] ?? '' ),
				'unit'          => sanitize_text_field( $attrs['unit'] ?? '' ),
				'ingredient'    => $name,
				'ingredient_id' => $id,
				'notes'         => sanitize_text_field( $attrs['notes'] ?? '' ),
				'link'          => $link,
				'target'        => in_array( $attrs['target'] ?? '', array( 'same', 'new' ), true ) ? $attrs['target'] : 'same',
				'sort'          => $sort++,
				'key'           => sanitize_text_field( $attrs['blockKey'] ?? wp_generate_password( 9, false ) ),
			);
		}

		update_post_meta( $post_id, 'rpr_recipe_ingredients', $ingredients );

		if ( $term_ids ) {
			wp_set_post_terms( $post_id, array_unique( $term_ids ), 'rpr_ingredient' );
		}
	}

	private function sync_instructions( int $post_id, array $inner_blocks ): void {
		$instructions = array();
		$sort         = 1;

		foreach ( $inner_blocks as $block ) {
			$attrs = $block['attrs'] ?? array();

			if ( 'rpr/instruction-group' === $block['blockName'] ) {
				$instructions[] = array(
					'grouptitle' => sanitize_text_field( $attrs['title'] ?? '' ),
					'sort'       => $sort++,
					'key'        => sanitize_text_field( $attrs['blockKey'] ?? wp_generate_password( 9, false ) ),
				);
				continue;
			}

			if ( 'rpr/instruction' !== $block['blockName'] ) {
				continue;
			}

			$instructions[] = array(
				'description' => wp_kses_post( $attrs['description'] ?? '' ),
				'image'       => absint( $attrs['image'] ?? 0 ),
				'sort'        => $sort++,
				'key'         => sanitize_text_field( $attrs['blockKey'] ?? wp_generate_password( 9, false ) ),
			);
		}

		update_post_meta( $post_id, 'rpr_recipe_instructions', $instructions );
	}

	// ──────────────────────────────────────────────────────────────────────────
	// Editor assets
	// ──────────────────────────────────────────────────────────────────────────

	/**
	 * Enqueue the active recipe template's stylesheet for block-based recipe pages.
	 * For classic recipes this is done inside recipe.php; for block recipes we must
	 * enqueue it here because recipe.php is never loaded.
	 */
	/**
	 * Enqueue the active recipe template's stylesheet for all recipe pages.
	 *
	 * The template's recipe.php calls wp_enqueue_style() inside the_content filter,
	 * which fires after wp_head() — so those calls are no-ops for classic themes.
	 * Enqueueing here (priority 20, after the style is registered at priority 10)
	 * ensures the <link> tag appears in <head> for both classic and block recipes.
	 */
	public function enqueue_frontend_assets() {
		if ( ! is_singular( 'rpr_recipe' ) ) {
			return;
		}

		$template = Options::get_option( 'rpr_recipe_template', 'rpr_default' );
		if ( ! $template ) {
			$template = 'rpr_default';
		}

		$css_name = str_replace( '_', '-', $template ); // e.g. rpr_default → rpr-default
		$handle   = $css_name . '-template-style';
		$css_path = NS\PLUGIN_DIR . "inc/frontend/templates/{$template}/assets/{$css_name}.css";
		$css_url  = NS\PLUGIN_URL . "inc/frontend/templates/{$template}/assets/{$css_name}.css";

		// Fall back to rpr_default if the configured template CSS doesn't exist.
		if ( ! file_exists( $css_path ) ) {
			$css_path = NS\PLUGIN_DIR . 'inc/frontend/templates/rpr_default/assets/rpr-default.css';
			$css_url  = NS\PLUGIN_URL . 'inc/frontend/templates/rpr_default/assets/rpr-default.css';
			$handle   = 'rpr-default-template-style';
		}

		wp_enqueue_style( $handle, $css_url, array(), $this->version );
	}

	public function enqueue_editor_assets() {
		$screen = get_current_screen();
		if ( ! $screen || 'rpr_recipe' !== $screen->post_type ) {
			return;
		}

		$asset_file = plugin_dir_path( __FILE__ ) . '../../blocks/build/index.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			add_action( 'admin_notices', array( $this, 'build_missing_notice' ) );
			return;
		}

		$asset = include $asset_file;

		wp_enqueue_script(
			'rpr-blocks-editor',
			plugin_dir_url( __FILE__ ) . '../../blocks/build/index.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_enqueue_style(
			'rpr-blocks-editor-style',
			plugin_dir_url( __FILE__ ) . '../../blocks/build/index.css',
			array( 'wp-edit-blocks' ),
			$asset['version']
		);

		wp_localize_script( 'rpr-blocks-editor', 'rprBlocksData', array(
			'unitList'        => $this->get_unit_list(),
			'servingUnitList' => $this->get_serving_unit_list(),
			'restBase'        => rest_url( 'rpr-blocks/v1' ),
			'nonce'           => wp_create_nonce( 'wp_rest' ),
		) );
	}

	public function build_missing_notice() {
		?>
		<div class="notice notice-warning">
			<p>
				<strong>RecipePress Reloaded:</strong>
				<?php esc_html_e( 'Block editor assets are missing. Run `npm install && npm run build` in the blocks/ directory.', 'recipepress-reloaded' ); ?>
			</p>
		</div>
		<?php
	}

	// ──────────────────────────────────────────────────────────────────────────
	// Metabox suppression
	// ──────────────────────────────────────────────────────────────────────────

	public function suppress_metaboxes_in_block_editor() {
		if ( ! $this->is_block_editor_active() ) {
			return;
		}

		// Determine the post being edited — get_the_ID() is unreliable during add_meta_boxes.
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! $post_id ) {
			global $post;
			$post_id = $post ? $post->ID : 0;
		}

		if ( ! $post_id || ! self::post_uses_recipe_blocks( $post_id ) ) {
			return;
		}

		foreach ( array( 'rpr_ingredients_metabox', 'rpr_instructions_metabox', 'rpr_notes_metabox',
			'rpr_information_metabox', 'rpr_nutrition_metabox', 'rpr_video_metabox',
			'rpr_source_metabox', 'rpr_equipment_metabox' ) as $id ) {
			remove_meta_box( $id, 'rpr_recipe', 'normal' );
			remove_meta_box( $id, 'rpr_recipe', 'side' );
			remove_meta_box( $id, 'rpr_recipe', 'advanced' );
		}
	}

	private function is_block_editor_active(): bool {
		$screen = get_current_screen();
		return $screen && method_exists( $screen, 'is_block_editor' ) && $screen->is_block_editor();
	}

	// ──────────────────────────────────────────────────────────────────────────
	// Helpers
	// ──────────────────────────────────────────────────────────────────────────

	private function get_unit_list(): array {
		$raw = Options::get_option( 'rpr_ingredient_unit_list', '' );
		return $raw ? array_values( array_filter( array_map( 'trim', explode( "\n", $raw ) ) ) ) : array();
	}

	private function get_serving_unit_list(): array {
		$raw = Options::get_option( 'rpr_serving_unit_list', '' );
		return $raw ? array_values( array_filter( array_map( 'trim', explode( "\n", $raw ) ) ) ) : array();
	}
}
