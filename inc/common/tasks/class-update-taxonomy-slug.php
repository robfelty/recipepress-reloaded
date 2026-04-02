<?php
/**
 * Run through the chore of converting tags to keywords
 *
 * @package Recencio
 */

namespace Recipepress\Inc\Common\Tasks;

use Recipepress as NS;
use Recipepress\Inc\Libraries\WP_Background_Processing\WP_Background_Process;


/**
 * Creates a background process that runs our chore
 *
 * Our recipes were using post tags to store the recipe keyword.
 * This chore converts each `post_tag` to a `rpr_keywords`
 *
 * @since 1.0.0
 *
 * @package Recencio
 */
class Update_Taxonomy_Slug extends WP_Background_Process {

	/**
	 * A tag to identify our process
	 *
	 * @var $action
	 */
	protected $action = 'rpr_update_taxonomy_slug';

	/**
	 * The update message display for this task
	 *
	 * @return array
	 */
	public function update_message() {

		return array(
			$this->action => __( "Your recipe's taxonomies need to be updated.", 'recipepress-reloaded' ),
		);
	}

	/**
	 * The update message display for this task
	 *
	 * @return bool
	 */
	public function is_update_needed() {

		$old_rpr_ver = get_option( 'rpr_version' ) ?: false;

		return '0.10.0' === $old_rpr_ver;
	}

	/**
	 * Returns an array of items to process
	 *
	 * @return array
	 */
	public function items_to_process() {

		$taxonomies = get_option( 'rpr_options' ); // Get old options.
		$taxes      = $taxonomies['tax_custom'];   // Get custom taxonomies.
		$results    = array();

		foreach ( $taxes as $tax ) {
			$results[] = $tax['slug'];
		}

		return $results;
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param int $taxonomy TaxonomyID to iterate over.
	 *
	 * @return mixed
	 */
	protected function task( $taxonomy ) {

		global $wpdb;

		// If a taxonomy starts with `rpr_` skip it.
		if ( 0 === stripos( $taxonomy, 'rpr_' ) ) {
			return false;
		}

		$wpdb->update( $wpdb->term_taxonomy, //phpcs:ignore
			array(
				'taxonomy' => 'rpr_' . $taxonomy,
			),
			array(
				'taxonomy' => $taxonomy,
			),
			array( '%s' ),
			array( '%s' )
		);

		return false;
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {

		update_option( 'rpr_version', '0.11.0' );

		parent::complete();
	}
}
