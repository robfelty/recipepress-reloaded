<?php
/**
 * This class handles the Recent Recipe Rating widget.
 *
 * @link   https://wzymedia.com
 *
 * @since  1.3.0
 *
 * @package Recipepress
 */

namespace Recipepress\Inc\Frontend\Widgets;

use Recipepress as NS;
use Recipepress\Inc\Core\Options;
use Recipepress\Inc\Frontend\Rating;

/**
 * The Recent Recipe Ratings widget
 *
 * @package Recipepress
 * @author  wzyMedia <wzy@outlook.com>
 */
class Recipe_Recent_Ratings extends \WP_Widget {

	use NS\Inc\Common\Traits\Utilities;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.7.0
	 */
	public function __construct() {

		$this->set_widget_options();

		// Create the widget.
		parent::__construct(
			'rpr-recent-recipe-rating',
			__( 'RPR Recent Ratings', 'recipepress-reloaded' ),
			$this->widget_options,
			$this->control_options
		);
	}

	/**
	 * Set the options of the widget
	 *
	 * @since 1.7.0
	 */
	private function set_widget_options() {

		// Set up the widget options.
		$this->widget_options = array(
			'classname'   => 'rpr-recent-recipe-rating',
			'description' => esc_html__( 'Displays the contents of recent reviews leaved on a recipe', 'recipepress-reloaded' ),
		);

		// Set up the widget control options.
		$this->control_options = array(
			'width'  => 325,
			'height' => 350,
		);
	}

	/**
	 * Register our widget, un-register the builtin widget.
	 */
	public function register_widget() {

		if ( Options::get_option( 'rpr_recent_recipe_rating_widget' ) ) {
			register_widget( $this );
		}
	}

	/**
	 * Outputs the widget based on the arguments input through the widget controls.
	 *
	 * @since 1.7.0
	 *
	 * @param array $args      Display arguments including 'before_title',
	 *                         'after_title', 'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 *
	 * @return void
	 */
	public function widget( $args, $instance ) {

		global $wpdb;
		$out = '';

		// If there is an error, stop and return.
		if ( isset( $instance['error'] ) && $instance['error'] ) {
			return;
		}

		// Output the theme's $before_widget wrapper.
		$out .= $args['before_widget']; // phpcs:ignore

		// Output the title (if we have any).
		if ( $instance['title'] ) {
			$out .= $args['before_title'] . sanitize_text_field( $instance['title'] ) . $args['after_title']; // phpcs:ignore
		}

		$min_rating    = ! empty( $instance['min_rating'] ) ? (int) $instance['min_rating'] : 4;
		$ratings_count = ! empty( $instance['ratings_count'] ) ? (int) $instance['ratings_count'] : 5;
		$word_count    = ! empty( $instance['word_count'] ) ? (int) $instance['word_count'] : 10;

		$comments = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $wpdb->comments 
				JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->comments.comment_post_ID 
				WHERE ( comment_approved = '1' ) 
				AND comment_karma >= %d
				AND $wpdb->posts.post_type IN ('rpr_recipe')
				ORDER BY $wpdb->comments.comment_date_gmt DESC
				LIMIT %d",
				$min_rating,
				$ratings_count
			)
		);

		$out .= '<div class="rpr recent-rating-container">';

		if ( ! empty( $comments ) ) {
			foreach ( $comments as $comment ) {
				$out .= '<div class="review">';
				$out .= '<div class="avatar">';
				$out .= get_avatar( $comment->comment_author_email, 50, '', $comment->comment_author );
				$out .= '<span style="display: inline-block; font-size: 12px; color: #FFC107" ';
				$out .= 'title="' . sprintf( __( '%s out of 5 stars', 'recipepress-reloaded' ), $comment->comment_karma ) . '">';
				$out .= $this->html_stars( $comment->comment_karma, false );
				$out .= '</span>';
				$out .= '</div>';
				$out .= '<div class="content">';
				$out .= '<a class="post-link" rel="noopener" href="' . get_the_permalink( (int) $comment->ID ) . '">';
				$out .= '<h3>' . $comment->post_title . '</h3>';
				$out .= '</a>';
				$out .= '<p>' . wp_trim_words( $comment->comment_content, $word_count, '...' ) . '</p>';
				$out .= '</div>';
				$out .= '</div>';
			}
		} else {
			$out .= __( 'No recipe ratings found', 'recipepress-reloaded' );
		}

		$out .= '</div>';

		// Close the theme's widget wrapper.
		$out .= $args['after_widget'];

		echo $out; // phpcs:ignore
	}

	/**
	 * Updates the widget control options for the particular instance of the widget.
	 *
	 * @since 1.0.0
	 *
	 * @param array $new_instance New settings for this instance as input by the user.
	 * @param array $old_instance Old settings for this instance.
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {

		// Fill current state with old data to be sure we not loose anything.
		$instance = $old_instance;

		// Check and sanitize all inputs.
		$instance['title']         = wp_strip_all_tags( $new_instance['title'] );
		$instance['ratings_count'] = absint( $new_instance['ratings_count'] );
		$instance['min_rating']    = absint( $new_instance['min_rating'] );
		$instance['word_count']    = absint( $new_instance['word_count'] );

		// Now we return new values and WordPress do all work for you.
		return $instance;
	}

	/**
	 * Displays the widget control options in the widget's admin screen.
	 *
	 * @since 1.0.0
	 *
	 * @param array $instance The current settings.
	 *
	 * @return void
	 */
	public function form( $instance ) {
		// Set up the default form values.
		$defaults = array(
			'title'         => '',
			'ratings_count' => 5,
			'min_rating'    => 4,
			'word_count'    => 10,
		);

		/* Merge the user-selected arguments with the defaults. */
		$instance = (array) wp_parse_args(  $instance, $defaults );

		/* element options. */
		$title         = sanitize_text_field( $instance['title'] );
		$ratings_count = (int) $instance['ratings_count'];
		$min_rating    = (int) $instance['min_rating'];
		$word_count    = (int) $instance['word_count'];
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?> ">
				<?php _e( 'Title (optional)', 'recipepress-reloaded' ); ?>:
			</label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
				   name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ) ?>"/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'ratings_count' ); ?>">
				<?php _e( 'Number of ratings', 'recipepress-reloaded' ); ?>:
			</label>
			<input type="number" class="widefat" id="<?php echo $this->get_field_id( 'ratings_count' ); ?>"
				   name="<?php echo $this->get_field_name( 'ratings_count' ); ?>"
				   value="<?php echo esc_attr( $ratings_count ); ?>"
				   style="width:50px;" min="1" max="100"/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'min_rating' ); ?>">
				<?php _e( 'Minimum rating', 'recipepress-reloaded' ); ?>:
			</label>
			<input type="number" class="widefat" id="<?php echo $this->get_field_id( 'min_rating' ); ?>"
				   name="<?php echo $this->get_field_name( 'min_rating' ); ?>"
				   value="<?php echo esc_attr( $min_rating ); ?>"
				   style="width:50px;" min="1" max="5"/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'word_count' ); ?>">
				<?php _e( 'Word count', 'recipepress-reloaded' ); ?>:
			</label>
			<input type="number" class="widefat" id="<?php echo $this->get_field_id( 'word_count' ); ?>"
				   name="<?php echo $this->get_field_name( 'word_count' ); ?>"
				   value="<?php echo esc_attr( $word_count ); ?>"
				   style="width:50px;" min="1" max="100"/>
		</p>

		<?php
	}
}
