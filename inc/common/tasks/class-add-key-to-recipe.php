<?php
/**
 * Run through the task of updates our recipe ingredients
 * and instructions
 *
 * @package Recencio
 */

namespace Recipepress\Inc\Common\Tasks;

use Recipepress as NS;
use Recipepress\Inc\Libraries\WP_Background_Processing\WP_Background_Process;
use Recipepress\Inc\Frontend\Rating;


/**
 * Creates a background process that adds a `key`
 * to recipe ingredients and instructions
 *
 * @since 2.0.0
 *
 * @package Recencio
 */
class Add_Key_To_Recipe extends WP_Background_Process {

	/**
	 * A tag to identify our process
	 *
	 * @var $action
	 */
	protected $action = 'rpr_add_key_to_recipe';

	/**
	 * The update message display for this task
	 *
	 * @return array
	 */
	public function update_message() {

		return array(
            $this->action => __( 'Your recipes need to be updated.', 'recipepress-reloaded' ),
		);
	}

	/**
	 * The update message display for this task
	 *
	 * @return bool
	 */
	public function is_update_needed() {

	    $needs_update = get_option( $this->action ) ? get_option( $this->action )['needs_update'] : true;
		$items_to_process = $this->items_to_process();

		return $needs_update && $items_to_process;
	}

	/**
	 * Returns an array of items to process
	 *
	 * @return array
	 */
	public function items_to_process() {
		global $wpdb;

		// phpcs:ignore
		return $wpdb->get_results(
			'SELECT p.ID from ' . $wpdb->posts . ' as p WHERE post_type = "rpr_recipe" AND post_status = "publish"',
			ARRAY_N
		);
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param  array $item The recipe ID to iterate over.
	 *
	 * @return bool
	 */
	protected function task( $item ) {
		$ID                   = (int) $item[0];
		$updated_ingredients  = [];
		$updated_instructions = [];
		$ingredients          = get_post_meta( $ID, 'rpr_recipe_ingredients', true );
		$instructions         = get_post_meta( $ID, 'rpr_recipe_instructions', true );

		foreach ( $ingredients as $ingredient ) {
			if ( empty( $ingredient['key'] ) ) {
				$ingredient['key'] = substr( md5( mt_rand() ), 0, 9 );
			}
			$updated_ingredients[] = $ingredient;
		}
		update_post_meta( $ID, 'rpr_recipe_ingredients', $updated_ingredients );

		foreach ( $instructions as $instruction ) {
			if ( empty( $instruction['key'] ) ) {
				$instruction['key'] = substr( md5( mt_rand() ), 0, 9 );
			}
			$updated_instructions[] = $instruction;
		}
		update_post_meta( $ID, 'rpr_recipe_instructions', $updated_instructions );

		return false;
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {

		update_option( $this->action, array( 'needs_update' => false ) );

		parent::complete();
	}
}
