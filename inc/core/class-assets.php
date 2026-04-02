<?php

namespace Recipepress\Inc\Core;

use Recipepress as NS;
use Recipepress\Inc\Core\Options;

/**
 * Handles the loading of static assets such as JS & CSS files.
 *
 * This class defines all code necessary to register and enqueue JavaScript
 * and CSS files.
 *
 * @since 1.0.0
 *
 * @credit Igor Benic https://www.ibenic.com/how-to-create-an-asset-manager-for-wordpress-plugins/
 *
 * @author Kemory Grubb
 **/
class Assets {

	/**
	 * Version
	 *
	 * @var string
	 */
	public $version;

	/**
	 * Version
	 *
	 * @var string
	 */
	public $plugin_name;

	/**
	 * Scripts to load
	 */
	public $scripts;

	/**
	 * Styles to Load
	 */
	public $styles;

	/**
	 * Loaded styles through JS
	 *
	 * @var array
	 */
	public $loaded_styles;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since       1.0.0
	 *
	 * @param       string $plugin_name        The name of this plugin.
	 * @param       string $version            The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name   = $plugin_name;
		$this->version       = $version;
		$this->scripts       = array();
		$this->styles        = array();
		$this->loaded_styles = array();
	}

	/**
	 * Enqueue all registered scripts and styles.
	 *
	 * @return void
	 */
	public function enqueue_all() {
		$this->register_admin_scripts_styles();
		$this->register_public_scripts_styles();

		if ( $this->scripts ) {
			foreach ( $this->scripts as $slug => $script ) {
				$this->enqueue_script( $slug );
			}
		}
		if ( $this->styles ) {
			foreach ( $this->styles as $slug => $style ) {
				$this->enqueue_style( $slug );
			}
		}
	}

	/**
	 * Register the default scripts.
	 */
	public function register_public_scripts_styles() {
		/*if ( Options::get_option( 'rpr_recipe_template_use_icons' ) ) {
			$this->add_style( 'rpr-icons', NS\PLUGIN_URL . 'assets/public/css/rpr-icons.css', array() );
		}*/
	}

	/**
	 * Register the admin scripts.
	 */
	public function register_admin_scripts_styles() {
		// This is called before get_current_screen() is initialized.
		if ( isset( $_REQUEST['page'] ) && ( 'recipepress-reloaded' === $_REQUEST['page'] || 'rpr_extensions' === $_REQUEST['page'] ) ) {
			// Selectize.
			$this->add_script( 'rpr-selectize', NS\ADMIN_ASSET_URL . 'js/libraries/selectize.min.js', array( 'jquery', 'jquery-ui-sortable', 'recipepress-reloaded' ) );
			$this->add_style( 'rpr-selectize', NS\ADMIN_ASSET_URL . 'css/libraries/selectize.default.css', array( 'recipepress-reloaded' ) );
		}
	}

	/**
	 * Add a script
	 *
	 * @param string  $slug         The slug name of this script resource.
	 * @param string  $url          The URL path of this script resource.
	 * @param array   $dependencies The dependencies of our script resource.
	 * @param boolean $footer       Should this script be loaded in the footer.
	 *
	 * @return void
	 */
	public function add_script( $slug, $url, array $dependencies, $footer = true ) {
		if ( isset( $this->scripts[ $slug ] ) ) {
			return;
		}

		$this->scripts[ $slug ] = array(
			'url'          => $url,
			'dependencies' => $dependencies,
			'footer'       => $footer,
		);
	}

	/**
	 * Remove a script
	 *
	 * @param string $slug The slug handle of this script resource.
	 *
	 * @return void
	 */
	public function remove_script( $slug ) {
		if ( ! isset( $this->scripts[ $slug ] ) ) {
			return;
		}

		unset( $this->scripts[ $slug ] );
	}

	/**
	 * Enqueue a registered script.
	 *
	 * @param string $slug The slug name of this script resource.
	 * @return void
	 */
	public function enqueue_script( $slug ) {
		if ( ! isset( $this->scripts[ $slug ] ) ) {
			return;
		}

		if ( ! \wp_script_is( $slug ) ) {
			$script = $this->scripts[ $slug ];
			\wp_enqueue_script( $slug, $script['url'], $script['dependencies'], $this->version, $script['footer'] );
		}
	}

	/**
	 * Load this in footer.
	 *
	 * @param string $slug The script's handle.
	 * @param bool   $return Should we return or echo our script.
	 *
	 * @return mixed
	 */
	public function load_script( $slug, $return = false ) {
		if ( ! isset( $this->scripts[ $slug ] ) ) {
			return false;
		}
		// Not enqueued? Load it in right away but defer it for later.
		if ( ! \wp_script_is( $slug ) ) {
			$script = $this->scripts[ $slug ];
			$html   = '<script defer type="text/javascript" src="' . $script['url'] . '"></script>';
			if ( ! $return ) {
				echo $html;
			} else {
				return $html;
			}
		}
		return false;
	}

	/**
	 * Add a stylesheet.
	 *
	 * @param string $slug
	 * @param string $url
	 * @param array $dependencies
	 * @return void
	 */
	public function add_style( $slug, $url, array $dependencies ) {
		if ( isset( $this->styles[ $slug ] ) ) {
			return;
		}

		$this->styles[ $slug ] = compact('url', 'dependencies');
	}

	/**
	 * Remove a stylesheet.
	 *
	 * @param string $slug
	 * @return void
	 */
	public function remove_style( $slug ) {
		if ( ! isset( $this->styles[ $slug ] ) ) {
			return;
		}

		unset( $this->styles[ $slug ] );
	}

	/**
	 * Enqueue a registered stylesheet.
	 *
	 * @param string $slug
	 * @return void
	 */
	public function enqueue_style( $slug ) {
		if ( ! isset( $this->styles[ $slug ] ) ) {
			return;
		}

		if ( ! \wp_style_is( $slug ) ) {
			$style = $this->styles[ $slug ];
			wp_enqueue_style( $slug, $style['url'], $style['dependencies'], $this->version );
		}
	}

	/**
	 * Load CSS on request if not loaded already.
	 *
	 * @param string $slug
	 * @param bool   $return
	 * @return mixed
	 */
	public function load_style( $slug, $return = false ) {
		if ( ! isset( $this->styles[ $slug ] ) ) {
			return false;
		}
		// Not enqueued? Load it in right away but defer it for later.
		if ( ! \wp_style_is( $slug ) && ! in_array( $slug, $this->loaded_styles, true ) ) {
			$this->loaded_styles[] = $slug;
			$style                 = $this->styles[ $slug ];
			$js_slug               = str_replace( '-', '_', $slug );
			$html                  = "<script defer>
            var head               = document.getElementsByTagName('head')[0];
            var link_" . $js_slug . "  = document.createElement('link');
             
            link_" . $js_slug . ".rel  = 'stylesheet';
            link_" . $js_slug . ".type = 'text/css';
            link_" . $js_slug . ".href = '" . $style['url'] . "';
            link_" . $js_slug . ".media = 'all';
            head.appendChild(link_" . $js_slug . ");</script>";
			if ( ! $return ) {
				echo $html;
			} else {
				return $html;
			}
		}
		return false;
	}
}
