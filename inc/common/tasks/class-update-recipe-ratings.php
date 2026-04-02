<?php
/**
 * Run through the task of updates our recipe ingredients
 *
 * @package Recencio
 */

namespace Recipepress\Inc\Common\Tasks;

use Recipepress as NS;
use Recipepress\Inc\Libraries\WP_Background_Processing\WP_Background_Process;
use Recipepress\Inc\Frontend\Rating;


/**
 * Creates a background process that adds the recipe rating counts to the post_meta
 *
 * Added a new feature that sorts the WP admin recipes table by user ratings count,
 * this tasks adds the count to the `wp_postmeta` table under the `rpr_rating_count` key
 *
 * @since 1.9.0
 *
 * @package Recencio
 */
class Update_Recipe_Ratings extends WP_Background_Process {

	/**
	 * A tag to identify our process
	 *
	 * @var $action
	 */
	protected $action = 'rpr_update_recipe_ratings';

	/**
	 * The update message display for this task
	 *
	 * @return array
	 */
	public function update_message() {

		return array(
			$this->action => __( "Your recipe's user ratings needs to be updated.", 'recipepress-reloaded' ),
		);
	}

	/**
	 * The update message display for this task
	 *
	 * @return bool
	 */
	public function is_update_needed() {

		return get_option( $this->action ) ? get_option( $this->action )['needs_update'] : false;
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
	 * @param  array $recipe The recipe ID to iterate over.
	 *
	 * @return bool
	 */
	protected function task( $recipe ) {

		$ID     = (int) $recipe[0];
		$rating = new Rating( 'recipepress-reloaded', '1.9.0' );
		$count  = $rating->rating_info( 'count', $ID );

		update_post_meta( $ID, 'rpr_rating_count', $count );

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
