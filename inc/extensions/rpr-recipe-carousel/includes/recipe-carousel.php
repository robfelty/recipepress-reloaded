<?php
/**
 * The HTML markup of our recipe carousel
 *
 * @package Recipepress
 */

$out     = '';
/*$recipes = get_posts(
	array(
		'numberposts' => 10,
		'exclude'     => array( $this->the_review_id() ),
		'post_type'   => 'rpr_recipe',
	)
);*/

$out .= '<div class="rpr-carousel no-print">';
/*foreach ( $recipes as $recipe ) {
	$out .= '<div style="background-color: antiquewhite; margin: 0 5px;">' . $recipe->post_title . '</div>';
}*/
$out .= '</div>';

echo $out; // phpcs:ignore
