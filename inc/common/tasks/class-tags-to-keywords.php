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
class Tags_To_Keywords extends WP_Background_Process {

	/**
	 * A tag to identify our process
	 *
	 * @var $action
	 */
	public $action = 'rpr_tags_to_keywords_background_process';

	/**
	 * The update message
	 *
	 * @since    1.0.0
	 *
	 * @access public
	 * @var    string $messages The admin update messages.
	 */
	public $message;

	/**
	 * The update message display for this task
	 *
	 * @return array
	 */
	public function update_message() {

		return array(
			$this->action => 'Your recipe\'s "tags" need to be converted to the new "keywords" taxonomy.',
		);
	}

	/**
	 * The update message display for this task
	 *
	 * @return bool
	 */
	public function is_update_needed() {

		$plugin_version = NS\PLUGIN_VERSION;
		$update_status  = get_option( $this->action ) ? get_option( $this->action )['needs_update'] : false;

		if ( '1.0.0' === $plugin_version ) {
			return false;
		}

		if ( false === $update_status ) {
			return update_option( $this->action, array( 'needs_update' => true ) );
		}

		return $update_status;
	}

	/**
	 * Returns an array of items to process
	 *
	 * @return array|null
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
	 * @param mixed $recipe_id Recipe post ID to iterate over.
	 *
	 * @return mixed
	 */
	protected function task( $recipe_id ) {

		$terms      = get_the_terms( $recipe_id, 'post_tag' );
		$_terms     = array();
		$_inserted  = false;
		$_removed   = false;
		$_deleted   = false;
		$_associate = false;

		if ( $terms && ! is_wp_error( $terms ) ) {

			foreach ( $terms as $term ) {

				$_terms[] = $term->name;
				$keyword  = wp_insert_term( $term->name, 'rpr_keywords', array( 'slug' => $term->slug ) );

				if ( is_wp_error( $keyword ) ) {
					continue;
				} else {
					$_inserted = true;
				}

				if ( ! is_wp_error( $keyword ) ) {

					$rem = wp_remove_object_terms( $recipe_id, $term->term_id, 'post_tag' );

					if ( true !== $rem ) {
						continue;
					} else {
						$_removed = true;
					}

					$del = wp_delete_term( $term->term_id, 'post_tag' );

					if ( true !== $del ) {
						continue;
					} else {
						$_deleted = true;
					}

					$set = wp_set_post_terms( $recipe_id, $term->name, 'rpr_keywords', true );

					if ( ! $set || ! is_wp_error( $set ) ) {
						$_associate = true;
					}
				}
			}
		}

		// phpcs:ignore
		error_log(
			sprintf(
				'Updating recipe ID %1$d --> Found "%2$s" --> Inserted keyword "%3$s" --> Removed assoc. with recipe "%4$s" --> Deleted tag "%5$s" --> Made new association "%6$s"',
				$recipe_id,
				implode( ', ', $_terms ),
				$_inserted ? 'Yes' : 'No',
				$_removed ? 'Yes' : 'No',
				$_deleted ? 'Yes' : 'No',
				$_associate ? 'Yes' : 'No'
			)
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

		error_log( $this->action . ' completed.' ); // phpcs:ignore

		update_option( $this->action, array( 'needs_update' => false ) );

		parent::complete();
	}
}
