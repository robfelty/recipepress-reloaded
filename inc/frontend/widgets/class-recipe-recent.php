<?php
/**
 * This class displays a list of recent recipe.
 *
 * @link       https://wzymedia.com
 * @since   1.7.0
 *
 * @package Recipepress
 * @author  wzyMedia <wzy@outlook.com>
 */

namespace Recipepress\Inc\Frontend\Widgets;

use Recipepress\Inc\Core\Options;
use Recipepress\Inc\Frontend\Rating;

/**
 * The recent recipes widget
 *
 * @since   1.7.0
 *
 * @package Recipepress
 * @author  wzyMedia <wzy@outlook.com>
 */
class Recipe_Recent extends \WP_Widget {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 */
	public function __construct() {

		$this->set_widget_options();

		// Create the widget.
		parent::__construct(
			'rpr-recent-recipes',
			__( 'RPR Recent Recipes', 'recipepress-reloaded' ),
			$this->widget_options,
			$this->control_options
		);
	}

	private function set_widget_options() {

		// Set up the widget options.
		$this->widget_options = array(
			'classname'   => 'recent-recipes',
			'description' => esc_html__( 'A widget to display the most recent recipes.', 'recipepress-reloaded' ),
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

		if ( Options::get_option( 'rpr_recent_recipe_widget', true ) ) {
			register_widget( $this );
		}
	}

	/**
	 * Outputs the widget based on the arguments input through the widget controls.
	 *
	 * @uses mb_substr()
	 * @see https://stackoverflow.com/questions/9087502/php-substr-function-with-utf-8-leaves-marks-at-the-end
	 *
	 * @param array $args
	 * @param array $instance
	 *
	 * @since 1.0.0
	 */
	public function widget( $args, $instance ) {

		// If there is an error, stop and return.
		if ( ! empty( $instance['error'] ) ) {
			return;
		}

		// Output the theme's $before_widget wrapper.
		echo $args['before_widget'];

		// Output the title (if we have any).
		if ( isset( $instance['title'] ) ) {
			echo $args['before_title'] . sanitize_text_field( $instance['title'] ) . $args['after_title'];
		}

		// Begin frontend output.
		$out         = '';
		$query_args  = array(
			'post_type'      => ( ! empty( $instance['regular_posts'] ) && $instance['regular_posts'] ) ? array( 'post', 'rpr_recipe' ) : 'rpr_recipe',
			'posts_per_page' => ! empty( $instance['recipe_count'] ) ? (int) $instance['recipe_count'] : 5,
		);
		$recipes     = get_posts( $query_args );
		$rating      = new Rating( 'recipepress-reloaded', '1.0.0' );

		$out .= '<div class="rpr recent-recipe-container">';
		foreach ( $recipes as $recipe ) {
			$out .= '<div class="rpr recent-recipe">';
			$out .= '<div class="rpr featured-image">';
			$out .= '<a href="' . get_the_permalink( $recipe->ID ) . '" rel="noopener">';
			$out .= get_the_post_thumbnail( $recipe->ID );
			$out .= '</a>';
			$out .= '</div>';
			$out .=	'<div class="rpr recipe-details">';
			$out .= '<a href="' . get_the_permalink( $recipe->ID ) . '" rel="noopener">';
			$out .= '<span class="rpr recipe-rating">' . $rating->html_stars( $rating->rating_info( 'avg', $recipe->ID ), false ) . '</span>';
			$out .= '<h3>' . $recipe->post_title . '</h3>';
			$out .= '</a>';
			$out .= '</div>';
			$out .=	'</div>';
		}
		$out .=	'</div>';

		$out .= '<style>
					.rpr.recent-recipe {position: relative;}
					.rpr.featured-image {height: 10rem; overflow: hidden;}
					.rpr.recipe-details h3 {margin: 0 0 5px 5px; color: #fff; line-height: 1.4;}
					.rpr.recipe-details span {color: #fff; margin: 0 0 0 5px;}
					.rpr.recipe-details {position: absolute; bottom: 0; background: linear-gradient(0deg, rgba(0,0,0,0.8) 0%, rgba(255,255,255,0) 100%); width: 100%;}
					.rpr.recent-recipe-container {display: grid; grid-template-columns: repeat(2, 1fr);	gap: 10px 10px;}
				</style>';

		// Print out output.
		echo $out;

		// Close the theme's widget wrapper.
		echo $args['after_widget'];
	}

	/**
	 * Updates the widget control options for the particular instance of the widget.
	 *
	 * @since 1.0.0
	 *
	 * @param object $new_instance
	 * @param object $old_instance
	 *
	 * @return object
	 */
	public function update( $new_instance, $old_instance ) {

		// Fill current state with old data to be sure we not loose anything
		$instance = $old_instance;

		// Check and sanitize all inputs.
		$instance['title']         = strip_tags( $new_instance['title'] );
		$instance['recipe_count']  = (int) $new_instance['recipe_count'];

		// Now we return new values and WordPress do all work for you.
		return $instance;
	}

	/**
	 * Displays the widget control options in the Widgets admin screen.
	 *
	 * @since 0.8.0
	 *
	 * @param object $instance
	 *
	 * @return void
	 *
	 */
	public function form( $instance ) {
		// Set up the default form values.
		$defaults = array(
			'title'         => '',
			'recipe_count'  => 5,
		);

		// Merge the user-selected arguments with the defaults.
		$instance = (array) wp_parse_args( $instance, $defaults );

		// Element options.
		$title         = sanitize_text_field( $instance['title'] );
		$recipe_count  = (int) $instance['recipe_count'];
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?> ">
				<?php _e( 'Title (optional)', 'recipepress-reloaded' ); ?>:
			</label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
				name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>"/>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'recipe_count' ); ?>">
				<?php _e( 'Number of Recipes', 'recipepress-reloaded' ); ?>:
			</label>
			<input type="number" class="widefat" id="<?php echo $this->get_field_id( 'recipe_count' ); ?>"
				name="<?php echo $this->get_field_name( 'recipe_count' ); ?>"
				value="<?php echo esc_attr( $recipe_count ); ?>"
				style="width:50px;" min="1" max="100" pattern="[0-9]"/>
		</p>
		<?php
	}
}
