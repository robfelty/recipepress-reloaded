<?php
/**
 * This class a widget displaying a calendar of recipe post dates.
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
 * This was inspired by a plugin found on http://pippinsplugins.com
 *
 * @since   1.0.0
 *
 * @package Recipepress
 * @author  wzyMedia <wzy@outlook.com>
 */
class Recipe_Calendar extends \WP_Widget {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 */
	public function __construct() {

		$this->set_widget_options();

		// Create the widget.
		parent::__construct(
			'rpr-recipe-calendar',
			__( 'RPR Recipe Calendar', 'recipepress-reloaded' ),
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
			'classname'   => 'rpr-recipe-calendar',
			'description' => esc_html__( 'A widget displaying a calendar of recipe posts', 'recipepress-reloaded' ),
		);

		// Set up the widget control options.
		$this->control_options = array(
			'width'  => 325,
			'height' => 350,
		);

	}

	/**
	 * Register our recipe calendar widget if enabled in plugin settings.
	 */
	public function register_widget() {

		if ( Options::get_option( 'rpr_recipe_calendar_widget' ) ) {
			register_widget( $this );
		}
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @since 1.0.0
	 *
	 * @param array $args      Display arguments including 'before_title',
	 *                         'after_title', 'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	public function widget( $args, $instance ) {

		$title            = $instance['title'];
		$posttype_enabled = $instance['posttype_enabled'];

		echo $args['before_widget']; // phpcs:ignore

		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore
		}
		?>
		<div class="widget_calendar">
			<div id="calendar_wrap">
				<?php
                    if ( $posttype_enabled ) {
                        $this->get_rpr_calendar( array(), false, true, true );
                    } else {
                        $this->get_rpr_calendar( array( 'rpr_recipe' ) );
                    }
				?>
			</div>
		</div>
		<?php echo $args['after_widget']; // phpcs:ignore ?>
		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance New settings for this instance as input by the user.
	 * @param array $old_instance Old settings for this instance.
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {

		$instance                     = $old_instance;
		$instance['title']            = wp_strip_all_tags( $new_instance['title'] );
		$instance['posttype_enabled'] = isset( $new_instance['posttype_enabled'] ) ? $new_instance['posttype_enabled'] : false;

		return $instance;
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options.
	 */
	public function form( $instance ) {

		$title            = isset( $instance['title'] ) ? $instance['title'] : '';
		$posttype_enabled = isset( $instance['posttype_enabled'] ) ? $instance['posttype_enabled'] : false;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Title', 'recipepress-reloaded' ); ?>:
			</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
				type="text" value="<?php echo esc_attr( $title ); ?>"/>
		</p>
		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'posttype_enabled' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'posttype_enabled' ) ); ?>"
				type="checkbox" value="1" <?php checked( '1', $posttype_enabled ); ?>/>
			<label for="<?php echo esc_attr( $this->get_field_id( 'posttype_enabled' ) ); ?>">
				<?php esc_html_e( 'Show regular posts?', 'recipepress-reloaded' ); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * Create our own calendar widget
	 *
	 * Extends get_calendar() by including custom post types.
	 * Derived from get_calendar() code in /wp-includes/general-template.php.
	 *
	 * @param array $post_types    An array of post types we are working with.
	 * @param bool  $initial       Use one letter initial of day or 3 letter abbrev. e.g. "M" or "Mon".
	 * @param bool  $echo          Print or return the content.
	 * @param bool  $regular_posts Display regular WordPress posts.
	 *
	 * @return mixed
	 */
	public function get_rpr_calendar( $post_types, $initial = true, $echo = true, $regular_posts = false ) {

		global $wpdb, $m, $monthnum, $year, $wp_locale, $posts;
		$custom_post_type = get_post_type_object( 'rpr_recipe' );
		$output = '';

		if ( empty( $post_types ) || ! is_array( $post_types ) ) {
			$post_types = get_post_types([ 'public'   => true, '_builtin' => false ] );
			$post_types = array_merge( $post_types, array( 'post' ) );
		} else {
			/* Trust but verify. */
			$my_post_types = array();
			foreach ( $post_types as $post_type ) {
				if ( post_type_exists( $post_type ) ) {
					$my_post_types[] = $post_type;
				}
			}
			$post_types = $my_post_types;
		}

		$post_types_key = implode( '-', $post_types );
		$post_types_sql = "'" . implode( "' , '", $post_types ) . "'";
		$key            = md5( $m . $monthnum . $year . $post_types_key );
        $cache          = (array) wp_cache_get( 'rpr_calendar', 'calendar' );

		if ( $cache && isset( $cache[$key] ) ) {

            if ( ! $echo ) {

                return $cache[$key];
            }

            echo $cache[$key]; // phpcs:ignore

            return true;
        }

		// Quick check. If we have no posts at all, abort!
		if ( ! $posts ) {

			$sql     = "SELECT 1 as test FROM $wpdb->posts WHERE post_type IN ( $post_types_sql ) AND post_status = 'publish' LIMIT 1";
			$gotsome = $wpdb->get_var( $sql );  // phpcs:ignore

			if ( ! $gotsome ) {

				$cache[ $key ] = '';

				return wp_cache_set( 'rpr_calendar', $cache, 'calendar' );
			}
		}

		if ( isset( $_GET[ 'w' ] ) ) {
			$w = '' . (int) $_GET['w'];
		}

		// Week_begins = 0 stands for Sunday.
		$week_begins = (int) get_option( 'start_of_week' );

		// Let's figure out when we are.
		if ( ! empty( $monthnum ) && ! empty( $year ) ) {
			$thismonth = '' . zeroise( (int) $monthnum, 2 );
			$thisyear  = '' . (int) $year;
		} elseif ( ! empty( $w ) ) {
			// We need to get the month from MySQL.
			$thisyear  = '' . (int) substr( $m, 0, 4 );
			$d         = ( ( $w - 1 ) * 7 ) + 6; //it seems MySQL's weeks disagree with PHP's.
			$thismonth = $wpdb->get_var( "SELECT DATE_FORMAT( ( DATE_ADD( '${thisyear}0101' , INTERVAL $d DAY ) ) , '%m' ) " ); // phpcs:ignore
		} elseif ( ! empty( $m ) ) {
			$thisyear = '' . (int) substr( $m, 0, 4 );
			if ( strlen( $m ) < 6 ) {
				$thismonth = '01';
			} else {
				$thismonth = '' . zeroise( (int) substr( $m, 4, 2 ), 2 );
			}
		} else {
			$thisyear  = gmdate( 'Y', current_time( 'timestamp' ) );
			$thismonth = gmdate( 'm', current_time( 'timestamp' ) );
		}

		$unixmonth = mktime( 0, 0, 0, $thismonth, 1, $thisyear );

		// Get the next and previous month and year with at least one post. // phpcs:ignore
		$previous = $wpdb->get_row( "SELECT DISTINCT MONTH( post_date ) AS month , YEAR( post_date ) AS year
    FROM $wpdb->posts
    WHERE post_date < '$thisyear-$thismonth-01'
    AND post_type IN ( $post_types_sql ) AND post_status = 'publish'
      ORDER BY post_date DESC
      LIMIT 1" );
		$next     = $wpdb->get_row( "SELECT DISTINCT MONTH( post_date ) AS month, YEAR( post_date ) AS year
    FROM $wpdb->posts
    WHERE post_date > '$thisyear-$thismonth-01'
    AND MONTH( post_date ) != MONTH( '$thisyear-$thismonth-01' )
    AND post_type IN ( $post_types_sql ) AND post_status = 'publish'
      ORDER  BY post_date ASC
      LIMIT 1" );

		/* translators: Calendar caption: 1: month name, 2: 4-digit year */
		$calendar_caption = _x( '%1$s %2$s', 'calendar caption' );
		$calendar_output  = '<table id="wp-calendar">
  <caption>' . sprintf( $calendar_caption, $wp_locale->get_month( $thismonth ), date( 'Y', $unixmonth ) ) . '</caption>
  <thead>
  <tr>';

		$myweek = array();

		for ( $wdcount = 0; $wdcount <= 6; $wdcount ++ ) {
			$myweek[] = $wp_locale->get_weekday( ( $wdcount + $week_begins ) % 7 );
		}

		foreach ( $myweek as $wd ) {
			$day_name         = ( true === $initial ) ? $wp_locale->get_weekday_initial( $wd ) : $wp_locale->get_weekday_abbrev( $wd );
			$wd               = esc_attr( $wd );
			$calendar_output .= "\n\t\t<th scope=\"col\" title=\"$wd\">$day_name</th>";
		}

		$calendar_output .= '
  </tr>
  </thead>

  <tfoot>
  <tr>';


		$next_month_link = '';
		$prev_month_link = '';

		if ( $regular_posts && $previous ) {
			$prev_month_link = get_month_link( $previous->year, $previous->month );
		} elseif ( null !== $custom_post_type && null !== $previous ) {
			$prev_month_link = get_site_url() . '/' . $custom_post_type->has_archive . '/' . $previous->year . '/' . $previous->month . '/';
		}

		if ( $regular_posts && $next ) {
			$next_month_link = get_month_link( $next->year, $next->month );
		} elseif ( null !== $custom_post_type && null !== $next ) {
			$next_month_link = get_site_url() . '/' . $custom_post_type->has_archive . '/' . $next->year . '/' . $next->month . '/';
		}

		if ( $previous ) {
			$calendar_output .= "\n\t\t" . '<td colspan="3" id="prev"><a href="' . $prev_month_link . '" title="' . sprintf( __( 'View recipes for %1$s %2$s' ), $wp_locale->get_month( $previous->month ), date( 'Y', mktime( 0, 0, 0, $previous->month, 1, $previous->year ) ) ) . '">&laquo; ' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $previous->month ) ) . '</a></td>';
		} else {
			$calendar_output .= "\n\t\t" . '<td colspan="3" id="prev" class="pad">&nbsp;</td>';
		}

		$calendar_output .= "\n\t\t" . '<td class="pad">&nbsp;</td>';

		if ( $next ) {
			$calendar_output .= "\n\t\t" . '<td colspan="3" id="next"><a href="' . $next_month_link . '" title="' . esc_attr( sprintf( __( 'View recipes for %1$s %2$s' ), $wp_locale->get_month( $next->month ), date( 'Y', mktime( 0, 0, 0, $next->month, 1, $next->year ) ) ) ) . '">' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $next->month ) ) . ' &raquo;</a></td>';
		} else {
			$calendar_output .= "\n\t\t" . '<td colspan="3" id="next" class="pad">&nbsp;</td>';
		}

		$calendar_output .= '
  </tr>
  </tfoot>

  <tbody>
  <tr>';

		// Get days with posts.
		$dayswithposts = $wpdb->get_results( "SELECT DISTINCT DAYOFMONTH( post_date )
    FROM $wpdb->posts WHERE MONTH( post_date ) = '$thismonth'
    AND YEAR( post_date ) = '$thisyear'
    AND post_type IN ( $post_types_sql ) AND post_status = 'publish'
    AND post_date < '" . current_time( 'mysql' ) . '\'', ARRAY_N );
		if ( $dayswithposts ) {
			foreach ( (array) $dayswithposts as $daywith ) {
				$daywithpost[] = $daywith[ 0 ];
			}
		} else {
			$daywithpost = array();
		}

		if ( strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'MSIE' ) !== false || stripos( $_SERVER[ 'HTTP_USER_AGENT' ], 'camino' ) !== false || stripos( $_SERVER[ 'HTTP_USER_AGENT' ], 'safari' ) !== false ) {
			$ak_title_separator = "\n";
		} else {
			$ak_title_separator = ', ';
		}

		$ak_titles_for_day = array();
		$ak_post_titles    = $wpdb->get_results( "SELECT ID, post_title, DAYOFMONTH( post_date ) as dom "
												 . "FROM $wpdb->posts "
												 . "WHERE YEAR( post_date ) = '$thisyear' "
												 . "AND MONTH( post_date ) = '$thismonth' "
												 . "AND post_date < '" . current_time( 'mysql' ) . "' "
												 . "AND post_type IN ( $post_types_sql ) AND post_status = 'publish'"
		);
		if ( $ak_post_titles ) {
			foreach ( (array) $ak_post_titles as $ak_post_title ) {

				$post_title = esc_attr( apply_filters( 'the_title', $ak_post_title->post_title, $ak_post_title->ID ) );

				if ( empty( $ak_titles_for_day[ 'day_' . $ak_post_title->dom ] ) ) {
					$ak_titles_for_day[ 'day_' . $ak_post_title->dom ] = '';
				}
				if ( empty($ak_titles_for_day[(string) $ak_post_title->dom] ) ) // first one
				{
                    $ak_titles_for_day[(string) $ak_post_title->dom] = $post_title;
				} else {
                    $ak_titles_for_day[(string) $ak_post_title->dom] .= $ak_title_separator . $post_title;
				}
			}
		}

		// See how much we should pad in the beginning
		$pad = calendar_week_mod( date( 'w', $unixmonth ) - $week_begins );
		if ( (int) $pad !== 0 ) {
			$calendar_output .= "\n\t\t" . '<td colspan="' . esc_attr( $pad ) . '" class="pad">&nbsp;</td>';
		}

		$daysinmonth = (int) date( 't', $unixmonth );
		for ( $day = 1; $day <= $daysinmonth; ++ $day ) {
			if ( isset( $newrow ) && $newrow ) {
				$calendar_output .= "\n\t</tr>\n\t<tr>\n\t\t";
			}
			$newrow = false;

			if ( $day == gmdate( 'j', current_time( 'timestamp' ) ) && $thismonth == gmdate( 'm', current_time( 'timestamp' ) ) && $thisyear == gmdate( 'Y', current_time( 'timestamp' ) ) ) {
				$calendar_output .= '<td id="today">';
			} else {
				$calendar_output .= '<td>';
			}

			$post_day_link = '';
			if ( $regular_posts ) {
				$post_day_link = get_day_link( $thisyear , $thismonth , $day );
			} elseif ( null !== $custom_post_type && $day ) {
				$post_day_link = get_site_url() . '/' . $custom_post_type->has_archive . '/' . $thisyear . '/' . $thismonth . '/' . $day . '/';
			}

			if ( in_array( $day, $daywithpost, false ) ) { // any posts today?
				$calendar_output .= '<a href="' . $post_day_link . "\" title=\"" . esc_attr( $ak_titles_for_day[ $day ] ) . "\">$day</a>";
			} else {
				$calendar_output .= $day;
			}
			$calendar_output .= '</td>';

			if ( 6 === (int) calendar_week_mod( date( 'w', mktime( 0, 0, 0, $thismonth, $day, $thisyear ) ) - $week_begins ) ) {
				$newrow = true;
			}
		}

		$pad = 7 - calendar_week_mod( date( 'w', mktime( 0, 0, 0, $thismonth, $day, $thisyear ) ) - $week_begins );
		if ( (int) $pad !== 0 && (int) $pad !== 7 ) {
			$calendar_output .= "\n\t\t" . '<td class="pad" colspan="' . esc_attr( $pad ) . '">&nbsp;</td>';
		}

		$calendar_output .= "\n\t</tr>\n\t</tbody>\n\t</table>";

		$cache[ $key ] = $calendar_output;
		wp_cache_set( 'rpr_calendar', $cache, 'calendar' );

		if ( ! $echo ) {
			return $calendar_output;
		}

		echo $calendar_output;

		return true;
	}

}
