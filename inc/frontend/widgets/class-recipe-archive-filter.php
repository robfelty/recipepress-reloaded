<?php
/**
 * Displays the Taxonomy Filter Widget
 *
 * @link    https://wzymedia.com
 *
 * @since   1.0.0
 *
 * @package Recipepress
 */

namespace Recipepress\Inc\Frontend\Widgets;

use Recipepress\Inc\Core\Options;

/**
 * Add a widget for filtering the recipe archive
 *
 * @since   1.0.0
 *
 * @package Recipepress
 * @author  wzyMedia <wzy@outlook.com>
 */
class Recipe_Archive_Filter extends \WP_Widget {

	/**
	 * Our taxonomies
	 *
	 * @since 1.0.0
	 *
	 * @var   array $taxonomies An array of all our taxonomies.
	 */
	protected $taxonomies;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 */
	public function __construct() {

		$this->set_widget_options();

		// Create the widget.
		parent::__construct(
			'rpr-recipe-archive-filter',
			__( 'RPR Archive Filter', 'recipepress-reloaded' ),
			$this->widget_options,
			$this->control_options
		);

		$this->taxonomies = explode( ',', strtolower( Options::get_option( 'rpr_taxonomy_selection', '' ) ) );
	}

	/**
	 * Set the options of the widget
	 *
	 * @since 1.0.0
	 */
	private function set_widget_options() {

		// Set up the widget options.
		$this->widget_options = array(
			'classname'   => 'rpr-recipe-archive-filter',
			'description' => esc_html__( 'Add a widget the the recipe archive page to allow the filtering of results', 'recipepress-reloaded' ),
		);

		// Set up the widget control options.
		$this->control_options = array(
			'width'  => 325,
			'height' => 350,
		);
	}

	/**
	 * Register our widget.
	 */
	public function register_widget() {

		if ( Options::get_option( 'rpr_recipe_archive_filter_widget' ) ) {
			register_widget( $this );
		}
	}

	/**
	 * Create Custom Query Vars
	 *
	 * @since 1.0.0
	 *
	 * @param array $vars An array of query variables.
	 *
	 * @return array
	 */
	public function add_query_vars( $vars ) {

		// Add custom query vars that will be public.
		foreach ( $this->taxonomies as $taxonomy ) {
			$vars[] = $taxonomy . '_ids';
		}

		return $vars;
	}

	/**
	 * Override the recipe archive query
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Query $query The current query.
	 *
	 * @return void
	 */
	public function custom_recipe_archive( $query ) {

		// Only run this query if we're on the recipe archive page and not on the admin side.
		if ( is_post_type_archive( 'rpr_recipe' ) && $query->is_main_query() && ! is_admin() ) {

			/**
			 * Used to conditionally build the tax_query
			 * the tax_query is used for a custom taxonomy assigned to the post type
			 * using the `'relation' => 'AND'` to make the search more broad
			 */
			$tax_query_array = array( 'relation' => 'AND' );

			foreach ( $this->taxonomies as $taxonomy ) {

				// Get query vars from URL. E.g. /recipes/?course_ids[]=6 .
				$query_vars = array_filter( get_query_var( $taxonomy . '_ids', array() ) );

				// Conditionally add arrays to the tax_query based on values in the URL.
				$query_vars ? array_push(
					$tax_query_array,
					array(
						'taxonomy' => 'rpr_' . $taxonomy,
						'field'    => 'term_id',
						'terms'    => $query_vars,
					)
				) : null;
			}

			// Final tax_query.
			$query->set( 'tax_query', $tax_query_array );
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

		// This widget will only show on the recipe archive page.
		if ( ! is_archive() && ! is_post_type_archive( 'rpr_recipe' ) ) {
			return;
		}

		// The taxonomies chosen by the user.
		$selected_taxonomies = explode( ',', strtolower( $instance['selected_taxonomies'] ) );

		// Output the theme's $before_widget wrapper.
		echo $args['before_widget']; // phpcs:ignore

		// If a title was input by the user, display it.
		if ( ! empty( $instance['title'] ) ) {
			// phpcs:ignore
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $args['after_title'];
		}
		?>

			<form id="rpr-recipe-archive-filter" method="GET" action="<?php echo esc_url( get_post_type_archive_link( 'rpr_recipe' ) ); ?>">
				<?php
				$selectors = array();

				foreach ( $selected_taxonomies as $taxon ) {
					// If a user selected taxonomy is not in our original list of taxonomies, don't bother.
					if ( in_array( $taxon, $this->taxonomies, true ) ) {
						$selectors[ $taxon ] = get_terms(
							array(
								'taxonomy'   => 'rpr_' . $taxon,
								'hide_empty' => true,
							)
						);
					}
				}
				?>

                <?php if ( isset( $instance['selection_type'] ) && 'checkboxes' === $instance['selection_type'] ) : ?>
                    <?php foreach ( $selectors as $key => $values ) { ?>
                        <div class="rpr-filter-section">
                            <h2><?php echo esc_html( ucfirst( $key ) ); ?></h2>
                            <?php foreach ( $values as $_tax ) { ?>
                                <div>
                                    <input
                                        type="checkbox"
                                        id="<?php echo esc_attr( $_tax->slug ); ?>"
                                        value="<?php echo (int) $_tax->term_id; ?>"
                                        name="<?php echo esc_attr( $key ); ?>_ids[]"
                                        <?php echo in_array( (string) $_tax->term_id, get_query_var( esc_attr( $key ) . '_ids', array() ), true ) ? 'checked' : null; ?>
                                    />
                                    <label for="<?php echo esc_attr( $_tax->slug ); ?>"><?php echo esc_html( $_tax->name ) . ( $instance['show_count'] ? ' (' . (int) $_tax->count . ')' : null ); ?></label>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                <?php else : ?>
                    <?php foreach ( $selectors as $key => $values ) {
                        // Add an empty option to the top of our select dropdown.
                        array_unshift( $values, (object) array( 'name' => sprintf( __( 'Select a %s', 'recipepress-reloaded' ), $key ), 'term_id' => 0, 'count' => 0 ) ); ?>
                        <div class="rpr-filter-dropdown">
                            <label for="rpr-filter-dropdown-<?php echo esc_html( $key ); ?>"><?php echo esc_html( ucfirst( $key ) ); ?></label>
                            <select name="<?php echo esc_attr( $key ); ?>_ids[]" id="rpr-filter-dropdown-<?php echo esc_html( $key ); ?>">
                                <?php foreach ( $values as $i => $v ) { ?>
                                    <?php $selected = in_array( (string) $v->term_id, get_query_var( esc_attr( $key ) . '_ids', array() ), true ) ? 'selected' : null; ?>
                                    <option value="<?php echo (int) $v->term_id; ?>" <?php echo $selected ?>>
                                        <?php echo esc_html( $v->name ) . ( ( $instance['show_count'] && $v->count ) ? ' (' . (int) $v->count . ')' : null ); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>
                <?php endif; ?>
			</form>

		<?php
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

		$instance['title']               = wp_strip_all_tags( $new_instance['title'] );
		$instance['selected_taxonomies'] = sanitize_text_field( $new_instance['selected_taxonomies'] );
		$instance['show_count']          = isset( $new_instance['show_count'] ) ? 1 : 0;
		$instance['selection_type']      = isset( $new_instance['selection_type'] ) ? $new_instance['selection_type'] : 'checkboxes';

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
			'title'               => esc_attr__( 'Filter Recipes', 'recipepress-reloaded' ),
			'selected_taxonomies' => Options::get_option( 'rpr_taxonomy_selection', '' ),
			'show_count'          => 1,
			'selection_type'      => 'checkboxes',
		);

		// Merge the user-selected arguments with the defaults.
		$instance = (array) wp_parse_args( $instance, $defaults );
	?>

		<div class="rpr-widget-controls rpr-archive-filter row">
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
					<?php esc_html_e( 'Title', 'recipepress-reloaded' ); ?>:
				</label>
				<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
					value="<?php echo esc_attr( $instance['title'] ); ?>"/>
			</p>
		</div>
		<div class="rpr-widget-controls rpr-archive-filter row">
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'selected_taxonomies' ) ); ?>">
					<?php esc_html_e( 'Taxonomies', 'recipepress-reloaded' ); ?>:
				</label>
				<input type="text" class="rpr-filter-selected-taxonomies widefat"
					id="<?php echo esc_attr( $this->get_field_id( 'selected_taxonomies' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'selected_taxonomies' ) ); ?>"
					value="<?php echo esc_attr( $instance['selected_taxonomies'] ); ?>"/>
			</p>
		</div>
        <div class="rpr-widget-controls rpr-archive-filter row">
            <p><?php esc_html_e( 'Taxonomy filter selection type', 'recipepress-reloaded' ); ?>:</p>
            <p style="margin:0">
                <input type="radio"
                       id="<?php echo esc_attr( $this->get_field_id( 'selection_type' ) . '_1' ); ?>"
                       name="<?php echo esc_attr( $this->get_field_name( 'selection_type' ) ); ?>"
                       value="checkboxes"
                        <?php echo 'checkboxes' === $instance['selection_type'] ? ' checked' : '';  ?>
                />
                <label for="<?php echo esc_attr( $this->get_field_id( 'selection_type' ) . '_1' ); ?>">
                    <?php esc_html_e( 'Checkboxes', 'recipepress-reloaded' ); ?>
                </label>
            </p>
            <p style="margin:0">
                <input type="radio"
                       id="<?php echo esc_attr( $this->get_field_id( 'selection_type' ) . '_2' ); ?>"
                       name="<?php echo esc_attr( $this->get_field_name( 'selection_type' ) ); ?>"
                       value="dropdowns"
                       <?php echo 'dropdowns' === $instance['selection_type'] ? ' checked' : '';  ?>
                />
                <label for="<?php echo esc_attr( $this->get_field_id( 'selection_type' ) . '_2' ); ?>">
                    <?php esc_html_e( 'Dropdowns', 'recipepress-reloaded' ); ?>
                </label>
            </p>
        </div>
		<div class="rpr-widget-controls rpr-archive-filter row">
			<p>
				<input type="checkbox" class=""
					id="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'show_count' ) ); ?>"
					value="<?php echo (int) $instance['show_count']; ?>"
					<?php checked( $instance['show_count'] ); ?>
					/>
				<label for="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>">
					<?php esc_html_e( 'Show recipe count', 'recipepress-reloaded' ); ?>
				</label>
			</p>
		</div>

		<?php
	}

}
