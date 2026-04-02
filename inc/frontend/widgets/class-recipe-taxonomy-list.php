<?php
/**
 * This class replaces the builtin WP Taxonomy List widget.
 *
 * @link   https://wzymedia.com
 *
 * @since  1.0.0
 *
 * @package Recipepress
 */

namespace Recipepress\Inc\Frontend\Widgets;

use Recipepress\Inc\Core\Options;

/**
 * This class replaces the builtin WP Tag Cloud widget.
 *
 * @package Recipepress
 * @author  wzyMedia <wzy@outlook.com>
 */
class Recipe_Taxonomy_List extends \WP_Widget {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 */
	public function __construct() {

		$this->set_widget_options();

		// Create the widget.
		parent::__construct(
			'rpr-recipe-taxonomy-list',
			__( 'RPR Taxonomy List', 'recipepress-reloaded' ),
			$this->widget_options,
			$this->control_options
		);
	}

	/**
	 * Set the options of the widget
	 *
	 * @since 1.0.0
	 */
	private function set_widget_options() {

		// Set up the widget options.
		$this->widget_options = array(
			'classname'   => 'taxonomy_list rpr-taxonomy-list',
			'description' => esc_html__( 'An advanced widget that gives you total control over the output of your recipe taxonomies.', 'recipepress-reloaded' ),
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

		if ( Options::get_option( 'rpr_taxonomy_list_widget' ) ) {
			register_widget( $this );
		}
	}

	/**
	 * Outputs the widget based on the arguments input through the widget controls.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args      Display arguments including 'before_title',
	 *                         'after_title', 'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 *
	 * @return void
	 */
	public function widget( $args, $instance ) {

		// If there is an error, stop and return.
		if ( isset( $instance['error'] ) && $instance['error'] ) {
			return;
		}


		// Output the theme's $before_widget wrapper.
		echo $args['before_widget']; // phpcs:ignore

		// Output the title (if we have any).
		if ( $instance['title'] ) {
			echo $args['before_title'] . sanitize_text_field( $instance['title'] ) . $args['after_title']; // phpcs:ignore
		}

		if ( empty( $instance['taxonomy'] ) ) {
			return;
		}

		// Put together the list of terms.
		$terms = get_terms( $instance['taxonomy'], $instance );

		if ( ! is_wp_error( $terms ) && count( $terms ) > 0 ) {

			echo '<ul class="taglist">';

			foreach ( $terms as $term ) {

				echo '<li>';
				echo '<a href="' . esc_url_raw( get_term_link( $term, $instance['taxonomy'] ) ) . '">';
				echo $term->name; // phpcs:ignore

				if ( true === $instance['show_count'] ) {
					echo ' ';
					echo sanitize_text_field( $instance['before_count'] ); // phpcs:ignore
					echo sanitize_text_field( $term->count ); // phpcs:ignore
					echo sanitize_text_field( $instance['after_count'] ); // phpcs:ignore
				}
				echo '</a>';
				echo '</li>';
			}
			echo '</ul>';
		} else {
			// phpcs:ignore
			echo '<p class="tag-list tag-list-warning">' . __( 'No terms found', 'recipepress-reloaded' ) . '</p>';
		}

		// Close the theme's widget wrapper.
		echo $args['after_widget']; // phpcs:ignore
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
		$instance['title']        = wp_strip_all_tags( $new_instance['title'] );
		$instance['taxonomy']     = wp_strip_all_tags( $new_instance['taxonomy'] );
		$instance['item_count']   = absint( $new_instance['item_count'] );
		$instance['order_by']     = wp_strip_all_tags( $new_instance['order_by'] );
		$instance['order']        = wp_strip_all_tags( $new_instance['order'] );
		$instance['show_count']   = (bool) $new_instance['show_count'];
		$instance['before_count'] = wp_strip_all_tags( $new_instance['before_count'] );
		$instance['after_count']  = wp_strip_all_tags( $new_instance['after_count'] );
		$instance['hide_empty']   = (bool) $new_instance['hide_empty'];

		// Now we return new values and WordPress do all work for you.
		return $instance;
	}

	/**
	 * Displays the widget control options in the Widgets admin screen.
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
			'title'        => '',
			'taxonomy'     => 'rpr_course',
			'item_count'   => 0,
			'order_by'     => 'name',
			'order'        => 'ASC',
			'show_count'   => false,
			'before_count' => ' ( ',
			'after_count'  => ' ) ',
			'hide_empty'   => false,
		);

		/* Merge the user-selected arguments with the defaults. */
		$instance = wp_parse_args( (array) $instance, $defaults );

		/* element options. */
		$title        = sanitize_text_field( $instance['title'] );
		$taxonomy     = sanitize_key( $instance['taxonomy'] );
		$item_count   = sanitize_text_field( $instance['item_count'] );
		$order_by     = sanitize_key( $instance['order_by'] );
		$order        = sanitize_sql_orderby( $instance['order'] );
		$show_count   = isset( $instance['show_count'] ) ? (bool) $instance['show_count'] : false;
		$before_count = sanitize_text_field( $instance['before_count'] );
		$after_count  = sanitize_text_field( $instance['after_count'] );
		$hide_empty   = isset( $instance['hide_empty'] ) ? (bool) $instance['hide_empty'] : false;
		$taxonomies   = get_taxonomies( array( 'show_tagcloud' => true, '_builtin' => false ), 'objects' );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?> ">
				<?php _e( 'Title (optional)', 'recipepress-reloaded' ); ?>:
			</label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
				   name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ) ?>"/>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>">
				<?php _e( 'Taxonomy to display', 'recipepress-reloaded' ); ?>:
			</label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'taxonomy' ); ?>"
					name="<?php echo $this->get_field_name( 'taxonomy' ); ?>" size="4" multiple="false">
				<?php foreach ( $taxonomies as $tax ) { ?>
					<option value="<?php echo $tax->name; ?>" <?php selected( in_array( $tax->name, (array) $taxonomy, true ) ); ?>><?php echo $tax->labels->singular_name; ?></option>
				<?php } ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'item_count' ); ?>">
				<?php _e( 'Displayed taxonomy count', 'recipepress-reloaded' ); ?>:
			</label>
			<input type="number" class="widefat" id="<?php echo $this->get_field_id( 'item_count' ); ?>"
				   name="<?php echo $this->get_field_name( 'item_count' ); ?>"
				   value="<?php echo esc_attr( $item_count ); ?>"
				   style="width:50px;" min="1" max="100"/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'order_by' ); ?>">
				<?php _e( 'Order items by', 'recipepress-reloaded' ); ?>:
			</label>
			<select id="<?php echo $this->get_field_id( 'order_by' ); ?>"
					name="<?php echo $this->get_field_name( 'order_by' ); ?>" class="widefat" style="width:100px;">
				<option value="name" <?php echo selected( $order_by, 'name', false ) ?> >
					<?php _e( 'Name', 'recipepress-reloaded' ); ?>:
				</option>
				<option value="count" <?php echo selected( $order_by, 'count', false ); ?> >
					<?php _e( 'Count', 'recipepress-reloaded' ); ?>:
				</option>
			</select>
			<label for="<?php echo $this->get_field_id( 'order' ); ?>">
			</label>
			<select id="<?php echo $this->get_field_id( 'order' ); ?>"
					name="<?php echo $this->get_field_name( 'order' ); ?>"
					class="widefat" style="width:100px;">'
				<option value="asc" <?php echo selected( $order, 'asc', false ); ?> >
					<?php echo 'ASC'; ?>
				</option>
				<option value="desc" <?php echo selected( $order, 'desc', false ); ?> >
					<?php echo 'DESC'; ?>
				</option>
			</select>
		</p>
		<p>
			<input type="checkbox" class="widefat" id="<?php echo $this->get_field_id( 'show_count' ); ?>"
				   name="<?php echo $this->get_field_name( 'show_count' ); ?>" <?php checked( $show_count ); ?> />
			<label for="<?php echo $this->get_field_id( 'show_count' ); ?>">
				<?php _e( 'Show count with', 'rcno-reviews' ); ?>
			</label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'before_count' ); ?>"
				   name="<?php echo $this->get_field_name( 'before_count' ); ?>"
				   value="<?php echo esc_attr( $before_count ); ?>" style="width:20px;"/>
			<label for="<?php echo $this->get_field_id( 'before_count' ); ?>">
				<?php _e( 'before and', 'rcno-reviews' ); ?>
			</label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'after_count' ); ?>"
				   name="<?php echo $this->get_field_name( 'after_count' ); ?>"
				   value="<?php echo esc_attr( $after_count ); ?>" style="width:20px;"/>
			<label for="<?php echo $this->get_field_id( 'after_count' ); ?>">
				<?php _e( 'behind', 'rcno-reviews' ); ?>
			</label>
		</p>
		<p>
			<input type="checkbox" class="widefat" id="<?php echo $this->get_field_id( 'hide_empty' ); ?>"
				   name="<?php echo $this->get_field_name( 'hide_empty' ); ?>" <?php checked( $hide_empty ); ?> />
			<label for="<?php echo $this->get_field_id( 'hide_empty' ); ?>">
				<?php _e( 'Hide empty terms?', 'recipepress-reloaded' ); ?>
			</label>
		</p>
		<?php
	}
}
