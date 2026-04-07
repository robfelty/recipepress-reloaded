import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { TextControl } from '@wordpress/components';
import { useEffect } from '@wordpress/element';

export default function IngredientGroupEdit( { attributes, setAttributes } ) {
	const { title, blockKey } = attributes;
	const blockProps = useBlockProps( { className: 'rpr-ingredient-group-block' } );

	useEffect( () => {
		if ( ! blockKey ) {
			setAttributes( { blockKey: Math.random().toString( 36 ).substr( 2, 9 ) } );
		}
	}, [] );

	return (
		<div { ...blockProps }>
			<span className="rpr-group-label">
				{ __( 'Group:', 'recipepress-reloaded' ) }
			</span>
			<TextControl
				value={ title }
				onChange={ ( value ) => setAttributes( { title: value } ) }
				placeholder={ __( 'e.g. For the sauce…', 'recipepress-reloaded' ) }
				className="rpr-group-title-input"
				hideLabelFromVision
				label={ __( 'Group title', 'recipepress-reloaded' ) }
			/>
		</div>
	);
}
