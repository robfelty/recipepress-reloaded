import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';

export default function RecipeNotesEdit() {
	const blockProps = useBlockProps( { className: 'rpr-recipe-notes-block' } );
	const [ meta, setMeta ] = useEntityProp( 'postType', 'rpr_recipe', 'meta' );
	const notes = meta?.rpr_recipe_notes ?? '';

	return (
		<div { ...blockProps }>
			<h3 className="rpr-block-heading">
				{ __( 'Notes', 'recipepress-reloaded' ) }
			</h3>
			<RichText
				tagName="div"
				value={ notes }
				onChange={ ( v ) => setMeta( { ...meta, rpr_recipe_notes: v } ) }
				placeholder={ __( 'Add tips, substitutions, storage info…', 'recipepress-reloaded' ) }
				allowedFormats={ [ 'core/bold', 'core/italic', 'core/link', 'core/list' ] }
				className="rpr-notes-content"
				multiline="p"
			/>
		</div>
	);
}
