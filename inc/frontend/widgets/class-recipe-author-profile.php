<?php
/**
 * Registers the Author Profile widget
 *
 * @link    https://wzymedia.com
 *
 * @since   1.0.0
 *
 * @package Recipepress
 */

namespace Recipepress\Inc\Frontend\Widgets;

use Recipepress\Inc\Core\Options;
use Recipepress\Inc\Common\Utilities\Icons;

/**
 * Add a widget for display the blog's author profile
 *
 * @since   1.0.0
 *
 * @package Recipepress
 * @author  wzyMedia <wzy@outlook.com>
 */
class Recipe_Author_Profile extends \WP_Widget {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 */
	public function __construct() {

		$this->set_widget_options();

		// Create the widget.
		parent::__construct(
			'rpr-author-profile',
			__( 'RPR Author Profile', 'recipepress-reloaded' ),
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
			'classname'   => 'rpr-author-profile',
			'description' => esc_html__( 'Add a widget to display the blog\'s author profile information', 'recipepress-reloaded' ),
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

		if ( Options::get_option( 'rpr_recipe_profile_widget' ) ) { // TODO: this needs to be added.

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

		// Get each link.
		$links = array(
			'facebook'  => esc_attr( $instance['facebook_link'] ),
			'twitter'   => esc_attr( $instance['twitter_link'] ),
			'yummly'    => esc_attr( $instance['yummly_link'] ),
			'pinterest'  => esc_attr( $instance['pinterest_link'] ),
			'youtube'   => esc_attr( $instance['youtube_link'] ),
			'instagram' => esc_attr( $instance['instagram_link'] ),
		);

		// Get each color.
		$colors = array(
			'facebook'  => esc_attr( $instance['facebook_color'] ),
			'twitter'   => esc_attr( $instance['twitter_color'] ),
			'yummly'    => esc_attr( $instance['yummly_color'] ),
			'pinterest'  => esc_attr( $instance['pinterest_color'] ),
			'youtube'   => esc_attr( $instance['youtube_color'] ),
			'instagram' => esc_attr( $instance['instagram_color'] ),
		);

		$size         = isset( $instance['icon_size'] ) ? (int) $instance['icon_size'] : 30;
		$photo_size   = isset( $instance['photo_size'] ) ? (int) $instance['photo_size'] : 250;
		$title        = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$image        = isset( $instance['image'] ) ? esc_attr( $instance['image'] ) : '';
		$profile_link = isset( $instance['profile_link'] ) ? esc_attr( $instance['profile_link'] ) : '';
		$description  = isset( $instance['description'] ) ? esc_attr( $instance['description'] ) : '';
		$hide_header  = isset( $instance['hide_header'] ) ? (bool) $instance['hide_header'] : true;

		echo $args['before_widget']; // phpcs:ignore

		// Call frontend display function.
		$this->displaySML( $links, $colors, $size, $title, $image, $profile_link, $description, $hide_header, $photo_size );

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

		$instance = array(
			'title'           => ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : 'About Me',
			'image'           => ( ! empty( $new_instance['image'] ) ) ? wp_strip_all_tags( $new_instance['image'] ) : '',
			'profile_link'    => ( ! empty( $new_instance['profile_link'] ) ) ? wp_strip_all_tags( $new_instance['profile_link'] ) : '',
			'description'     => ( ! empty( $new_instance['description'] ) ) ? wp_strip_all_tags( $new_instance['description'] ) : '',
			'facebook_link'   => ( ! empty( $new_instance['facebook_link'] ) ) ? wp_strip_all_tags( $new_instance['facebook_link'] ) : '',
			'facebook_color'  => ( ! empty( $new_instance['facebook_color'] ) ) ? wp_strip_all_tags( $new_instance['facebook_color'] ) : '#3b5998',
			'twitter_link'    => ( ! empty( $new_instance['twitter_link'] ) ) ? wp_strip_all_tags( $new_instance['twitter_link'] ) : '',
			'twitter_color'   => ( ! empty( $new_instance['twitter_color'] ) ) ? wp_strip_all_tags( $new_instance['twitter_color'] ) : '#4099ff',
			'yummly_link'     => ( ! empty( $new_instance['yummly_link'] ) ) ? wp_strip_all_tags( $new_instance['yummly_link'] ) : '',
			'yummly_color'    => ( ! empty( $new_instance['yummly_color'] ) ) ? wp_strip_all_tags( $new_instance['yummly_color'] ) : '#e16120',
			'pinterest_link'  => ( ! empty( $new_instance['pinterest_link'] ) ) ? wp_strip_all_tags( $new_instance['pinterest_link'] ) : '',
			'pinterest_color' => ( ! empty( $new_instance['pinterest_color'] ) ) ? wp_strip_all_tags( $new_instance['pinterest_color'] ) : '#007bb5',
			'youtube_link'    => ( ! empty( $new_instance['youtube_link'] ) ) ? wp_strip_all_tags( $new_instance['youtube_link'] ) : '',
			'youtube_color'   => ( ! empty( $new_instance['youtube_color'] ) ) ? wp_strip_all_tags( $new_instance['youtube_color'] ) : '#c62e33',
			'instagram_link'  => ( ! empty( $new_instance['instagram_link'] ) ) ? wp_strip_all_tags( $new_instance['instagram_link'] ) : '',
			'instagram_color' => ( ! empty( $new_instance['instagram_color'] ) ) ? wp_strip_all_tags( $new_instance['instagram_color'] ) : '#517fa4',
			'icon_size'       => ( ! empty( $new_instance['icon_size'] ) ) ? wp_strip_all_tags( $new_instance['icon_size'] ) : '40',
			'photo_size'      => ( ! empty( $new_instance['photo_size'] ) ) ? wp_strip_all_tags( $new_instance['photo_size'] ) : '240',
			'hide_header'     => ! empty( $new_instance['hide_header'] ) ? true : false,
		);

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

		// Get widget title.
		if ( isset( $instance['title'] ) ) {
			$title = esc_attr( $instance['title'] );
		}
		// Get widget image.
		if ( isset( $instance['image'] ) ) {
			$image = esc_attr( $instance['image'] );
		}
		// Get profile link page.
		if ( isset( $instance['profile_link'] ) ) {
			$profile_link = esc_attr( $instance['profile_link'] );
		}
		// Get widget description.
		if ( isset( $instance['description'] ) ) {
			$description = esc_attr( $instance['description'] );
		}
		// Get Facebook link.
		if ( isset( $instance['facebook_link'] ) ) {
			$facebook_link = esc_attr( $instance['facebook_link'] );
		}
		// Get Facebook Icon Color.
		if ( isset( $instance['facebook_color'] ) ) {
			$facebook_color = esc_attr( $instance['facebook_color'] );
		}

		// Get Twitter link.
		if ( isset( $instance['twitter_link'] ) ) {
			$twitter_link = esc_attr( $instance['twitter_link'] );
		}
		// Get Twitter Icon color.
		if ( isset( $instance['twitter_color'] ) ) {
			$twitter_color = esc_attr( $instance['twitter_color'] );
		}

		// Get Yummly link.
		if ( isset( $instance['yummly_link'] ) ) {
			$yummly_link = esc_attr( $instance['yummly_link'] );
		}
		// Get yummly+ Icon Color.
		if ( isset( $instance['yummly_color'] ) ) {
			$yummly_color = esc_attr( $instance['yummly_color'] );
		}

		// Get pinterest link.
		if ( isset( $instance['pinterest_link'] ) ) {
			$pinterest_link = esc_attr( $instance['pinterest_link'] );
		}
		// Get pinterest Icon Color.
		if ( isset( $instance['pinterest_color'] ) ) {
			$pinterest_color = esc_attr( $instance['pinterest_color'] );
		}

		// Get youtube link.
		if ( isset( $instance['youtube_link'] ) ) {
			$youtube_link = esc_attr( $instance['youtube_link'] );
		}
		// Get youtube Icon Color.
		if ( isset( $instance['youtube_color'] ) ) {
			$youtube_color = esc_attr( $instance['youtube_color'] );
		}

		// Get Instagram link.
		if ( isset( $instance['instagram_link'] ) ) {
			$instagram_link = esc_attr( $instance['instagram_link'] );
		}
		// Get Instagram Icon Color.
		if ( isset( $instance['instagram_color'] ) ) {
			$instagram_color = esc_attr( $instance['instagram_color'] );
		}

		// Get Icon Size.
		if ( isset( $instance['icon_size'] ) ) {
			$icon_size = esc_attr( $instance['icon_size'] );
		}

		// Get Photo Size.
		if ( isset( $instance['photo_size'] ) ) {
			$photo_size = esc_attr( $instance['photo_size'] );
		}

		// Hide the widget header.
		$hide_header = isset( $instance['hide_header'] ) ? $instance['hide_header'] : 0;
		?>

		<div class="rpr author-profile">
			<div class="rpr author-profile title">
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Widget Title', 'recipepress-reloaded' ); ?></label>
				<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ) ?>"
					   value="<?php echo esc_attr( ! empty( $title ) ? $title : 'About Me' ); ?>">
			</div>

			<div class="rpr author-profile photo">
				<label for="<?php echo $this->get_field_id( 'image' ); ?>">
					<?php esc_html_e( 'Profile Image', 'recipepress-reloaded' ); ?>
					<div class="photo-container" style="width:100%; display:flex; justify-content:center;">
						<div class="image" style="width:250px; height:250px; background-color:#ccc; overflow:hidden; display:flex; justify-content:center; align-items:center">
							<?php
							if ( ! empty( $image ) ) {
								echo "<img class='image-file saved' src='{$image}' />";
							}
							?>
						</div>
					</div>
				</label>
				<input type="hidden" class="image-file" id="<?php echo $this->get_field_id( 'image' ); ?>" name="<?php echo $this->get_field_name( 'image' ) ?>"
					   value="<?php echo esc_attr( ! empty( $image ) ? $image : '' ); ?>">
			</div>

			<div  class="rpr author-profile link">
				<label for="<?php echo $this->get_field_id( 'profile_link' ); ?>"><?php esc_html_e( 'Profile Link', 'recipepress-reloaded' ); ?></label>
				<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'profile_link' ); ?>" name="<?php echo $this->get_field_name( 'profile_link' ) ?>"
					   value="<?php echo esc_attr( ! empty( $profile_link ) ? $profile_link : '' ); ?>">
			</div>

			<div class="rpr author-profile bio">
				<label for="<?php echo $this->get_field_id( 'description' ); ?>"><?php esc_html_e( 'Profile Description', 'recipepress-reloaded' ); ?></label>
				<textarea class="widefat" name="<?php echo $this->get_field_name( 'description' ) ?>" id="<?php echo $this->get_field_id( 'description' ); ?>" cols="32"
						  rows="5"><?php echo esc_attr( ! empty( $description ) ? $description : '' ); ?></textarea>
			</div>

			<div class="rpr author-profile facebook">
				<label for="<?php echo $this->get_field_id( 'facebook_link' ); ?>"><?php esc_html_e( 'Facebook Profile Link', 'recipepress-reloaded' ); ?></label>
				<div class="rpr author-profile wrapper">
					<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'facebook_link' ); ?>" name="<?php echo $this->get_field_name( 'facebook_link' ) ?>"
						   value="<?php echo esc_attr( ! empty( $facebook_link ) ? $facebook_link : '' ); ?>">

					<input class="facebook-color" type="color" name="<?php echo $this->get_field_name( 'facebook_color' ); ?>"
						   id="<?php echo $this->get_field_id( 'facebook_color' ); ?>" value="<?php echo( ! empty( $facebook_color ) ? $facebook_color : '#3b5998' ); ?>" data-default="#3b5998">
					<span class="reset"><?php _e( 'Reset', 'recipepress-reloaded' ); ?></span>
				</div>
			</div>

			<div  class="rpr author-profile twitter">
				<label for="<?php echo $this->get_field_id( 'twitter_link' ); ?>"><?php esc_html_e( 'Twitter Profile Link', 'recipepress-reloaded' ); ?></label>
				<div class="rpr author-profile wrapper">
					<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'twitter_link' ); ?>" name="<?php echo $this->get_field_name( 'twitter_link' ) ?>"
						   value="<?php echo esc_attr( ! empty( $twitter_link ) ? $twitter_link : '' ); ?>">

					<input class="twitter-color" type="color" name="<?php echo $this->get_field_name( 'twitter_color' ); ?>"
						   id="<?php echo $this->get_field_id( 'twitter_color' ); ?>" value="<?php echo( ! empty( $twitter_color ) ? $twitter_color : '#4099ff' ); ?>" data-default="#4099ff">
					<span class="reset"><?php _e( 'Reset', 'recipepress-reloaded' ); ?></span>
				</div>
			</div>

			<div  class="rpr author-profile yummly">
				<label for="<?php echo $this->get_field_id( 'yummly_link' ); ?>"><?php esc_html_e( 'Yummly Profile Link', 'recipepress-reloaded' ); ?></label>
				<div class="rpr author-profile wrapper">
					<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'yummly_link' ); ?>" name="<?php echo $this->get_field_name( 'yummly_link' ) ?>"
						   value="<?php echo esc_attr( ! empty( $yummly_link ) ? $yummly_link : '' ); ?>">

					<input class="yummly-color" type="color" name="<?php echo $this->get_field_name( 'yummly_color' ); ?>"
						   id="<?php echo $this->get_field_id( 'yummly_color' ); ?>" value="<?php echo( ! empty( $yummly_color ) ? $yummly_color : '#e16120' ); ?>" data-default="#e16120">
					<span class="reset"><?php _e( 'Reset', 'recipepress-reloaded' ); ?></span>
				</div>
			</div>

			<div class="rpr author-profile pinterest">
				<label for="<?php echo $this->get_field_id( 'pinterest_link' ); ?>"><?php esc_html_e( 'Pinterest Profile Link', 'recipepress-reloaded' ); ?></label>
				<div class="rpr author-profile wrapper">
					<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'pinterest_link' ); ?>" name="<?php echo $this->get_field_name( 'pinterest_link' ) ?>"
						   value="<?php echo esc_attr( ! empty( $pinterest_link ) ? $pinterest_link : '' ); ?>">

					<input class="pinterest-color" type="color" name="<?php echo $this->get_field_name( 'pinterest_color' ); ?>"
						   id="<?php echo $this->get_field_id( 'pinterest_color' ); ?>" value="<?php echo( ! empty( $pinterest_color ) ? $pinterest_color : '#007bb5' ); ?>" data-default="#007bb5">
					<span class="reset"><?php _e( 'Reset', 'recipepress-reloaded' ); ?></span>
				</div>
			</div>

			<div class="rpr author-profile youtube">
				<label for="<?php echo $this->get_field_id( 'youtube_link' ); ?>"><?php esc_html_e( 'YouTube Profile Link', 'recipepress-reloaded' ); ?></label>
				<div class="rpr author-profile wrapper">
					<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'youtube_link' ); ?>" name="<?php echo $this->get_field_name( 'youtube_link' ) ?>"
						   value="<?php echo esc_attr( ! empty( $youtube_link ) ? $youtube_link : '' ); ?>">

					<input class="youtube-color" type="color" name="<?php echo $this->get_field_name( 'youtube_color' ); ?>"
						   id="<?php echo $this->get_field_id( 'youtube_color' ); ?>" value="<?php echo( ! empty( $youtube_color ) ? $youtube_color : '#c62e33' ); ?>" data-default="#c62e33">
					<span class="reset"><?php _e( 'Reset', 'recipepress-reloaded' ); ?></span>
				</div>
			</div>

			<div class="rpr author-profile instagram">
				<label for="<?php echo $this->get_field_id( 'instagram_link' ); ?>"><?php esc_html_e( 'Instagram Profile Link', 'recipepress-reloaded' ); ?></label>
				<div class="rpr author-profile wrapper">
					<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'instagram_link' ); ?>" name="<?php echo $this->get_field_name( 'instagram_link' ) ?>"
						   value="<?php echo esc_attr( ! empty( $instagram_link ) ? $instagram_link : '' ); ?>">

					<input class="instagram-color" type="color" name="<?php echo $this->get_field_name( 'instagram_color' ); ?>"
						   id="<?php echo $this->get_field_id( 'instagram_color' ); ?>" value="<?php echo( ! empty( $instagram_color ) ? $instagram_color : '#517fa4' ); ?>" data-default="#517fa4">
					<span class="reset"><?php _e( 'Reset', 'recipepress-reloaded' ); ?></span>
				</div>
			</div>

			<div  class="rpr author-profile size">
				<label for="<?php echo $this->get_field_id( 'icon_size' ); ?>"><?php esc_html_e( 'Icon Size', 'recipepress-reloaded' ); ?></label>
				<input type="number" class="widefat" id="<?php echo $this->get_field_id( 'icon_size' ); ?>" name="<?php echo $this->get_field_name( 'icon_size' ) ?>"
					   value="<?php echo esc_attr( ! empty( $icon_size ) ? $icon_size : '40' ); ?>" min="16" max="128">
			</div>

			<div  class="rpr author-profile photo-size">
				<label for="<?php echo $this->get_field_id( 'photo_size' ); ?>"><?php esc_html_e( 'Photo Size', 'recipepress-reloaded' ); ?></label>
				<input type="number" class="widefat" id="<?php echo $this->get_field_id( 'photo_size' ); ?>" name="<?php echo $this->get_field_name( 'photo_size' ) ?>"
					   value="<?php echo esc_attr( ! empty( $photo_size ) ? $photo_size : '240' ); ?>" min="50" max="500">
			</div>

			<div class="rpr author-profile hide">
				<input type="checkbox" id="<?php echo $this->get_field_id( 'hide_header' ); ?>"
					   name="<?php echo $this->get_field_name( 'hide_header' ) ?>"<?php checked( $hide_header ); ?>/>&nbsp;<label
					for="<?php echo $this->get_field_id( 'hide_header' ); ?>"><?php esc_html_e( 'Hide widget header' ); ?></label>
			</div>
		</div>

		<style>
			.rpr.author-profile div {margin: 1em 0 1em 0;}
			.rpr.author-profile.wrapper {position: relative;}
			.rpr.author-profile.wrapper input[type=color] {position: absolute; bottom: 0; right: -1px; width: 30px; height: 30px; border-radius: 0;}
			.rpr.author-profile.wrapper .reset {position: absolute; bottom: -15px; right: 0; cursor: pointer;}
		</style>

		<?php
	}

	/**
	 * Displays the social media links on the frontend.
	 *
	 * @since 1.0.0
	 *
	 * @param array   $links
	 * @param array   $colors
	 * @param string  $size
	 * @param string  $title
	 * @param string  $image
	 * @param string  $profile_link
	 * @param string  $description
	 * @param boolean $hide_header
	 * @param string  $photo_size
	 *
	 * @return void
	 */
	public function displaySML( $links, $colors, $size, $title, $image, $profile_link, $description, $hide_header, $photo_size ) { ?>

		<div class="rpr-author-profile-widget">
			<?php if ( ! $hide_header ) : ?>
				<h2 class="gp-widget-title widget-title"><?php echo $title; ?></h2>
			<?php endif; ?>
			<?php if ( $image ): ?>
				<div class="profile-image" title="<?php _e( 'Read more of my story', 'recipepress-reloaded' ); ?>">
					<a href="<?php echo esc_url( $profile_link ); ?>" rel="noopener">
						<img width="<?php echo $photo_size; ?>"
							 height="<?php echo $photo_size; ?>"
							 src="<?php echo $image; ?>"
						     style="<?php echo "width: {$photo_size}px; height: {$photo_size}px;"; ?>"
							 alt="<?php _e( 'Profile picture', 'recipepress-reloaded' ); ?>"
							 data-pin-nopin="true" />
					</a>
				</div>
			<?php endif; ?>
			<?php if ( $description ): ?>
				<div class="profile-description">
					<p><?php echo $description . ' <a class="more" href="' . esc_url( $profile_link ) . '" rel="noopener">' . __( 'Read more', 'recipepress-reloaded' ) . '</a>';
					?></p>
				</div>
			<?php endif; ?>
			<div class="social-media-links">
				<?php if ( $links['facebook'] ) : ?>
					<a href="<?php echo $links['facebook']; ?>" rel="noopener" target="_blank" title="<?php _e( 'Follow me on Facebook', 'recipepress-reloaded' ); ?>">
						<div class="icon facebook" style="background: <?php echo $colors['facebook']; ?>; width: <?php echo $size; ?>px; height: <?php echo $size; ?>px;">
							<?php Icons::the_icon( 'facebook' ); ?>
						</div>
					</a>
				<?php endif; ?>
				<?php if ( $links['twitter'] ) : ?>
				<a href="<?php echo $links['twitter']; ?>" rel="noopener" target="_blank" title="<?php _e( 'Follow me on Twitter', 'recipepress-reloaded' ); ?>">
					<div class="icon twitter" style="background: <?php echo $colors['twitter']; ?>; width: <?php echo $size; ?>px; height: <?php echo $size; ?>px;">
						<?php Icons::the_icon( 'twitter' ); ?>
					</div>
				</a>
				<?php endif; ?>
				<?php if ( $links['instagram'] ) : ?>
					<a href="<?php echo $links['instagram']; ?>" rel="noopener" target="_blank" title="<?php _e( 'Follow me on Instagram', 'recipepress-reloaded' ); ?>">
						<div class="icon instagram" style="background: <?php echo $colors['instagram']; ?>; width: <?php echo $size; ?>px; height: <?php echo $size; ?>px;">
							<?php Icons::the_icon( 'instagram' ); ?>
						</div>
					</a>
				<?php endif; ?>
				<?php if ( $links['pinterest'] ) : ?>
					<a href="<?php echo $links['pinterest']; ?>" rel="noopener" target="_blank" title="<?php _e( 'Follow me on Pinterest', 'recipepress-reloaded' ); ?>">
						<div class="icon pinterest" style="background: <?php echo $colors['pinterest']; ?>; width: <?php echo $size; ?>px; height: <?php echo $size; ?>px;">
							<?php Icons::the_icon( 'pinterest' ); ?>
						</div>
					</a>
				<?php endif; ?>
				<?php if ( $links['yummly'] ) : ?>
					<a href="<?php echo $links['yummly']; ?>" rel="noopener" target="_blank" title="<?php _e( 'Follow me on Yummly', 'recipepress-reloaded' ); ?>">
						<div class="icon yummly" style="background: <?php echo $colors['yummly']; ?>; width: <?php echo $size; ?>px; height: <?php echo $size; ?>px;">
							<?php Icons::the_icon( 'yummly' ); ?>
						</div>
					</a>
				<?php endif; ?>
				<?php if ( $links['youtube'] ) : ?>
					<a href="<?php echo $links['youtube']; ?>" rel="noopener" target="_blank" title="<?php _e( 'Follow me on YouTube', 'recipepress-reloaded' ); ?>">
						<div class="icon youtube" style="background: <?php echo $colors['youtube']; ?>; width: <?php echo $size; ?>px; height: <?php echo $size; ?>px;">
							<?php Icons::the_icon( 'youtube-play' ); ?>
						</div>
					</a>
				<?php endif; ?>
			</div>
		</div>

		<?php
	}

}
