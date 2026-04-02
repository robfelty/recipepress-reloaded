<?php

namespace Recipepress\Inc\Common\Utilities;

use Recipepress as NS;

/**
 * Handles the layout of the recipe template.
 *
 * @since 1.0.0
 *
 * @package Recipepress\Inc\Common\Traits
 */
class Layouts {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @var    array $layouts The layout information.
	 */
	public $layouts;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->get_layouts_list();
	}


	/**
	 * Create a list of available layouts locally and globally.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function get_layouts_list() {

		// First create a list of all locally available layouts.
		$dir_name = NS\PLUGIN_DIR . 'inc/frontend/templates/';

		$this->add_layout_to_list( $dir_name );

		// Then also add layouts available globally from the current theme (if available).
		$dir_name = get_stylesheet_directory() . '/recipepress/';

		$this->add_layout_to_list( $dir_name );
	}

	/**
	 * Add layout to to list.
	 *
	 * @since 1.0.0
	 *
	 * @param string $dir_name The directory name.
	 *
	 * @return void
	 */
	public function add_layout_to_list( $dir_name ) {

		if ( is_dir( $dir_name ) ) {

			// phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition
			if ( $handle = opendir( $dir_name ) ) {

				// phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition
				while ( false !== ( $folder = readdir( $handle ) ) ) {

					if ( $folder && '.' !== $folder && '..' !== $folder && '.svn' !== $folder ) {
						if ( false !== stripos( $dir_name, 'plugins' ) ) {
							$base_url = NS\PLUGIN_URL . 'inc/frontend/templates/' . $folder;
							$local    = true;
						} else {
							$base_url = \get_theme_file_uri() . '/recipepress/' . $folder;
							$local    = false;
						}

						$this->layouts[ $folder ] = array(
							'path'  => $dir_name . $folder,
							'url'   => $base_url,
							'local' => $local,
						);

						$this->get_layout_meta( $dir_name, $folder );
					}
				}
			}
		}
	}

	/**
	 * Get the layout meta data.
	 *
	 * @since 1.0.0
	 *
	 * @see http://stackoverflow.com/questions/11504541/get-comments-in-a-php-file
	 *
	 * @param string $dir_name The directory name.
	 * @param string $folder     The file name.
	 *
	 * @return null|void
	 */
	public function get_layout_meta( $dir_name, $folder ) {

		$params   = array();
		$filename = $dir_name . $folder . '/recipe.php';

		if ( ! file_exists( $filename ) ) {
			return null;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions
		$doc_comments = array_filter( token_get_all( file_get_contents( $filename ) ), array( $this, 'rpr_file_comment' ) );

		$file_doc_comment = array_shift( $doc_comments );

		$regexp = "/.*\:.*\n/";
		preg_match_all( $regexp, $file_doc_comment[1], $matches );

		foreach ( $matches[0] as $match ) {
			$param = explode( ': ', $match );

			if ( $param ) {
                $params[ trim( $param[0] ) ] = trim( $param[1] );
            }
		}

		$this->layouts[ $folder ]['description'] = isset( $params['Description'] ) ? $params['Description'] : '';
		$this->layouts[ $folder ]['title']       = isset( $params['Layout Name'] ) ? $params['Layout Name'] : '';
		$this->layouts[ $folder ]['author']      = isset( $params['Author'] ) ? $params['Author'] : '';
		$this->layouts[ $folder ]['author_mail'] = isset( $params['Author Mail'] ) ? $params['Author Mail'] : '';
		$this->layouts[ $folder ]['author_url']  = isset( $params['Author URL'] ) ? $params['Author URL'] : '';
		$this->layouts[ $folder ]['version']     = isset( $params['Version'] ) ? $params['Version'] : '';
		$this->layouts[ $folder ]['logo']        = $this->get_screenshot( 'logo', $dir_name . $folder, $this->layouts[ $folder ]['url'] );
		$this->layouts[ $folder ]['screenshot']  = $this->get_screenshot( 'screenshot', $dir_name . $folder, $this->layouts[ $folder ]['url'] );
	}

	/**
	 * Returns the `screenshot` file found
	 *
	 * The `screenshot` file is used as a thumbnail for our builtin thumbnails.
	 * SVG > PNG > JPG
	 *
	 * @since 1.7.0
	 *
	 * @param string $file
	 * @param string $path
	 * @param string $url
	 *
	 * @return string
	 */
	private function get_screenshot( $file, $path, $url ) {

		$out   = '';
		$files = glob( $path . '/' . $file . '*' );

		if ( ! empty( $files ) || false !== $files ) {
			foreach ( $files as $f ) {
				if ( false !== stripos( $f, '.jpg' ) ) {
					$out = $url . '/' . basename( $f );
				}
				if ( false !== stripos( $f, '.png' ) ) {
					$out = $url . '/' . basename( $f );
				}
				if ( false !== stripos( $f, '.svg' ) ) {
					$out = $url . '/' . basename( $f );
				}
			}
		}

		return $out;
	}

	/**
	 * The layout list.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function layout_list() {

		$list = array();

		foreach ( $this->layouts as $layout ) {
			$name          = strtolower( str_replace( ' ', '_', isset( $layout['title'] ) ? $layout['title'] : '' ) );
			$list[ $name ] = array(
				'screenshot' => isset( $layout['screenshot'] ) ? $layout['screenshot'] : '',
				'title'      => isset( $layout['title'] ) ? $layout['title'] : '',
				'author'     => isset( $layout['author'] ) ? $layout['author'] : '',
				'version'    => isset( $layout['version'] ) ? $layout['version'] : '',
				'local'      => $layout['local'],
			);
		}

		return $list;
	}

	/**
	 * Filter file content.
	 *
	 * @since 1.0.0
	 *
	 * @param string $entry The comparison entry.
	 *
	 * @return bool
	 */
	private function rpr_file_comment( $entry ) {
		return T_COMMENT === $entry[0];
	}

}
