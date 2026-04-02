<?php
/**
 * The public-facing comment rating system of the plugin.
 *
 * @since   1.0.0
 *
 * @package Recipepress
 */

namespace Recipepress\Inc\Frontend;

use Recipepress as NS;
use Recipepress\Inc\Core\Options;

/**
 * The public-facing comments rating system of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package Recipepress
 *
 * @author  wzyMedia <wzy@outlook.com>
 */
class Rating {

	use NS\Inc\Common\Traits\Utilities;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Enable public comment setting.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      bool   $enable_rating
	 */
	private $enable_rating;

	/**
	 * The public rating label, stored in the settings.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string   $enable_rating
	 */
	private $ratings_label;

	/**
	 * The foreground color of the comment rating stars.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string   $star_color
	 */
	private $star_color;

	/**
	 * The background color of the comment rating stars.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string   $star_background
	 */
	private $star_background;

	/**
	 * The comment rating provided by a site visitor.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $rating.
	 */
	private $rating;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of the plugin.
	 * @param      string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->enable_rating   = (bool) Options::get_option( 'rpr_comment_rating' );
		$this->ratings_label   = (string) Options::get_option( 'rpr_comment_rating_label' );
		$this->star_color      = (string) Options::get_option( 'rpr_comment_rating_color' );
		$this->star_background = apply_filters( 'rpr_star_background', '#f0f4f5' );
	}

	/**
	 * Enqueues the public facing stylesheet for the comment ratings.
	 */
	public function enqueue_styles() {

		if ( $this->enable_rating ) {

			wp_enqueue_style( 'rpr-rating-styles', NS\PUB_ASSET_URL . 'css/rpr-rating-styles.css', array(), $this->version );

			$custom_css = '
				.rating .whole .l, .rating .whole .r {
				    fill: ' . $this->star_color . '
				}
				.rating .half .l, .rating .half .r {
				    fill: ' . $this->star_color . '
				}
				.rating .rover .l, .rating .rover .r {
				    fill: ' . $this->star_color . '
				}
			';

			wp_add_inline_style( 'rpr-rating-styles', $custom_css );
		}
	}

    /**
     * Inlines the stylesheet for the comment ratings.
     */
    public function inline_styles() {
        // "★★★★★" "💖💖💖💖💖"
        if ( $this->enable_rating && is_singular( 'rpr_recipe' ) ) {
            $inline_css = '.rpr-stars {--percent:calc(var(--rating) / 5 * 100%); font-size:60px; display:flex; justify-content:center; line-height:1;}
            .rpr-stars::before {content:"★★★★★"; background:linear-gradient(90deg, #f8db00 var(--percent), #f1f1f1 var(--percent)); -webkit-background-clip:text; -webkit-text-fill-color:transparent;
            }';

            wp_add_inline_style( 'recipepress-reloaded', $inline_css );
        }
    }

	/**
	 * Enqueues the public facing scripts for the comment ratings.
	 */
	public function enqueue_scripts() {

		if ( $this->enable_rating && is_singular( 'rpr_recipe' ) ) {
			wp_enqueue_script( 'rpr-rating-scripts', NS\PUB_ASSET_URL . 'js/rpr-rating-scripts.js', array( 'jquery' ), $this->version, true );
		}
	}


	/**
	 * Saves the comment rating data
	 *
	 * This method is used alongside the `wp_insert_comment` action to save the user
	 * comment rating value. This only runs if a user rating is provided.
	 *
	 * @since 1.0.0
	 *
	 * @param int         $id The current comment ID.
	 * @param \WP_Comment $comment The current comment.
	 *
	 * @return void
	 */
	public function add_comment_karma( $id, $comment ) {

		if ( ! isset( $_POST['security_nonce'], $_POST['comment_karma'] ) || ! wp_verify_nonce( $_POST['security_nonce'], 'rpr-rating-nonce' ) ) {
			return;
		}

		$comment_karma = (int) $_POST['comment_karma'];

		if ( $comment_karma > 5 ) {
			$comment_karma = 5;
		}

		if ( $comment_karma <= 0 ) {
			$comment_karma = 1;
		}

		$updated_comment                  = array();
		$updated_comment['comment_ID']    = $id;
		$updated_comment['comment_karma'] = $comment_karma;

		$result = wp_update_comment( $updated_comment );

		// If we have saved a new rating then we update or create a `rpr_rating` meta
		// that stores the number of ratings that a recipe has.
		if ( $result ) {
			$count   = $this->rating_info( 'count', $comment->comment_post_ID );
			$average = $this->rating_info( 'avg', $comment->comment_post_ID );

			update_post_meta( $comment->comment_post_ID, 'rpr_rating_count', $count );
			update_post_meta( $comment->comment_post_ID, 'rpr_rating_average', $average );
		}
	}


	/**
	 * Display the star rating inside the comment form.
	 *
	 * @return string|bool
	 */
	public function comment_rating_form() {

		if ( $this->enable_rating && is_singular( 'rpr_recipe' ) ) {

			//$star = '<li class="empty"><span class="l"></span><span class="r"></span></li>';
			$star = '<li class="empty"><svg xmlns="http://www.w3.org/2000/svg" class="l" fill="#d6d6d6" viewBox="0 0 124.28 236.38"><polygon 
points="93.55 88.38 0 90.28 74.57 146.81 47.47 236.38 124.28 182.93 124.28 0 93.55 88.38"/>
					</svg><svg xmlns="http://www.w3.org/2000/svg" class="r" fill="#d6d6d6" viewBox="0 0 124.27 236.39"><polygon points="0.01 0 0 0.01 0 182.94 0.01 182.94 76.81 236.39 49.72 146.82 124.28 90.29 30.73 88.39 0.01 0"/>
					</svg>
					</li>';

			return printf(
				'<div class="rating-container no-print"><p class="rating-label">%s</p><ul class="rating form-rating">%s</ul>%s</div>',
				esc_html( $this->ratings_label ),
				str_repeat( $star, 5 ), // phpcs:ignore
			    wp_nonce_field( 'rpr-rating-nonce', 'security_nonce', true, false ) // phpcs:ignore
			);
		}

		return false;
	}

	/**
	 * Calculates the raw review score from the comment metadata.
	 *
	 * @since 1.0.0
	 *
	 * @param string $query     The string we are checking for: 'avg' | 'count' | 'min' | 'max'
	 * @param int    $recipe_id The current recipe post ID.
	 *
	 * @return int|float
	 */
	public function rating_info( $query, $recipe_id = null ) {

		$result  = null;
		$ratings = $this->comments_with_ratings( $recipe_id );

		switch ( $query ) {
			case 'avg':
				$result = null !== $ratings ? ( round( array_sum( $ratings ) / count( $ratings ), 1 ) ) : 0;
				break;

			case 'count':
				$result = null !== $ratings ? count( $ratings ) : 0;
				break;

			case 'min':
				$result = null !== $ratings ? (int) min( $ratings ) : 0;
				break;

			case 'max':
				$result = null !== $ratings ? (int) max( $ratings ) : 0;
				break;

			default:
				$result = 0;
		}

		return $result;
	}


	/**
	 * Does the retrieval of comments with an approved rating.
	 *
	 * @since 1.0.0
	 *
	 * @param int $recipe_id The current recipe's post ID.
	 *
	 * @return array
	 */
	private function comments_with_ratings( $recipe_id ) {

		$comments = get_comments(
			array(
				'post_id' => (int) $recipe_id,
				'status'  => 'approve',
			)
		);

		$karma_scores = array();

		foreach ( $comments as $comment ) {
			if ( (int) $comment->comment_karma > 0 ) {
				$karma_scores[] = (int) $comment->comment_karma;
			}
		}

		return $karma_scores ?: null;
	}


	/**
	 * Does the actual rendering of the 5-star rating.
	 *
	 * @since 1.0.0
	 *
	 * @param int  $recipe_id  The current recipe's post ID.
	 * @param bool $is_comment Is this a comment?.
	 *
	 * @return array|string
	 */
	public function rate_calculate( $recipe_id = null, $is_comment = null ) {

		$post_id    = (int) $recipe_id ?: get_the_ID();
		$comment_id = 0;

		if ( $is_comment ) {
			$c            = $GLOBALS['comment'];
			$this->rating = (float) $c->comment_karma;
			$comment_id   = (int) $c->comment_ID;

			if ( ! $this->rating ) {
				return null; // TODO: Check this else block.
			}
		} else {
			$this->rating = (float) $this->rating_info( 'avg', $post_id );
		}

		$this->rating = number_format( $this->rating, 1, '.', '' );

		if ( 0.0 === $this->rating ) {
			$coerced_rating = 0.0;
		} elseif ( 0 !== ( $this->rating * 10 ) % 5 ) {
			$coerced_rating = round( $this->rating * 2.0 ) / 2.0;
		} else {
			$coerced_rating = $this->rating;
		}

		$stars   = array( 0, 1, 2, 3, 4, 5, 6 );
		$classes = array( 'rating' );
		$format = '<li class="%1$s"><svg xmlns="http://www.w3.org/2000/svg" class="left" fill="%2$s" viewBox="0 0 124.28 236.38"><polygon points="93.55 88.38 0 90.28 74.57 146.81 47.47 236.38 124.28 182.93 124.28 0 93.55 88.38"/>
					</svg><svg xmlns="http://www.w3.org/2000/svg" class="right" fill="%3$s" viewBox="0 0 124.27 236.39"><polygon points="0.01 0 0 0.01 0 182.94 0.01 182.94 76.81 236.39 49.72 146.82 124.28 90.29 30.73 88.39 0.01 0"/>
					</svg>
					</li>';

		for ( $i = 1; $i <= 5; $i ++ ) {
			if ( $i <= $coerced_rating ) {
				$stars[ $i ] = sprintf( $format, 'whole', $this->star_color, $this->star_color );
			} elseif ( $i - 0.5 === $coerced_rating ) {
				$stars[ $i ] = sprintf( $format, 'half', $this->star_color, $this->star_background );
			} else {
				$stars[ $i ] = sprintf( $format, 'empty', $this->star_background, $this->star_background );
			}
		}

		$meta   = array();
		$meta[] = sprintf( 'data-id="%d"', $post_id );
		$meta[] = $comment_id ? sprintf( 'data-comment-id="%d"', $comment_id ) : null;
		// translators: '3 from 4 reviews'. The average review of a recipe.
		// TODO: "title" should not be translated, it's an HTML attribute.
		$title = $comment_id ? null : sprintf( __( ' title="%1$s from %2$s reviews"', 'recipepress-reloaded' ), $this->rating, $this->rating_info( 'count', $post_id ) );

		if ( 0.0 !== $this->rating ) {
			$stars[0] = sprintf(
				'<div class="rpr star-ratings" %s><ul data-rating="%01.1f" class="%s" %s>',
				$title,
				$this->rating,
				implode( ' ', $classes ),
				implode( ' ', $meta )
			);
			$stars[6] = '</ul></div>';
		}

        return implode( '', $stars );
	}

    /**
     * Return the rendered 5-star rating.
     *
     * @since 2.0.0
     *
     * @param int  $recipe_id  The current recipe's post ID.
     * @param bool $is_comment Is this a comment?.
     *
     * @return string
     */
    public function get_the_star_rating( $recipe_id = null, $is_comment = null ) {
        $stars      = '';
        $comment_id = 0;
        $meta       = array();
        $styles     = array();

        $rating = number_format( $this->rating_info( 'avg', $recipe_id ), 1, '.', '' );

        if ( $is_comment ) {
            $comment    = $GLOBALS['comment'];
            $rating     = $comment->comment_karma;
            $comment_id = $comment->comment_ID;

            if ( ! $rating ) {
                return $stars;
            }
        }

        $meta[] = sprintf( 'data-recipe-id="%d"', $recipe_id );
        $meta[] = $comment_id ? sprintf( 'data-comment-id="%d"', $comment_id ) : '';

        $styles[] = sprintf('--rating:%1$s;', $rating );

        $stars .= sprintf(
            '<div class="rpr-stars" aria-label="%1$s" style="%2$s" %3$s></div>',
            sprintf( __( '%1$s from %2$s reviews', 'recipepress-reloaded' ), $rating, $this->rating_info( 'count', $recipe_id ) ),
            implode( ' ', $styles ),
            implode( ' ', $meta )
        );

        return $stars;
	}

	/**
	 * Displays the recipe rating.
	 *
	 * @since 1.0.0
	 *
	 * @param int $recipe_id The current recipe's post ID.
	 *
	 * @return void
	 */
	public function the_rating( $recipe_id = null ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->rate_calculate( $recipe_id );
	}

	/**
	 * Displays the comment rating
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function the_comment_rating() {
		global $comment;

		return $this->rate_calculate( $comment->comment_post_ID, true );
	}


	/**
	 * Add the star rating above the displayed comment.
	 *
	 * @since 1.0.0
	 *
	 * @param string $comment The rated comment contents.
	 *
	 * @return string
	 */
	public function display_comment_rating( $comment ) {

		if ( $this->enable_rating && is_singular( 'rpr_recipe' ) && ! is_comment_feed() ) {
			$out  = '';
			$out .= $this->the_comment_rating();
			$out .= $comment;

			return $out;
		}

		return $comment;
	}

	/**
	 * Adds a metabox with the comment rating for a recipe to the
	 * comment edit page.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_metabox() {

		$comment_id = 0;

		// phpcs:ignore
		if ( isset( $_GET['action'] ) && 'editcomment' === $_GET['action'] ) {
			$comment_id = isset( $_GET['c'] ) ? (int) $_GET['c'] : 0; // phpcs:ignore
		}

		$comment = get_comment( $comment_id );
		$post_id = $comment ? $comment->comment_post_ID : 0;

		if ( 'rpr_recipe' !== get_post_type( $post_id ) ) {
			return;
		}

		add_meta_box(
			'rpr_recipe_rating',
			__( 'Recipe rating', 'recipepress-reloaded' ),
			array( $this, 'render_metabox' ),
			'comment',
			'normal'
		);
	}

	/**
	 * Renders the contents of our comment rating metabox
	 * on the comment edit page.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Comment $comment The current comment object.
	 *
	 * @return void
	 */
	public function render_metabox( $comment ) {

		wp_nonce_field( 'rpr_recipe_rating', 'rpr_recipe_rating_nonce' );

		$rating = $comment->comment_karma;
		echo "<input type='number' id='rpr_recipe_comment_rating' name='rpr_recipe_comment_rating' value='"
			. esc_attr( $rating ) . "' size='25' min='0' max='5' />";
	}

	/**
	 * Saves the comment rating on the comment edit page.
	 *
	 * @since 1.0.0
	 *
	 * @param string $location   The URL we are redirecting to on save.
	 * @param int    $comment_id The current comment ID.
	 *
	 * @return string
	 */
	public function save_comment_rating( $location, $comment_id ) {

		if ( ! isset( $_POST['rpr_recipe_comment_rating'] )
			 && ! wp_verify_nonce( $_POST['rpr_recipe_rating_nonce'], 'rpr_recipe_rating' ) ) {
			return $location;
		}

		// Update meta.
		$comment                  = array();
		$comment['comment_ID']    = $comment_id;
		$comment['comment_karma'] = (int) $_POST['rpr_recipe_comment_rating'];

		$result = wp_update_comment( $comment );

		// If we have saved a new rating then we update or create a `rpr_rating` meta
		// that stores the number of ratings that a recipe has.
		if ( $result ) {
			$recipe_id = isset( $_POST['comment_post_ID'] ) ? (int) $_POST['comment_post_ID'] : 0;
			$count     = $this->rating_info( 'count', $recipe_id );
			$avg       = $this->rating_info( 'avg', $recipe_id );

			update_post_meta( $recipe_id, 'rpr_rating_count', $count );
			update_post_meta( $recipe_id, 'rpr_rating_average', $avg );
		}

		// Return regular value after updating.
		return $location;
	}

	/**
	 * Adds recipe rating to the edit comments admin table.
	 *
	 * @since 1.0.0
	 *
	 * @see   https://stackoverflow.com/a/3354804/3513481
	 * @param array $columns An array of the columns in the admin reviews page.
	 *
	 * @return array
	 */
	public function add_comment_rating_column( $columns ) {

		if ( Options::get_option( 'rpr_comment_rating' ) ) {
			$columns = array_slice( $columns, 0, 3, true )
					+ array( 'rpr_rating' => __( 'Rating', 'recipepress-reloaded' ) )
					+ array_slice( $columns, 3, count( $columns ) - 3, true );
		}

		return $columns;
	}

	/**
	 * Adds the recipe rating to the comment admin columns
	 *
	 * @since 1.0.0
	 *
	 * @param array $column_name The array key and usually the name of the column.
	 * @param int   $comment_id  The comment ID of each comment listed in the admin column.
	 *
	 * @return void
	 */
	public function add_rating_to_column( $column_name, $comment_id ) {

		$out     = '';
		$comment = get_comment( $comment_id );

		if ( 'rpr_rating' === $column_name ) {
			$out .= '<div style="width: 60px; display: inline-block; font-size: 14px;">';
			$out .= $this->html_stars( $comment->comment_karma, true );
			$out .= '</div>';
		}

		echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Adds recipe rating to the posts admin table.
	 *
	 * @since 1.0.0
	 *
	 * @see   https://stackoverflow.com/a/3354804/3513481
	 * @param array $columns An array of the columns in the admin reviews page.
	 *
	 * @return array
	 */
	public function add_rating_posts_columns( $columns ) {

		if ( Options::get_option( 'rpr_comment_rating' ) ) {
			$columns['rpr_rating'] = __( 'Rating', 'recipepress-reloaded' );
		}

		return $columns;
	}

	/**
	 * Adds the recipe rating to the posts admin columns
	 *
	 * @since 1.0.0
	 *
	 * @param array $column_name The array key and usually the name of the column.
	 * @param int   $recipe_id   The post ID of a recipe.
	 *
	 * @return void
	 */
	public function add_rating_posts_column( $column_name, $recipe_id ) {

		$out   = '';
		$count = $this->rating_info( 'count', $recipe_id );
		$avg   = $this->rating_info( 'avg', $recipe_id );

		if ( 'rpr_rating' === $column_name ) {
			$out .= '<div style="width: 60px; display: inline-block; font-size: 14px;" ';
			// translators: "2 from 75 ratings".
			$out .= 'title="' . sprintf( __( '%1$s from %2$s ratings', 'recipepress-reloaded' ), round( $avg, 2 ), $count ) . '" >';
			$out .= $this->html_stars( (int) $avg, false );
			$out .= '</div>';
		}

		echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Enables sorting by the number of recipe rating
	 *
	 * @since 1.9.0
	 *
	 * @param array $columns An array of the columns in the admin reviews page.
	 *
	 * @return array
	 */
	public function sort_by_recipe_rating( $columns ) {

		$columns['rpr_rating'] = 'rpr_rating';

		return $columns;
	}

	/**
	 * Sorts the WP admin recipes table by the number of ratings
	 *
	 * @since 1.9.0
	 *
	 * @param \WP_Query $query The main query
	 *
	 * @return void
	 */
	public function order_by_recipe_rating( $query ) {

		if ( is_admin() ) {

			$screen = function_exists( 'get_current_screen' ) ? \get_current_screen() : null;

			if ( $screen && 'edit-rpr_recipe' === $screen->id && 'rpr_rating' === $query->get( 'orderby' ) ) {
				$query->set( 'meta_key', 'rpr_rating_count' );
				$query->set( 'orderby', 'meta_value_num' );
			}
		}
	}

    /**
     * @param \WP_Post  $post
     * @param \WP_Query $query
     *
     * @return object
     */
    public function filter_recipe_title( $post, $query ) {

        if ( ( 'rpr_recipe' === $post->post_type ) && ! is_admin() && in_the_loop() && Options::get_option( 'rpr_recipe_rating' )
            && is_singular( 'rpr_recipe' ) ) {

            add_filter( 'the_title', function( $title, $recipe_id ){
                if ( '0' ===  Options::get_option( 'rpr_recipe_rating_location' ) ) {
                    $title = '<div class="rpr-the-title">' . $this->rate_calculate( $recipe_id ) . '</div>' . $title;
                }
                if ( '1' ===  Options::get_option( 'rpr_recipe_rating_location' ) ) {
                    $title .= '<div class="rpr-the-title">' . $this->rate_calculate( $recipe_id ) . '</div>';
                }

                return $title;
            }, 10, 2 );
        }

        return $post;
	}

}
