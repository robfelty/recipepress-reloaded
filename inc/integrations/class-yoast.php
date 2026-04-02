<?php

namespace Recipepress\Inc\Integrations;
/**
 * Handle the recipe metadata integration with Yoast SEO Schema (version 11+)
 *
 * @since      2.1.0
 *
 * @package    Recipepress
 * @author     wzyMedia <wzy@outlook.com>
 */

use Recipepress\Inc\Core\Options;
use Recipepress\Inc\Frontend\Template;
use WPSEO_Schema_Context;
use Yoast\WP\SEO\Config\Schema_IDs;
use Yoast\WP\SEO\Generators\Schema\Abstract_Schema_Piece;

/**
 * Handle the recipe metadata integration with Yoast SEO Schema (version 11+)
 *
 * @since      2.1.0
 *
 * @package    Recipepress
 * @author     wzyMedia <wzy@outlook.com>
 */
class Yoast extends Abstract_Schema_Piece {

	/**
	 * Our recipe post
	 *
	 * @var \WP_Post
	 */
	private $recipe;

	/**
	 * Whether an article is used.
	 *
	 * @var boolean
	 */
	private $using_article;

	/**
	 * WPRM_Metadata_Yoast_Seo constructor.
	 *
	 * @since 2.1.0
	 *
	 * @param WPSEO_Schema_Context $context A value object with context variables.
	 */
	public function __construct( WPSEO_Schema_Context $context ) {
		$this->context = $context;

		$this->using_article = false;
		add_filter( 'wpseo_schema_article', array( $this, 'wpseo_schema_article' ) );
	}

	/**
	 * Alter the article metadata.
	 *
	 * @since 2.1.0
	 *
	 * @param array $data Article schema data.
	 */
	public function wpseo_schema_article( $data ) {

		$this->using_article = true;

		if ( $this->is_needed() ) {
			// Our recipe is the main entity.
			unset( $data['mainEntityOfPage'] );
		}

		return $data;
	}

	/**
	 * Determine whether we should return Recipe schema.
	 *
	 * @since  2.1.0
	 *
	 * @return bool
	 */
	public function is_needed() {
		if ( is_singular() ) {
			$recipe = $this->context->post;

			if ( $recipe && 'rpr_recipe' === $recipe->post_type ) {
				$this->recipe = $recipe;
				return true;
			}

		}

		return false;
	}

	/**
	 * Add recipe piece of the graph.
	 *
	 * @since  2.1.0
	 *
	 * @return array|bool $graph A graph piece on success, false on failure.
	 */
	public function generate() {
		$metadata = ( new Template( 'recipepress-reloaded', '2.1.0' ) )->get_the_rpr_recipe_schema( $this->recipe->ID );

		if ( $metadata ) {
			$metadata['@id'] = $this->context->canonical . '#recipe';

			$parent = $this->using_article ? $this->context->canonical . Schema_IDs::ARTICLE_HASH : $this->context->canonical . Schema_IDs::WEBPAGE_HASH;

			$metadata['isPartOf'] = array( '@id' => $parent );
			$metadata['mainEntityOfPage'] = $this->context->canonical . Schema_IDs::WEBPAGE_HASH;

			return $metadata;
		}

		return false;
	}
}

