import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button, Tooltip } from '@wordpress/components';
import { useEffect } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

export default function InstructionEdit( { attributes, setAttributes, clientId } ) {
	const { description, image, imageUrl, blockKey } = attributes;
	const blockProps = useBlockProps( { className: 'rpr-instruction-block' } );

	// Get step number by counting prior sibling instruction blocks.
	const stepNumber = useSelect( ( select ) => {
		const { getBlockRootClientId, getBlocks } = select( 'core/block-editor' );
		const parentId = getBlockRootClientId( clientId );
		if ( ! parentId ) return 1;
		const siblings = getBlocks( parentId );
		let count = 0;
		for ( const sibling of siblings ) {
			if ( sibling.name === 'rpr/instruction' ) {
				count++;
			}
			if ( sibling.clientId === clientId ) break;
		}
		return count;
	}, [ clientId ] );

	useEffect( () => {
		if ( ! blockKey ) {
			setAttributes( { blockKey: Math.random().toString( 36 ).substr( 2, 9 ) } );
		}
	}, [] );

	return (
		<div { ...blockProps }>
			<div className="rpr-instruction-row">
				<span className="rpr-step-number" aria-label={ __( 'Step', 'recipepress-reloaded' ) }>
					{ stepNumber }
				</span>

				<div className="rpr-instruction-content">
					<RichText
						tagName="p"
						value={ description }
						onChange={ ( v ) => setAttributes( { description: v } ) }
						placeholder={ __( 'Describe this step…', 'recipepress-reloaded' ) }
						allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
						className="rpr-instruction-description"
						data-description={ description }
					/>
				</div>

				<div className="rpr-instruction-media">
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) =>
								setAttributes( {
									image: media.id,
									imageUrl: media.sizes?.thumbnail?.url ?? media.url,
								} )
							}
							allowedTypes={ [ 'image' ] }
							value={ image }
							render={ ( { open } ) => (
								<>
									{ imageUrl ? (
										<div className="rpr-instruction-image-wrap">
											<img
												src={ imageUrl }
												alt={ __( 'Step image', 'recipepress-reloaded' ) }
												className="rpr-instruction-image"
											/>
											<div className="rpr-instruction-image-actions">
												<Button
													variant="secondary"
													isSmall
													onClick={ open }
												>
													{ __( 'Replace', 'recipepress-reloaded' ) }
												</Button>
												<Button
													variant="tertiary"
													isSmall
													isDestructive
													onClick={ () =>
														setAttributes( { image: 0, imageUrl: '' } )
													}
												>
													{ __( 'Remove', 'recipepress-reloaded' ) }
												</Button>
											</div>
										</div>
									) : (
										<Tooltip
											text={ __( 'Add a step image', 'recipepress-reloaded' ) }
										>
											<Button
												variant="tertiary"
												isSmall
												onClick={ open }
												className="rpr-add-step-image"
												aria-label={ __( 'Add step image', 'recipepress-reloaded' ) }
											>
												{ __( '+ Image', 'recipepress-reloaded' ) }
											</Button>
										</Tooltip>
									) }
								</>
							) }
						/>
					</MediaUploadCheck>
				</div>
			</div>
		</div>
	);
}
