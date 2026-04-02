<?php
/**
 * The HTML markup of our author box
 *
 * @package Recipepress
 */

use Recipepress\Inc\Common\Utilities\Icons;

$author             = get_the_author();
$author_description = wpautop( get_the_author_meta( 'description' ) );
$author_url         = get_the_author_meta( 'url' );
$author_archive     = get_author_posts_url( get_the_author_meta( 'ID' ) );
$gravatar           = get_avatar_url( get_the_author_meta( 'user_email' ) );

$facebook_url  = get_the_author_meta( 'rpr_facebook' );
$twitter_url   = get_the_author_meta( 'rpr_twitter' );
$pinterest_url = get_the_author_meta( 'rpr_pinterest' );
$linkedin_url  = get_the_author_meta( 'rpr_linkedin' );
$instagram_url = get_the_author_meta( 'rpr_instagram' );
$yummly_url    = get_the_author_meta( 'rpr_yummly' );

$out  = '';
$out .= '<div class="' . $this->id . ' rpr-author-box-container no-print">';

$out .= '<div class="rpr-author-box-gravatar-container">';
$out .= '<img width="96px" height="96px" class="rpr-author-box-gravatar" src="' . $gravatar . '" ';
$out .= 'alt="' . __( "Recipe author's Gravatar image", 'recipepress-reloaded' ) . '">';
$out .= '</div>'; // .rpr-author-box-gravatar-container

$out .= '<div class="rpr-author-box-bio-container">';
$out .= '<h3>' . $this->get_setting( 'author_box_title' ) . ' ' . $author . '</h3>';
$out .= $author_description;

$out .= '<div class="rpr-author-social-container">';
$out .= '<div class="rpr-author-social-icons">';

if ( '' !== $facebook_url ) { // Facebook.
	$out .= '<a href="' . esc_url( $facebook_url ) . '" rel="noreferrer noopener" ';
	$out .= 'aria-label="Facebook: ' . esc_url( $facebook_url ) . '" ';
	$out .= 'class="facebook" ';
	$out .= 'style="background: ' . esc_attr( $this->get_setting( 'facebook_color' ) ) . ';" >';
	$out .= Icons::get_the_icon( 'facebook' );
	$out .= '</a>';
}

if ( '' !== $twitter_url ) { // Twitter.
	$out .= '<a href="https://twitter.com/' . esc_attr( $twitter_url ) . '" rel="noreferrer noopener" ';
	$out .= 'aria-label="Twitter: ' . esc_url( $twitter_url ) . '" ';
	$out .= 'class="twitter" ';
	$out .= 'style="background: ' . esc_attr( $this->get_setting( 'twitter_color' ) ) . ';" >';
	$out .= Icons::get_the_icon( 'twitter' );
	$out .= '</a>';
}

if ( '' !== $pinterest_url ) { // Pinterest.
	$out .= '<a href="' . esc_url( $pinterest_url ) . '" rel="noreferrer noopener" ';
	$out .= 'aria-label="Pinterest: ' . esc_url( $pinterest_url ) . '" ';
	$out .= 'class="pinterest" ';
	$out .= 'style="background: ' . esc_attr( $this->get_setting( 'pinterest_color' ) ) . ';" >';
	$out .= Icons::get_the_icon( 'pinterest' );
	$out .= '</a>';
}

if ( '' !== $instagram_url ) { // Instagram.
	$out .= '<a href="' . esc_url( $instagram_url ) . '" rel="noreferrer noopener" ';
	$out .= 'aria-label="Instagram: ' . esc_url( $instagram_url ) . '" ';
	$out .= 'class="instagram" ';
	$out .= 'style="background: ' . esc_attr( $this->get_setting( 'instagram_color' ) ) . ';" >';
	$out .= Icons::get_the_icon( 'instagram' );
	$out .= '</a>';
}

if ( '' !== $linkedin_url ) { // LinkedIn.
	$out .= '<a href="' . esc_url( $linkedin_url ) . '" rel="noreferrer noopener" ';
	$out .= 'aria-label="LinkedIn: ' . esc_url( $linkedin_url ) . '" ';
	$out .= 'class="linkedin" ';
	$out .= 'style="background: ' . esc_attr( $this->get_setting( 'linkedin_color' ) ) . ';" >';
	$out .= Icons::get_the_icon( 'linkedin' );
	$out .= '</a>';
}

if ( '' !== $yummly_url ) { // Yummly.
	$out .= '<a href="' . esc_url( $yummly_url ) . '" rel="noreferrer noopener" ';
	$out .= 'aria-label="Yummly: ' . esc_url( $yummly_url ) . '" ';
	$out .= 'class="yummly" ';
	$out .= 'style="background: ' . esc_attr( $this->get_setting( 'yummly_color' ) ) . ';" >';
	$out .= Icons::get_the_icon( 'yummly' );
	$out .= '</a>';
}

$out .= '</div>'; // .rpr-author-social-icons

$out .= '<div class="rpr-author-archive-link">';
$out .= '<a href="' . $author_archive . '" rel="author">';
$out .= sprintf( '%1$s %2$s by %3$s &rarr;', __( 'View all', 'recipepress-reloaded' ), $post_type, $author );
$out .= '</a>';
$out .= '</div>'; // .rpr-author-archive-link

$out .= '</div>'; // .rpr-author-social-container

$out .= '</div>'; // .rpr-author-box-bio-container
$out .= '</div>'; // .rpr-author-box-container

echo $out; // phpcs:ignore
