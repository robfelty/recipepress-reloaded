import { __ } from '@wordpress/i18n';
import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';
import { useDispatch } from '@wordpress/data';
import { createBlock } from '@wordpress/blocks';

const ALLOWED_BLOCKS = [ 'rpr/instruction', 'rpr/instruction-group' ];
const TEMPLATE = [ [ 'rpr/instruction', {} ] ];

function InstructionsAppender( { clientId } ) {
	const { insertBlock } = useDispatch( 'core/block-editor' );

	return (
		<div className="rpr-instructions-appender">
			<Button
				variant="secondary"
				icon="plus-alt2"
				onClick={ () =>
					insertBlock(
						createBlock( 'rpr/instruction', {} ),
						undefined,
						clientId,
						false
					)
				}
				className="rpr-add-instruction"
			>
				{ __( 'Add Step', 'recipepress-reloaded' ) }
			</Button>
			<Button
				variant="tertiary"
				onClick={ () =>
					insertBlock(
						createBlock( 'rpr/instruction-group', {} ),
						undefined,
						clientId,
						false
					)
				}
				className="rpr-add-instruction-group"
			>
				{ __( 'Add Section', 'recipepress-reloaded' ) }
			</Button>
		</div>
	);
}

export default function InstructionsEdit( { clientId } ) {
	const blockProps = useBlockProps( { className: 'rpr-instructions-block' } );
	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'rpr-instructions-list' },
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
				{ __( 'Instructions', 'recipepress-reloaded' ) }
			</h3>
			<div { ...innerBlocksProps } />
			<InstructionsAppender clientId={ clientId } />
		</div>
	);
}
