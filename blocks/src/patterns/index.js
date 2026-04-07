import { __ } from '@wordpress/i18n';
import { registerBlockPattern, registerBlockPatternCategory } from '@wordpress/blocks';

registerBlockPatternCategory( 'recipepress', {
	label: __( 'RecipePress', 'recipepress-reloaded' ),
} );

registerBlockPattern( 'rpr/complete-recipe', {
	title: __( 'Complete Recipe', 'recipepress-reloaded' ),
	description: __(
		'A full recipe layout with timings, ingredients, step-by-step instructions, and notes.',
		'recipepress-reloaded'
	),
	categories: [ 'recipepress' ],
	keywords: [ 'recipe', 'ingredients', 'instructions', 'cooking', 'food' ],
	content: `<!-- wp:rpr/recipe-info /-->

<!-- wp:rpr/ingredients -->
<!-- wp:rpr/ingredient /-->
<!-- /wp:rpr/ingredients -->

<!-- wp:rpr/instructions -->
<!-- wp:rpr/instruction /-->
<!-- /wp:rpr/instructions -->

<!-- wp:rpr/recipe-notes /-->`,
} );

registerBlockPattern( 'rpr/ingredients-only', {
	title: __( 'Ingredients List', 'recipepress-reloaded' ),
	description: __( 'Just the ingredients list.', 'recipepress-reloaded' ),
	categories: [ 'recipepress' ],
	content: `<!-- wp:rpr/ingredients -->
<!-- wp:rpr/ingredient /-->
<!-- /wp:rpr/ingredients -->`,
} );

registerBlockPattern( 'rpr/instructions-only', {
	title: __( 'Instructions', 'recipepress-reloaded' ),
	description: __( 'Just the step-by-step instructions.', 'recipepress-reloaded' ),
	categories: [ 'recipepress' ],
	content: `<!-- wp:rpr/instructions -->
<!-- wp:rpr/instruction /-->
<!-- /wp:rpr/instructions -->`,
} );
