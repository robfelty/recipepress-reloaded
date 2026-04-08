<?php

namespace Recipepress\Inc\Blocks;

use Recipepress as NS;

/**
 * Bulk Converter: converts all classic-editor recipes to Gutenberg block format.
 *
 * Adds a "Convert to Blocks" tool page under the Recipes menu in wp-admin.
 * Conversion runs in AJAX batches so it works on large recipe libraries.
 *
 * @since 2.8.0
 */
class Bulk_Converter {

	/** @var string */
	private $plugin_name;

	/** @var string */
	private $version;

	/** Number of recipes processed per AJAX request. */
	private const BATCH_SIZE = 5;

	/** AJAX action name. */
	private const AJAX_ACTION = 'rpr_bulk_convert';

	public function __construct( string $plugin_name, string $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	// ──────────────────────────────────────────────────────────────────────────
	// Hooks
	// ──────────────────────────────────────────────────────────────────────────

	public function register_hooks(): void {
		add_action( 'admin_menu',             array( $this, 'add_submenu_page' ) );
		add_action( 'admin_enqueue_scripts',  array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( $this, 'ajax_batch_convert' ) );
	}

	// ──────────────────────────────────────────────────────────────────────────
	// Admin page
	// ──────────────────────────────────────────────────────────────────────────

	public function add_submenu_page(): void {
		add_submenu_page(
			'edit.php?post_type=rpr_recipe',
			__( 'Convert to Blocks', 'recipepress-reloaded' ),
			__( 'Convert to Blocks', 'recipepress-reloaded' ),
			'edit_posts',
			'rpr-bulk-convert',
			array( $this, 'render_page' )
		);
	}

	public function render_page(): void {
		$unconverted = $this->count_unconverted();
		$total       = $this->count_total();
		$converted   = $total - $unconverted;
		?>
		<div class="wrap" id="rpr-bulk-convert-wrap">
			<h1><?php esc_html_e( 'Convert Recipes to Blocks', 'recipepress-reloaded' ); ?></h1>

			<p>
				<?php
				printf(
					/* translators: 1: number of already-converted recipes, 2: total recipes */
					esc_html__( '%1$d of %2$d recipes already use the block editor.', 'recipepress-reloaded' ),
					$converted,
					$total
				);
				?>
			</p>

			<?php if ( $unconverted > 0 ) : ?>
				<p>
					<?php
					printf(
						/* translators: %d: number of recipes to convert */
						esc_html__( '%d recipe(s) will be converted. Original data will be archived so you can revert via the block editor.', 'recipepress-reloaded' ),
						$unconverted
					);
					?>
				</p>

				<div id="rpr-bulk-convert-progress" style="display:none; margin: 16px 0;">
					<progress id="rpr-bulk-convert-bar" value="0" max="<?php echo esc_attr( $unconverted ); ?>" style="width:100%; height:24px;"></progress>
					<p id="rpr-bulk-convert-status"></p>
				</div>

				<div id="rpr-bulk-convert-error" class="notice notice-error" style="display:none;">
					<p id="rpr-bulk-convert-error-msg"></p>
				</div>

				<div id="rpr-bulk-convert-done" class="notice notice-success" style="display:none;">
					<p><?php esc_html_e( 'All recipes have been converted to blocks.', 'recipepress-reloaded' ); ?></p>
				</div>

				<p>
					<button
						id="rpr-bulk-convert-btn"
						class="button button-primary"
						data-total="<?php echo esc_attr( $unconverted ); ?>"
					>
						<?php esc_html_e( 'Convert All Recipes', 'recipepress-reloaded' ); ?>
					</button>
				</p>
			<?php else : ?>
				<div class="notice notice-success inline">
					<p><?php esc_html_e( 'All recipes are already using the block editor.', 'recipepress-reloaded' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	// ──────────────────────────────────────────────────────────────────────────
	// Assets
	// ──────────────────────────────────────────────────────────────────────────

	public function enqueue_assets( string $hook ): void {
		if ( 'rpr_recipe_page_rpr-bulk-convert' !== $hook ) {
			return;
		}

		$js_url = NS\PLUGIN_URL . 'inc/blocks/assets/js/bulk-convert.js';
		$js_path = NS\PLUGIN_DIR . 'inc/blocks/assets/js/bulk-convert.js';

		if ( ! file_exists( $js_path ) ) {
			return;
		}

		wp_enqueue_script(
			'rpr-bulk-convert',
			$js_url,
			array( 'jquery' ),
			$this->version,
			true
		);

		wp_localize_script( 'rpr-bulk-convert', 'rprBulkConvert', array(
			'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
			'action'    => self::AJAX_ACTION,
			'nonce'     => wp_create_nonce( self::AJAX_ACTION ),
			'batchSize' => self::BATCH_SIZE,
		) );
	}

	// ──────────────────────────────────────────────────────────────────────────
	// AJAX handler
	// ──────────────────────────────────────────────────────────────────────────

	public function ajax_batch_convert(): void {
		check_ajax_referer( self::AJAX_ACTION, 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'recipepress-reloaded' ) ), 403 );
		}

		$query = new \WP_Query( array(
			'post_type'      => 'rpr_recipe',
			'post_status'    => 'any',
			'posts_per_page' => self::BATCH_SIZE,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => Blocks::USES_BLOCKS_KEY,
					'compare' => 'NOT EXISTS',
				),
			),
			'no_found_rows'  => false,
		) );

		$ids       = $query->posts;
		$converted = 0;

		foreach ( $ids as $post_id ) {
			if ( $this->convert_one( (int) $post_id ) ) {
				$converted++;
			}
		}

		// found_posts is total unconverted before this batch ran.
		// After conversion, remaining = found_posts - batch size.
		$still_remaining = max( 0, $query->found_posts - count( $ids ) );

		wp_send_json_success( array(
			'converted'       => $converted,
			'total_remaining' => $still_remaining,
			'done'            => $still_remaining === 0,
		) );
	}

	// ──────────────────────────────────────────────────────────────────────────
	// Conversion logic
	// ──────────────────────────────────────────────────────────────────────────

	/**
	 * Convert a single recipe post to block format.
	 *
	 * - Archives existing meta (ingredients, instructions, post_content).
	 * - Builds serialized block markup and writes it to post_content.
	 * - Sets the USES_BLOCKS_KEY flag.
	 *
	 * @param int $post_id
	 * @return bool True on success.
	 */
	private function convert_one( int $post_id ): bool {
		$post = get_post( $post_id );
		if ( ! $post || 'rpr_recipe' !== $post->post_type ) {
			return false;
		}

		// Skip if already converted.
		if ( Blocks::post_uses_recipe_blocks( $post_id ) ) {
			return false;
		}

		// Archive existing meta + post_content before overwriting.
		// This also sets USES_BLOCKS_KEY so sync_blocks_to_meta skips re-archiving.
		$this->archive_meta( $post_id, $post );

		// Build block markup.
		$block_content = $this->build_block_markup( $post_id );

		$result = wp_update_post( array(
			'ID'           => $post_id,
			'post_content' => $block_content,
		), true );

		return ! is_wp_error( $result );
	}

	/**
	 * Archive existing meta and mark the recipe as using blocks.
	 */
	private function archive_meta( int $post_id, \WP_Post $post ): void {
		$archive_keys = array( 'rpr_recipe_ingredients', 'rpr_recipe_instructions' );
		foreach ( $archive_keys as $key ) {
			if ( metadata_exists( 'post', $post_id, $key ) ) {
				$value = get_post_meta( $post_id, $key, true );
				update_post_meta( $post_id, $key . '_archived', $value );
			}
		}

		// Archive post_content before blocks are written.
		update_post_meta( $post_id, 'rpr_recipe_post_content_archived', $post->post_content );

		// Set the flag so the recipe is treated as block-based immediately.
		update_post_meta( $post_id, Blocks::USES_BLOCKS_KEY, '1' );
	}

	/**
	 * Build the full serialized block markup for a recipe.
	 */
	private function build_block_markup( int $post_id ): string {
		$blocks = array();

		$blocks[] = $this->make_block( 'rpr/recipe-info', array() );
		$blocks[] = $this->make_block( 'rpr/ingredients', array(), $this->build_ingredient_blocks( $post_id ) );
		$blocks[] = $this->make_block( 'rpr/instructions', array(), $this->build_instruction_blocks( $post_id ) );
		$blocks[] = $this->make_block( 'rpr/recipe-notes', array() );

		return implode( "\n", array_map( 'serialize_block', $blocks ) );
	}

	/**
	 * Build inner block arrays for the rpr/ingredients container.
	 */
	private function build_ingredient_blocks( int $post_id ): array {
		$ingredients = get_post_meta( $post_id, 'rpr_recipe_ingredients', true );
		if ( ! is_array( $ingredients ) || empty( $ingredients ) ) {
			return array( $this->make_block( 'rpr/ingredient', array() ) );
		}

		$blocks = array();
		foreach ( $ingredients as $ing ) {
			if ( ! empty( $ing['grouptitle'] ) ) {
				$blocks[] = $this->make_block( 'rpr/ingredient-group', array(
					'title'    => (string) ( $ing['grouptitle'] ?? '' ),
					'blockKey' => (string) ( $ing['key'] ?? wp_generate_password( 9, false ) ),
				) );
				continue;
			}

			$blocks[] = $this->make_block( 'rpr/ingredient', array(
				'amount'       => (string) ( $ing['amount'] ?? '' ),
				'unit'         => (string) ( $ing['unit'] ?? '' ),
				'ingredient'   => (string) ( $ing['ingredient'] ?? '' ),
				'ingredientId' => (int)    ( $ing['ingredient_id'] ?? 0 ),
				'notes'        => (string) ( $ing['notes'] ?? '' ),
				'link'         => (string) ( $ing['link'] ?? '' ),
				'target'       => (string) ( $ing['target'] ?? 'same' ),
				'blockKey'     => (string) ( $ing['key'] ?? wp_generate_password( 9, false ) ),
			) );
		}

		return $blocks ?: array( $this->make_block( 'rpr/ingredient', array() ) );
	}

	/**
	 * Build inner block arrays for the rpr/instructions container.
	 */
	private function build_instruction_blocks( int $post_id ): array {
		$instructions = get_post_meta( $post_id, 'rpr_recipe_instructions', true );
		if ( ! is_array( $instructions ) || empty( $instructions ) ) {
			return array( $this->make_block( 'rpr/instruction', array() ) );
		}

		$blocks = array();
		foreach ( $instructions as $inst ) {
			if ( ! empty( $inst['grouptitle'] ) ) {
				$blocks[] = $this->make_block( 'rpr/instruction-group', array(
					'title'    => (string) ( $inst['grouptitle'] ?? '' ),
					'blockKey' => (string) ( $inst['key'] ?? wp_generate_password( 9, false ) ),
				) );
				continue;
			}

			$blocks[] = $this->make_block( 'rpr/instruction', array(
				'description' => (string) ( $inst['description'] ?? '' ),
				'image'       => (int)    ( $inst['image'] ?? 0 ),
				'imageUrl'    => '',
				'blockKey'    => (string) ( $inst['key'] ?? wp_generate_password( 9, false ) ),
			) );
		}

		return $blocks ?: array( $this->make_block( 'rpr/instruction', array() ) );
	}

	/**
	 * Create a canonical block array suitable for serialize_block().
	 */
	private function make_block( string $name, array $attrs, array $inner = array() ): array {
		return array(
			'blockName'    => $name,
			'attrs'        => $attrs,
			'innerBlocks'  => $inner,
			'innerHTML'    => '',
			'innerContent' => $inner ? array_fill( 0, count( $inner ), null ) : array(),
		);
	}

	// ──────────────────────────────────────────────────────────────────────────
	// Counters
	// ──────────────────────────────────────────────────────────────────────────

	private function count_unconverted(): int {
		$query = new \WP_Query( array(
			'post_type'      => 'rpr_recipe',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => false,
			'meta_query'     => array(
				array(
					'key'     => Blocks::USES_BLOCKS_KEY,
					'compare' => 'NOT EXISTS',
				),
			),
		) );
		return (int) $query->found_posts;
	}

	private function count_total(): int {
		$query = new \WP_Query( array(
			'post_type'      => 'rpr_recipe',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => false,
		) );
		return (int) $query->found_posts;
	}
}
