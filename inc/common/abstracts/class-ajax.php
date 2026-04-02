<?php

namespace Recipepress\Inc\Common\Abstracts;

/**
 * WP_AJAX
 *
 * A simple class for doing AJAX related actions
 *
 * @see https://github.com/anthonybudd/WP_AJAX
 *
 * @package    Recipepress
 *
 * @author     Kemory Grubb <kemory@wzymedia.com>
 */
abstract class AJAX {

	/**
	 * The action name
	 *
	 * @since    1.0.0
	 *
	 * @var string $action The action name of our AJAX request
	 */
	protected $action;

	/**
	 * The HTTP $_REQUEST global
	 *
	 * @since    1.0.0
	 *
	 * @var array $request The HTTP $_REQUEST global.
	 */
	public $request;

	/**
	 * The wp object
	 *
	 * @since    1.0.0
	 *
	 * @var object $wp The global `$wp` object.
	 */
	public $wp;

	/**
	 * The WP user
	 *
	 * @since 1.0.0
	 *
	 * @var \WP_User $user The current user object.
	 */
	public $user;


	/**
	 * This where we do our work
	 *
	 * @since  1.0.0
	 *
	 * @return mixed
	 */
	abstract protected function run();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 */
	public function __construct() {

		global $wp;
		$this->wp      = $wp;
		$this->request = $_REQUEST; // phpcs:ignore

		if ( $this->is_logged_in() ) {
			$this->user = wp_get_current_user();
		}
	}

	/**
	 * Initialize our calling class and run its `run()` method
	 *
	 * @since   1.0.0
	 *
	 * @return void
	 */
	public static function boot() {

		$class  = self::get_class_name();
		$action = new $class();

		$action->run();
		die();
	}

	/**
	 * Add our actions to the `wp_ajax` action
	 *
	 * @since   1.0.0
	 *
	 * @param bool $public Should we listen for public AJAX requests.
	 *
	 * @return void
	 */
	public static function listen( $public = true ) {

		$action_name = self::get_action_name();
		$class_name  = self::get_class_name();

		add_action( 'wp_ajax_' . $action_name, array( $class_name, 'boot' ) );

		if ( $public ) {
			add_action( 'wp_ajax_nopriv_' . $action_name, array( $class_name, 'boot' ) );
		}
	}


	/**
	 * Get the name of the class we are working with
	 *
	 * @since   1.0.0
	 *
	 * @return string
	 */
	public static function get_class_name() {

		return static::class;
	}

    /**
     * Get the name of the class we are working with
     *
     * @since   1.0.0
     *
     * @throws \WP_Error
     * @throws \ReflectionException
     *
     * @return string
     */
	public static function get_action_name() {

		$class      = self::get_class_name();
		$reflection = new \ReflectionClass( $class );
		$action     = $reflection->newInstanceWithoutConstructor();

		if ( ! isset( $action->action ) ) {
			new \WP_Error( 'Public property ' . $action . ' not provided' );
		}

		return $action->action;
	}

	/**
	 * The WP admin AJAX URL
	 *
	 * @since   1.0.0
	 *
	 * @return string
	 */
	public static function form_url() {

		return admin_url( '/admin-ajax.php' );
	}

	/**
	 * Return to the previous page
	 *
	 * @since   1.0.0
	 *
	 * @return mixed
	 */
	public function return_back() {

		if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			header( 'Location: ' . $_SERVER['HTTP_REFERER'] ); // phpcs:ignore
			die();
		}

		return false;
	}

	/**
	 * Return to the previous page
	 *
	 * @since   1.0.0
	 *
	 * @param string $url    The URL we are redirecting to.
	 * @param array  $params The URL params.
	 *
	 * @return void
	 */
	public function return_redirect( $url, $params = array() ) {

		$url .= '?' . http_build_query( $params );
		ob_clean();
		header( 'Location: ' . $url );
		die();
	}

	/**
	 * Return data as JSON
	 *
	 * @since  1.0.0
	 *
	 * @param mixed $data The data to send back as JSON.
	 *
	 * @return void
	 */
	public function return_json( $data ) {

		wp_send_json_success( $data );
	}

	/**
	 * The AJAX URL of our action
	 *
	 * @since   1.0.0
	 *
	 * @param array $params The URL params.
	 *
	 * @return string
	 */
	public static function url( $params = array() ) {

		$params = http_build_query(
			array_merge(
				array(
					'action' => ( new static() )->action,
				),
				$params
			)
		);

		return admin_url( '/admin-ajax.php' ) . '?' . $params;
	}

	/**
	 * Is the user logged in
	 *
	 * @since   1.0.0
	 *
	 * @return mixed
	 */
	public function is_logged_in() {

		if ( function_exists( 'is_user_logged_in' ) ) {
			return is_user_logged_in();
		}

		return null;
	}

	/**
	 * Is a key in our HTTP request
	 *
	 * @since   1.0.0
	 *
	 * @param string $key The needle.
	 *
	 * @return mixed
	 */
	public function has( $key ) {

		if ( isset( $this->request[ $key ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get a key from our HTTP request
	 *
	 * @param string $key           The needle.
	 * @param string $default       Default if key not present.
	 * @param bool   $strip_slashes Strip slashes.
	 *
	 * @return string
	 */
	public function get( $key, $default = null, $strip_slashes = true ) {

		if ( $this->has( $key ) ) {

			if ( $strip_slashes ) {
				return stripslashes( $this->request[ $key ] );
			}

			return $this->request[ $key ];
		}

		return $default;
	}

	/**
	 * Checks or returns the HTTP post verb
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $request_type The type of request you want to check. If an array
	 *                            this method will return true if the request matches any type.
	 *
	 * @return bool|string
	 */
	public function request_type( $request_type = null ) {

		if ( null !== $request_type ) {

			if ( is_array( $request_type ) ) {
				// phpcs:ignore
				return in_array( $_SERVER['REQUEST_METHOD'], array_map( 'strtoupper', $request_type ), true );
			}

			return ( strtoupper( $request_type ) === $_SERVER['REQUEST_METHOD'] ); // phpcs:ignore
		}

		return $_SERVER['REQUEST_METHOD']; // phpcs:ignore
	}
}
