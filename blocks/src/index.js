/**
 * RecipePress Reloaded – Gutenberg Blocks
 *
 * Entry point. Registers a custom block category, all RPR blocks,
 * the migration plugin, and block patterns.
 */
import { getCategories, setCategories } from '@wordpress/blocks';

const existingCategories = getCategories();
if ( ! existingCategories.find( ( cat ) => cat.slug === 'recipepress' ) ) {
	setCategories( [
		{
			slug: 'recipepress',
			title: 'RecipePress',
			icon: 'food',
		},
		...existingCategories,
	] );
}

// Block registrations.
import './recipe-info/index';
import './ingredients/index';
import './ingredient/index';
import './ingredient-group/index';
import './instructions/index';
import './instruction/index';
import './instruction-group/index';
import './recipe-notes/index';

// Editor plugin (migration panel).
import './migration/index';

// Block patterns.
import './patterns/index';

// Editor styles.
import './editor.css';
