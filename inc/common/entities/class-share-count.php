<?php

namespace Recipepress\Inc\Common\Entities;

use Recipepress as NS;
use Recipepress\Inc\Core\Options;

/**
 * The abstract post type class.
 *
 * @package Recipepress
 *
 * @author  Kemory Grubb <kemory@wzymedia.com>
 */
class Share_Count {

	/**
	 * The share count
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      int $facebook The number of times the related share button was clicked
	 */
	public $facebook;

	/**
	 * The share count
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      int $facebook The number of times the related share button was clicked
	 */
	public $twitter;

	/**
	 * The share count
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      int $pinterest The number of times the related share button was clicked
	 */
	public $pinterest;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 */
	public function __construct() {

		$this->facebook  = 0;
		$this->twitter   = 0;
		$this->pinterest = 0;
	}
}
