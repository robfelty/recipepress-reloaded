<?php
/**
 * @var RPR_Social_Media_Sharing $_this
 */

use Recipepress\Inc\Common\Utilities\Icons;

$selected_buttons  = explode( ',', strtolower( $_this->get_setting( 'social_media_sites' ) ) );
$available_buttons = array();
$button_color      = $_this->get_setting( 'buttons_color' ) ?: 'transparent';


$mobile_share  = '';
$mobile_share .= '<div class="rpr-share-button">';
$mobile_share .= '<a ';
$mobile_share .= 'style="background: ' . $button_color . '">';
$mobile_share .= '<i class="rpr-icon icon-share"></i>';
$mobile_share .= '<p>' . __( 'Share', 'recipepress-reloaded' ) . '</p>';
$mobile_share .= '</a>';
$mobile_share .= '</div>'; // .rpr-share-button

$available_buttons['mobile_share'] = $mobile_share;


if ( in_array( 'facebook', $selected_buttons, true ) ) {
	$facebook  = '';
	$facebook .= '<div class="rpr-share rpr-facebook-button" data-social="facebook">';
	$facebook .= '<a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( get_the_permalink() ) . '" ';
	$facebook .= 'title="' . __( 'Share on Facebook', 'recipepress-reloaded' ) . '" ';
	$facebook .= 'rel="noopener" ';
	$facebook .= 'onclick="socialShare(this);window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;" ';
	$facebook .= 'style="background: ' . $button_color . '">';
	$facebook .= Icons::get_the_icon( 'facebook' );
	$facebook .= '<p>' . __( 'Facebook', 'recipepress-reloaded' ) . '</p>';
	$facebook .= '</a>';
	$facebook .= '</div>'; // .rpr-facebook-button

	$available_buttons['facebook'] = $facebook;
}

if ( in_array( 'twitter', $selected_buttons, true ) ) {
	$twitter  = '';
	$twitter .= '<div class="rpr-share rpr-twitter-button" data-social="twitter">';
	$twitter .= '<a target="_blank" href="https://twitter.com/intent/tweet?url=' . rawurlencode( get_the_permalink() );
	$twitter .= '&text=' . apply_filters( 'rpr_social_twitter_text', rawurlencode( get_the_title( $_this->the_recipe_id() ) ) );
	$twitter .= '&via=' . esc_attr( $_this->get_setting( 'twitter_username' ) ) . '" ';
	$twitter .= 'onclick="window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;" ';
	$twitter .= 'title="' . __( 'Share on Twitter', 'recipepress-reloaded' ) . '" ';
	$twitter .= 'rel="noopener" ';
	$twitter .= 'style="background: ' . $button_color . '">';
	$twitter .= Icons::get_the_icon( 'twitter' );
	$twitter .= '<p>' . __( 'Twitter', 'recipepress-reloaded' ) . '</p>';
	$twitter .= '</a>';
	$twitter .= '</div>'; // .rpr-twitter-button

	$available_buttons['twitter'] = $twitter;
}

if ( in_array( 'google+', $selected_buttons, true ) ) {
	$google  = '';
	$google .= '<div class="rpr-share rpr-google-button" data-social="google">';
	$google .= '<a target="_blank" href="https://plus.google.com/share?url=' . rawurlencode( get_the_permalink() ) . '" ';
	$google .= 'onclick="window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;" ';
	$google .= 'rel="noopener" ';
	$google .= 'style="background: ' . $button_color . '">';
	$google .= Icons::get_the_icon( 'gplus' );
	$google .= '<p>' . __( 'Google+', 'recipepress-reloaded' ) . '</p>';
	$google .= '</a>';
	$google .= '</div>'; // .rpr-google-button

	$available_buttons['google'] = $google;
}

if ( in_array( 'pinterest', $selected_buttons, true ) ) {
	$description = ( get_post( $_this->the_recipe_id() ) )->post_excerpt ?: get_the_title( $_this->the_recipe_id() );
	$pinterest   = '';
	$pinterest   .= '<div class="rpr-share rpr-pinterest-button" data-social="pinterest">';
	$pinterest   .= '<a data-pin-do="skipLink" target="_blank" href="https://pinterest.com/pin/create/link/?url=' . rawurlencode( get_the_permalink() );
	$pinterest   .= '&media=' . get_the_post_thumbnail_url( $_this->the_recipe_id(), 'full' );
	$pinterest   .= '&description=' . rawurlencode( $description ) . '" ';
	$pinterest   .= 'onclick="window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;" ';
	$pinterest   .= 'title="' . __( 'Share on Pinterest', 'recipepress-reloaded' ) . '" ';
	$pinterest   .= 'rel="noopener" ';
	$pinterest   .= 'style="background: ' . $button_color . '">';
	$pinterest   .= Icons::get_the_icon( 'pinterest' );
	$pinterest   .= '<p>' . __( 'Pinterest', 'recipepress-reloaded' ) . '</p>';
	$pinterest   .= '</a>';
	$pinterest   .= '</div>'; // .rpr-pinterest-button

	$available_buttons['pinterest'] = $pinterest;
}

if ( in_array( 'mix', $selected_buttons, true ) ) {
	$mix  = '';
	$mix .= '<div class="rpr-share rpr-mix-button" data-social="mix">';
	$mix .= '<a href="https://mix.com/mixit?url=' . rawurlencode( get_the_permalink() ) . '" ';
	$mix .= 'onclick="window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;" ';
	$mix .= 'title="' . __( 'Share on Mix', 'recipepress-reloaded' ) . '" ';
	$mix .= 'rel="noopener" ';
	$mix .= 'style="background: ' . $button_color . '">';
	$mix .= Icons::get_the_icon( 'mix' );
	$mix .= '<p>' . __( 'Mix', 'recipepress-reloaded' ) . '</p>';
	$mix .= '</a>';
	$mix .= '</div>'; // .rpr-stumble-button

	$available_buttons['mix'] = $mix;
}

if ( in_array( 'tumblr', $selected_buttons, true ) ) {
	$tumblr  = '';
	$tumblr .= '<div class="rpr-share rpr-tumblr-button" data-social="tumblr">';
	$tumblr .= '<a href="https://www.tumblr.com/widgets/share/tool?canonicalUrl=' . rawurlencode( get_the_permalink() );
	$tumblr .= '&title=' . rawurlencode( get_the_title( $_this->the_recipe_id() ) );
	$tumblr .= '&caption=' . wp_strip_all_tags( get_the_excerpt( $_this->the_recipe_id() ) ) . '" ';
	$tumblr .= 'onclick="window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;" ';
	$tumblr .= 'title="' . __( 'Share on Tumblr', 'recipepress-reloaded' ) . '" ';
	$tumblr .= 'rel="noopener" ';
	$tumblr .= 'style="background: ' . $button_color . '">';
	$tumblr .= Icons::get_the_icon( 'tumblr' );
	$tumblr .= '<p>' . __( 'Tumblr', 'recipepress-reloaded' ) . '</p>';
	$tumblr .= '</a>';
	$tumblr .= '</div>';

	$available_buttons['tumblr'] = $tumblr;
}

if ( in_array( 'reddit', $selected_buttons, true ) ) {
	$reddit  = '';
	$reddit .= '<div class="rpr-share rpr-reddit-button" data-social="reddit">';
	$reddit .= '<a href="https://reddit.com/submit?url=' . rawurlencode( get_the_permalink() );
	$reddit .= '&title=' . rawurlencode( get_the_title( $_this->the_recipe_id() ) ) . '" ';
	$reddit .= 'onclick="window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;" ';
	$reddit .= 'title="' . __( 'Share on Reddit', 'recipepress-reloaded' ) . '" ';
	$reddit .= 'rel="noopener" ';
	$reddit .= 'style="background: ' . $button_color . '">';
	$reddit .= Icons::get_the_icon( 'reddit-alien' );
	$reddit .= '<p>' . __( 'Reddit', 'recipepress-reloaded' ) . '</p>';
	$reddit .= '</a>';
	$reddit .= '</div>';

	$available_buttons['reddit'] = $reddit;
}

if ( in_array( 'pocket', $selected_buttons, true ) ) {
	$pocket  = '';
	$pocket .= '<div class="rpr-share rpr-pocket-button"  data-social="pocket">';
	$pocket .= '<a href="https://getpocket.com/edit?url=' . rawurlencode( get_the_permalink() ) . '" ';
	$pocket .= 'onclick="window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;" ';
	$pocket .= 'title="' . __( 'Save to Pocket', 'recipepress-reloaded' ) . '" ';
	$pocket .= 'rel="noopener" ';
	$pocket .= 'style="background: ' . $button_color . '">';
	$pocket .= Icons::get_the_icon( 'get-pocket' );
	$pocket .= '<p>' . __( 'Pocket', 'recipepress-reloaded' ) . '</p>';
	$pocket .= '</a>';
	$pocket .= '</div>';

	$available_buttons['pocket'] = $pocket;
}

if ( in_array( 'digg', $selected_buttons, true ) ) {
	$digg  = '';
	$digg .= '<div class="rpr-share rpr-digg-button" data-social="digg">';
	$digg .= '<a href="http://digg.com/submit?url=' . rawurlencode( get_the_permalink() ) . '" ';
	$digg .= 'onclick="window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;" ';
	$digg .= 'title="' . __( 'Share on Digg', 'recipepress-reloaded' ) . '" ';
	$digg .= 'rel="noopener" ';
	$digg .= 'style="background: ' . $button_color . '">';
	$digg .= Icons::get_the_icon( 'digg' );
	$digg .= '<p>' . __( 'Digg', 'recipepress-reloaded' ) . '</p>';
	$digg .= '</a>';
	$digg .= '</div>';

	$available_buttons['digg'] = $digg;
}

if ( in_array( 'instapaper', $selected_buttons, true ) ) {
	$instapaper  = '';
	$instapaper .= '<div class="rpr-share rpr-instapaper-button" data-social="instapaper">';
	$instapaper .= '<a href="http://www.instapaper.com/edit?url=' . rawurlencode( get_the_permalink() );
	$instapaper .= '&title=' . rawurlencode( get_the_title( $_this->the_recipe_id() ) );
	$instapaper .= '&description=' . rawurlencode( get_the_excerpt( $_this->the_recipe_id() ) ) . '" ';
	$instapaper .= 'onclick="window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;" ';
	$instapaper .= 'title="' . __( 'Share on Instapaper', 'recipepress-reloaded' ) . '" ';
	$instapaper .= 'rel="noopener" ';
	$instapaper .= 'style="background: ' . $button_color . '">';
	$instapaper .= Icons::get_the_icon( 'instapaper' );
	$instapaper .= '<p>' . __( 'InstaPaper', 'recipepress-reloaded' ) . '</p>';
	$instapaper .= '</a>';
	$instapaper .= '</div>';

	$available_buttons['instapaper'] = $instapaper;
}

if ( in_array( 'buffer', $selected_buttons, true ) ) {
	$buffer  = '';
	$buffer .= '<div class="rpr-share rpr-buffer-button"  data-social="buffer">';
	$buffer .= '<a href="https://buffer.com/add?text=' . rawurlencode( get_the_title( $_this->the_recipe_id() ) );
	$buffer .= '&url=' . rawurlencode( get_the_permalink() ) . '" ';
	$buffer .= 'onclick="window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;" ';
	$buffer .= 'title="' . __( 'Share on Buffer', 'recipepress-reloaded' ) . '" ';
	$buffer .= 'rel="noopener" ';
	$buffer .= 'style="background: ' . $button_color . '">';
	$buffer .= Icons::get_the_icon( 'buffer' );
	$buffer .= '<p>' . __( 'Buffer', 'recipepress-reloaded' ) . '</p>';
	$buffer .= '</a>';
	$buffer .= '</div>';

	$available_buttons['buffer'] = $buffer;
}

if ( in_array( 'email', $selected_buttons, true ) ) {
	$email  = '';
	$email .= '<div class="rpr-share rpr-email-button"  data-social="email">';
	$email .= '<a href="mailto:?subject=' . rawurlencode( get_the_title( $_this->the_recipe_id() ) )
			. '&body=' . rawurlencode( wp_strip_all_tags( get_the_excerpt( $_this->the_recipe_id() ) ) . "\n\n" )
			. rawurlencode( __( 'Get the Full Recipe Here', 'recipepress-reloaded' ) . ': ' )
			. rawurlencode( get_the_permalink( $_this->the_recipe_id() ) ) . '" ';
	$email .= 'title="' . __( 'Email this recipe', 'recipepress-reloaded' ) . '" ';
	$email .= 'rel="noopener" ';
	$email .= 'style="background: ' . $button_color . '">';
	$email .= Icons::get_the_icon( 'mail-alt' );
	$email .= '<p>' . __( 'Email', 'recipepress-reloaded' ) . '</p>';
	$email .= '</a>';
	$email .= '</div>';

	$available_buttons['email'] = $email;
}

if ( in_array( 'print', $selected_buttons, true ) ) {
	$print  = '';
	$print .= '<div class="rpr-share rpr-print-button"  data-social="print">';
	$print .= '<a href="#" ';
	$print .= 'title="' . __( 'Print this recipe', 'recipepress-reloaded' ) . '" ';
	$print .= 'rel="noopener" ';
	$print .= 'style="background: ' . $button_color . '">';
	$print .= Icons::get_the_icon( 'print' );
	$print .= '<p>' . __( 'Print', 'recipepress-reloaded' ) . '</p>';
	$print .= '</a>';
	$print .= '</div>';

	$available_buttons['print'] = $print;
}

if ( in_array( 'yummly', $selected_buttons, true ) ) {
	$yummly  = '';
	$yummly .= '<div class="rpr-share rpr-yummly-button"  data-social="yummly">';
	$yummly .= '<a href="https://www.yummly.com/urb/verify?url=' . rawurlencode( get_the_permalink( $_this->the_recipe_id() ) );
	$yummly .= '&title=' . rawurlencode( get_the_title( $_this->the_recipe_id() ) );
	$yummly .= '&image=1';
	$yummly .= '&yumtype=button" ';
	$yummly .= 'onclick="window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;" ';
	$yummly .= 'title="' . __( 'Share on Yummly', 'recipepress-reloaded' ) . '" ';
	$yummly .= 'rel="noopener" ';
	$yummly .= 'style="background: ' . $button_color . '">';
	$yummly .= Icons::get_the_icon( 'yummly' );
	$yummly .= '<p>' . __( 'Yummly', 'recipepress-reloaded' ) . '</p>';
	$yummly .= '</a>';
	$yummly .= '</div>';

	$available_buttons['yummly'] = $yummly;
}

if ( in_array( 'flipboard', $selected_buttons, true ) ) {
	$flip  = '';
	$flip .= '<div class="rpr-share rpr-flipboard-button"  data-social="flipboard">';
	$flip .= '<a href="https://share.flipboard.com/bookmarklet/popout?v=2';
	$flip .= '&title=' . rawurlencode( get_the_title( $_this->the_recipe_id() ) );
	$flip .= '&url=' . rawurlencode( get_the_permalink( $_this->the_recipe_id() ) ) . '" ';
	$flip .= 'onclick="window.open(this.href, \'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;" ';
	$flip .= 'title="' . __( 'Share on Flipboard', 'recipepress-reloaded' ) . '" ';
	$flip .= 'rel="noopener" ';
	$flip .= 'style="background: ' . $button_color . '">';
	$flip .= Icons::get_the_icon( 'flipboard' );
	$flip .= '<p>' . __( 'Flipboard', 'recipepress-reloaded' ) . '</p>';
	$flip .= '</a>';
	$flip .= '</div>';

	$available_buttons['flipboard'] = $flip;
}

// Sort the 'available_buttons' array using the 'selected_buttons' array to set the order.
uksort(
	$available_buttons,
	static function( $key1, $key2 ) use ( $selected_buttons ) {
		if ( array_search( $key1, $selected_buttons, true ) === array_search( $key2, $selected_buttons, true ) ) {
			return 0;
		}
		return ( array_search( $key1, $selected_buttons, true ) < array_search( $key2, $selected_buttons, true ) ) ? -1 : 1;
	}
);
