<?php
/**
 * This where we are printing the buttons on the frontend.
 *
 * @var array $available_buttons
 * @var RPR_Social_Media_Sharing $_this
 */

require __DIR__ . '/all-buttons.php';

do_action( 'rpr/social_media_sharing_buttons/start' );

$out = '<div class="rpr-share-buttons no-print">';
if ( '' !== $_this->get_setting( 'share_buttons_title' ) ) {
	$out .= '<div class="rpr-share-this">';
	$out .= '<p>' . esc_html( $_this->get_setting( 'share_buttons_title' ) ) . '</p>';
	$out .= '</div>'; // .share-this
}
$out .= '<div class="rpr-share-buttons-list">';

$mobile_buttons = array( 'facebook', 'twitter', 'pinterest', 'yummly', 'email', 'print' );

foreach ( $available_buttons as $key => $output ) {
	if ( apply_filters( 'rpr_share_buttons_mobile', wp_is_mobile() ) ) {
		$out .= in_array( $key, $mobile_buttons, true ) ? $output : null;
	} else {
		$out .= 'mobile_share' === $key ? null : $output;
	}
}

$out .= '</div>'; // .rpr-share-buttons-list
$out .= '</div>'; // .rpr-share-buttons

echo $out; // phpcs:ignore

do_action( 'rpr/social_media_sharing_buttons/end' );
