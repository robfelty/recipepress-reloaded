import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { TextControl, SelectControl } from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';

function TimeField( { label, metaKey, meta, setMeta } ) {
	const value = meta?.[ metaKey ] ?? '';
	const minutes = parseInt( value, 10 ) || 0;

	const hours = Math.floor( minutes / 60 );
	const mins = minutes % 60;

	const formatDisplay = () => {
		if ( ! minutes ) return '';
		if ( hours && mins ) return `${ hours }h ${ mins }m`;
		if ( hours ) return `${ hours }h`;
		return `${ mins }m`;
	};

	return (
		<div className="rpr-info-field">
			<label className="rpr-info-label">{ label }</label>
			<div className="rpr-time-inputs">
				<TextControl
					type="number"
					min="0"
					value={ hours || '' }
					onChange={ ( h ) => {
						const newMins = ( parseInt( h, 10 ) || 0 ) * 60 + mins;
						setMeta( { ...meta, [ metaKey ]: String( newMins ) } );
					} }
					placeholder="0"
					className="rpr-time-hours"
					hideLabelFromVision
					label={ __( 'Hours', 'recipepress-reloaded' ) }
				/>
				<span className="rpr-time-sep">{ __( 'h', 'recipepress-reloaded' ) }</span>
				<TextControl
					type="number"
					min="0"
					max="59"
					value={ mins || '' }
					onChange={ ( m ) => {
						const newMins = hours * 60 + ( parseInt( m, 10 ) || 0 );
						setMeta( { ...meta, [ metaKey ]: String( newMins ) } );
					} }
					placeholder="0"
					className="rpr-time-minutes"
					hideLabelFromVision
					label={ __( 'Minutes', 'recipepress-reloaded' ) }
				/>
				<span className="rpr-time-sep">{ __( 'min', 'recipepress-reloaded' ) }</span>
				{ !! minutes && (
					<span className="rpr-time-display">{ formatDisplay() }</span>
				) }
			</div>
		</div>
	);
}

export default function RecipeInfoEdit() {
	const blockProps = useBlockProps( { className: 'rpr-recipe-info-block' } );
	const [ meta, setMeta ] = useEntityProp( 'postType', 'rpr_recipe', 'meta' );

	const servingUnitList = window.rprBlocksData?.servingUnitList ?? [];
	const servingUnitOptions = [
		{ label: __( '— select —', 'recipepress-reloaded' ), value: '' },
		...servingUnitList.map( ( unit ) => ( { label: unit, value: unit } ) ),
		{ label: __( 'Custom…', 'recipepress-reloaded' ), value: '__custom__' },
	];

	const servingsType = meta?.rpr_recipe_servings_type ?? '';
	const isCustomUnit = servingsType && ! servingUnitList.includes( servingsType );

	return (
		<div { ...blockProps }>
			<h3 className="rpr-block-heading">
				{ __( 'Recipe Info', 'recipepress-reloaded' ) }
			</h3>
			<div className="rpr-info-grid">
				<div className="rpr-info-field rpr-servings-field">
					<label className="rpr-info-label">
						{ __( 'Servings', 'recipepress-reloaded' ) }
					</label>
					<div className="rpr-servings-inputs">
						<TextControl
							type="number"
							min="0"
							value={ meta?.rpr_recipe_servings ?? '' }
							onChange={ ( v ) =>
								setMeta( { ...meta, rpr_recipe_servings: v } )
							}
							placeholder="4"
							hideLabelFromVision
							label={ __( 'Servings count', 'recipepress-reloaded' ) }
							className="rpr-servings-count"
						/>
						{ servingUnitList.length > 0 ? (
							<>
								<SelectControl
									value={ isCustomUnit ? '__custom__' : servingsType }
									options={ servingUnitOptions }
									onChange={ ( v ) => {
										if ( v !== '__custom__' ) {
											setMeta( { ...meta, rpr_recipe_servings_type: v } );
										}
									} }
									hideLabelFromVision
									label={ __( 'Serving unit', 'recipepress-reloaded' ) }
									className="rpr-servings-type-select"
								/>
								{ ( isCustomUnit || servingsType === '__custom__' ) && (
									<TextControl
										value={ isCustomUnit ? servingsType : '' }
										onChange={ ( v ) =>
											setMeta( { ...meta, rpr_recipe_servings_type: v } )
										}
										placeholder={ __( 'servings', 'recipepress-reloaded' ) }
										hideLabelFromVision
										label={ __( 'Custom serving unit', 'recipepress-reloaded' ) }
										className="rpr-servings-type-custom"
									/>
								) }
							</>
						) : (
							<TextControl
								value={ servingsType }
								onChange={ ( v ) =>
									setMeta( { ...meta, rpr_recipe_servings_type: v } )
								}
								placeholder={ __( 'servings', 'recipepress-reloaded' ) }
								hideLabelFromVision
								label={ __( 'Serving unit', 'recipepress-reloaded' ) }
								className="rpr-servings-type"
							/>
						) }
					</div>
				</div>

				<TimeField
					label={ __( 'Prep Time', 'recipepress-reloaded' ) }
					metaKey="rpr_recipe_prep_time"
					meta={ meta }
					setMeta={ setMeta }
				/>
				<TimeField
					label={ __( 'Cook Time', 'recipepress-reloaded' ) }
					metaKey="rpr_recipe_cook_time"
					meta={ meta }
					setMeta={ setMeta }
				/>
				<TimeField
					label={ __( 'Passive Time', 'recipepress-reloaded' ) }
					metaKey="rpr_recipe_passive_time"
					meta={ meta }
					setMeta={ setMeta }
				/>
			</div>
		</div>
	);
}
