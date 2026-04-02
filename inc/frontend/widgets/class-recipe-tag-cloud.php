<?php
/**
 * This class replaces the builtin WP Tag Cloud widget.
 *
 * @link    https://wzymedia.com
 * @since   1.0.0
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
class Recipe_Tag_Cloud extends \WP_Widget {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 */
	public function __construct() {

		$this->set_widget_options();

		// Create the widget.
		parent::__construct(
			'rpr-recipe-tag-cloud',
			__( 'RPR Tag Cloud', 'recipepress-reloaded' ),
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
			'classname'   => 'tags rpr-tags',
			'description' => esc_html__( 'An advanced widget that gives you total control over the output of your tags.', 'recipepress-reloaded' ),
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

		if ( Options::get_option( 'rpr_tag_cloud_widget' ) ) {
			unregister_widget( 'WP_Widget_Tag_Cloud' );
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
	 * @param array $instance  The settings for the particular instance of the widget.
	 *
	 * @return void
	 */
	public function widget( $args, $instance ) {

		// If there is an error, stop and return.
		if ( ! empty( $instance['error'] ) ) {
			return;
		}

		// Make sure empty callbacks aren't passed for custom functions.
		$instance['topic_count_text_callback']  = ! empty( $instance['topic_count_text_callback'] ) ? $instance['topic_count_text_callback'] : 'default_topic_count_text';
		$instance['topic_count_scale_callback'] = ! empty( $instance['topic_count_scale_callback'] ) ? $instance['topic_count_scale_callback'] : 'default_topic_count_scale';

		// If the separator is empty, set it to the default new line.
		$instance['separator'] = ! empty( $instance['separator'] ) ? $instance['separator'] : "\n";

		// Overwrite the echo argument.
		$instance['echo'] = false;

		// Output the theme's $before_widget wrapper.
		echo $args['before_widget']; // phpcs:ignore

		// If a title was inputted by the user, display it.
		if ( ! empty( $instance['title'] ) ) {
			// phpcs:ignore
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $args['after_title'];
		}

		// Get the tag cloud.
		$tags = str_replace( array( "\r", "\n", "\t" ), ' ', wp_tag_cloud( $instance ) );

		// If $format should be flat, wrap it in the <p> element.
		if ( 'flat' === $instance['format'] ) {

			$classes = array( 'term-cloud' );

			foreach ( (array) $instance['taxonomy'] as $tax ) {
				$classes[] = sanitize_html_class( "{$tax}-cloud" );
			}

			$tags = '<p class="' . implode( ' ', $classes ) . '">' . $tags . '</p>';
		}

		// Output the tag cloud.
		echo $tags; // phpcs:ignore

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

		$instance['title']                      = wp_strip_all_tags( $new_instance['title'] );
		$instance['smallest']                   = wp_strip_all_tags( $new_instance['smallest'] );
		$instance['largest']                    = wp_strip_all_tags( $new_instance['largest'] );
		$instance['number']                     = wp_strip_all_tags( $new_instance['number'] );
		$instance['separator']                  = wp_strip_all_tags( $new_instance['separator'] );
		$instance['name__like']                 = wp_strip_all_tags( $new_instance['name__like'] );
		$instance['search']                     = wp_strip_all_tags( $new_instance['search'] );
		$instance['child_of']                   = wp_strip_all_tags( $new_instance['child_of'] );
		$instance['parent']                     = wp_strip_all_tags( $new_instance['parent'] );
		$instance['topic_count_text_callback']  = wp_strip_all_tags( $new_instance['topic_count_text_callback'] );
		$instance['topic_count_scale_callback'] = wp_strip_all_tags( $new_instance['topic_count_scale_callback'] );
		$instance['include']                    = preg_replace( '/[^0-9,]/', '', $new_instance['include'] );
		$instance['exclude']                    = preg_replace( '/[^0-9,]/', '', $new_instance['exclude'] );
		$instance['unit']                       = $new_instance['unit'];
		$instance['format']                     = $new_instance['format'];
		$instance['orderby']                    = $new_instance['orderby'];
		$instance['order']                      = $new_instance['order'];
		$instance['taxonomy']                   = $new_instance['taxonomy'];
		$instance['link']                       = $new_instance['link'];
		$instance['pad_counts']                 = ( isset( $new_instance['pad_counts'] ) ? 1 : 0 );
		$instance['hide_empty']                 = ( isset( $new_instance['hide_empty'] ) ? 1 : 0 );

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
			'title'                      => esc_attr__( 'Tag Cloud', 'recipepress-reloaded' ),
			'order'                      => 'ASC',
			'orderby'                    => 'name',
			'format'                     => 'flat',
			'include'                    => '',
			'exclude'                    => '',
			'unit'                       => 'pt',
			'smallest'                   => 8,
			'largest'                    => 22,
			'link'                       => 'view',
			'number'                     => 0,
			'separator'                  => ' ',
			'child_of'                   => '',
			'parent'                     => '',
			'taxonomy'                   => array( 'post_tag' ),
			'hide_empty'                 => 1,
			'pad_counts'                 => false,
			'search'                     => '',
			'name__like'                 => '',
			'topic_count_text_callback'  => 'default_topic_count_text',
			'topic_count_scale_callback' => 'default_topic_count_scale',
		);

		// Merge the user-selected arguments with the defaults.
		$instance = wp_parse_args( (array) $instance, $defaults );

		// Element options.
		$taxonomies = get_taxonomies(
			array(
				'show_tagcloud' => true,
				'_builtin'      => false,
			),
			'objects'
		);

		$format = array(
			'flat' => esc_attr__( 'Flat', 'recipepress-reloaded' ),
			'list' => esc_attr__( 'List', 'recipepress-reloaded' ),
		);

		$order = array(
			'ASC'  => esc_attr__( 'Ascending', 'recipepress-reloaded' ),
			'DESC' => esc_attr__( 'Descending', 'recipepress-reloaded' ),
			'RAND' => esc_attr__( 'Random', 'recipepress-reloaded' ),
		);

		$orderby = array(
			'count' => esc_attr__( 'Count', 'recipepress-reloaded' ),
			'name'  => esc_attr__( 'Name', 'recipepress-reloaded' ),
		);

		$unit = array(
			'pt' => 'pt',
			'px' => 'px',
			'em' => 'em',
			'%'  => '%',
		);

		?>

		<div class="rcno-widget-controls columns-2 column-first">
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>">
					<?php esc_html_e( 'Title', 'recipepress-reloaded' ); ?>:
				</label>
				<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
					   name="<?php echo $this->get_field_name( 'title' ); ?>"
					   value="<?php echo esc_attr( $instance['title'] ); ?>"/>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>">
					<?php esc_html_e( 'Taxonomy', 'recipepress-reloaded' ); ?>:
				</label>
				<select class="widefat" id="<?php echo $this->get_field_id( 'taxonomy' ); ?>"
						name="<?php echo $this->get_field_name( 'taxonomy' ); ?>[]" size="4" multiple="multiple">
					<?php foreach ( $taxonomies as $taxonomy ) { ?>
						<option value="<?php echo $taxonomy->name; ?>" <?php selected( in_array( $taxonomy->name, (array) $instance['taxonomy'], true ) ); ?>><?php echo $taxonomy->labels->singular_name; ?></option>
					<?php } ?>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'format' ); ?>">
					<?php esc_html_e( 'Format', 'recipepress-reloaded' ); ?>:
				</label>
				<select class="widefat" id="<?php echo $this->get_field_id( 'format' ); ?>"
						name="<?php echo $this->get_field_name( 'format' ); ?>">
					<?php foreach ( $format as $option_value => $option_label ) { ?>
						<option value="<?php echo $option_value; ?>" <?php selected( $instance['format'], $option_value ); ?>><?php echo $option_label; ?></option>
					<?php } ?>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'order' ); ?>">
					<?php esc_html_e( 'Order', 'recipepress-reloaded' ); ?>:
				</label>
				<select class="widefat" id="<?php echo $this->get_field_id( 'order' ); ?>"
						name="<?php echo $this->get_field_name( 'order' ); ?>">
					<?php foreach ( $order as $option_value => $option_label ) { ?>
						<option value="<?php echo $option_value; ?>" <?php selected( $instance['order'], $option_value ); ?>><?php echo $option_label; ?></option>
					<?php } ?>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'orderby' ); ?>">
					<?php esc_html_e( 'Order by', 'recipepress-reloaded' ); ?>:
				</label>
				<select class="widefat" id="<?php echo $this->get_field_id( 'orderby' ); ?>"
						name="<?php echo $this->get_field_name( 'orderby' ); ?>">
					<?php foreach ( $orderby as $option_value => $option_label ) { ?>
						<option value="<?php echo $option_value; ?>" <?php selected( $instance['orderby'], $option_value ); ?>><?php echo $option_label; ?></option>
					<?php } ?>
				</select>
			</p>
		</div>

		<div class="rcno-widget-controls columns-2 column-last">
			<!--<p>
				<label for="<?php echo $this->get_field_id( 'include' ); ?>"><code>include</code></label>
				<input type="text" class="smallfat code" id="<?php echo $this->get_field_id( 'include' ); ?>" name="<?php echo $this->get_field_name( 'include' ); ?>" value="<?php echo esc_attr( $instance['include'] ); ?>" />
			</p>-->
			<!--<p>
				<label for="<?php echo $this->get_field_id( 'exclude' ); ?>"><code>exclude</code></label>
				<input type="text" class="smallfat code" id="<?php echo $this->get_field_id( 'exclude' ); ?>" name="<?php echo $this->get_field_name( 'exclude' ); ?>" value="<?php echo esc_attr( $instance['exclude'] ); ?>" />
			</p>-->
			<p>
				<label for="<?php echo $this->get_field_id( 'number' ); ?>">
					<?php esc_html_e( 'Number', 'recipepress-reloaded' ); ?>:
				</label>
				<input type="number" class="smallfat code" id="<?php echo $this->get_field_id( 'number' ); ?>"
					   name="<?php echo $this->get_field_name( 'number' ); ?>"
					   value="<?php echo esc_attr( $instance['number'] ); ?>"/>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'largest' ); ?>">
					<?php esc_html_e( 'Largest', 'recipepress-reloaded' ); ?>:
				</label>
				<input type="number" class="smallfat code" id="<?php echo $this->get_field_id( 'largest' ); ?>"
					   name="<?php echo $this->get_field_name( 'largest' ); ?>"
					   value="<?php echo esc_attr( $instance['largest'] ); ?>" min="5" max="256"/>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'smallest' ); ?>">
					<?php esc_html_e( 'Smallest', 'recipepress-reloaded' ); ?>:
				</label>
				<input type="number" class="smallfat code" id="<?php echo $this->get_field_id( 'smallest' ); ?>"
					   name="<?php echo $this->get_field_name( 'smallest' ); ?>"
					   value="<?php echo esc_attr( $instance['smallest'] ); ?>" min="1" max="128"/>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'unit' ); ?>">
					<?php esc_html_e( 'Unit', 'recipepress-reloaded' ); ?>:
				</label>
				<select class="smallfat" id="<?php echo $this->get_field_id( 'unit' ); ?>"
						name="<?php echo $this->get_field_name( 'unit' ); ?>">
					<?php foreach ( $unit as $option_value => $option_label ) { ?>
						<option value="<?php echo $option_value; ?>" <?php selected( $instance['unit'], $option_value ); ?>><?php echo $option_label; ?></option>
					<?php } ?>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'separator' ); ?>">
					<?php esc_html_e( 'Separator', 'recipepress-reloaded' ); ?>:
				</label>
				<input type="text" class="smallfat code" id="<?php echo $this->get_field_id( 'separator' ); ?>"
				    name="<?php echo $this->get_field_name( 'separator' ); ?>"
				    value="<?php echo esc_attr( $instance['separator'] ); ?>"/>
			</p>
		</div>
		<div style="clear:both;"></div>
		<?php
	}

}

