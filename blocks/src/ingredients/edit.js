import { __ } from '@wordpress/i18n';
import { useBlockProps, InnerBlocks, useInnerBlocksProps } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';
import { useDispatch } from '@wordpress/data';
import { createBlock } from '@wordpress/blocks';

const ALLOWED_BLOCKS = [ 'rpr/ingredient', 'rpr/ingredient-group' ];
const TEMPLATE = [ [ 'rpr/ingredient', {} ] ];

function IngredientsAppender( { clientId } ) {
	const { insertBlock } = useDispatch( 'core/block-editor' );

	return (
		<div className="rpr-ingredients-appender">
			<Button
				variant="secondary"
				icon="plus-alt2"
				onClick={ () =>
					insertBlock(
						createBlock( 'rpr/ingredient', {} ),
						undefined,
						clientId,
						false
					)
				}
				className="rpr-add-ingredient"
			>
				{ __( 'Add Ingredient', 'recipepress-reloaded' ) }
			</Button>
			<Button
				variant="tertiary"
				onClick={ () =>
					insertBlock(
						createBlock( 'rpr/ingredient-group', {} ),
						undefined,
						clientId,
						false
					)
				}
				className="rpr-add-group"
			>
				{ __( 'Add Group', 'recipepress-reloaded' ) }
			</Button>
		</div>
	);
}

export default function IngredientsEdit( { clientId } ) {
	const blockProps = useBlockProps( { className: 'rpr-ingredients-block' } );
	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'rpr-ingredients-list' },
		{
			allowedBlocks: ALLOWED_BLOCKS,
			template: TEMPLATE,
			templateLock: false,
			renderAppender: false,
		}
	);

	return (
		<div { ...blockProps }>
			<h3 className="rpr-block-heading">
				{ __( 'Ingredients', 'recipepress-reloaded' ) }
			</h3>
			<div className="rpr-ingredients-header">
				<span className="rpr-col-label rpr-col-amount">{ __( 'Amount', 'recipepress-reloaded' ) }</span>
				<span className="rpr-col-label rpr-col-unit">{ __( 'Unit', 'recipepress-reloaded' ) }</span>
				<span className="rpr-col-label rpr-col-name">{ __( 'Ingredient', 'recipepress-reloaded' ) }</span>
				<span className="rpr-col-label rpr-col-notes">{ __( 'Notes', 'recipepress-reloaded' ) }</span>
			</div>
			<div { ...innerBlocksProps } />
			<IngredientsAppender clientId={ clientId } />
		</div>
	);
}
