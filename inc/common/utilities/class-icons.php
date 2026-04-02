<?php

namespace Recipepress\Inc\Common\Utilities;

use Recipepress as NS;

/**
 * Handles outputting SVG directly into HTML
 *
 * @since 1.7.0
 *
 * @package Recipepress
 */
class Icons {

	/**
	 * @param string $icon The icon name.
	 *
	 * @return false|string
	 */
	public static function get_the_icon( $icon = null ) {

		return $icon ? @file_get_contents( NS\PLUGIN_DIR . 'assets/public/svg/icons/' . $icon . '.svg', false, null ) : '';
	}

	/**
	 * @param null $icon
	 *
	 * @return void
	 */
	public static function the_icon( $icon = null ) {

		echo self::get_the_icon( $icon );
	}

	/**
	 * @param string $icon The icon name.
	 *
	 * @return false|string
	 */
	public static function get_the_uri( $icon = null ) {

		return $icon ?  NS\PLUGIN_URL . 'assets/public/svg/icons/' . $icon . '.svg' : '';
	}

}
