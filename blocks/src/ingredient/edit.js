import { __ } from '@wordpress/i18n';
import { useBlockProps, URLInput } from '@wordpress/block-editor';
import { TextControl, Button, Popover, ComboboxControl } from '@wordpress/components';
import { useState, useEffect, useCallback, useRef } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

/**
 * Debounce helper.
 */
function useDebounce( fn, delay ) {
	const timer = useRef( null );
	return useCallback(
		( ...args ) => {
			clearTimeout( timer.current );
			timer.current = setTimeout( () => fn( ...args ), delay );
		},
		[ fn, delay ]
	);
}

/**
 * Ingredient name input with REST-API autocomplete.
 */
function IngredientAutocomplete( { value, ingredientId, onChange } ) {
	const [ inputValue, setInputValue ] = useState( value );
	const [ suggestions, setSuggestions ] = useState( [] );
	const [ isOpen, setIsOpen ] = useState( false );
	const [ isLoading, setIsLoading ] = useState( false );
	const anchorRef = useRef( null );

	// Keep input in sync when attribute changes externally (e.g. migration).
	useEffect( () => {
		setInputValue( value );
	}, [ value ] );

	const fetchSuggestions = useDebounce( async ( search ) => {
		if ( search.length < 2 ) {
			setSuggestions( [] );
			setIsOpen( false );
			return;
		}
		setIsLoading( true );
		try {
			const terms = await apiFetch( {
				path: `/wp/v2/rpr_ingredient?search=${ encodeURIComponent( search ) }&per_page=10`,
			} );
			setSuggestions( terms );
			setIsOpen( terms.length > 0 );
		} catch {
			setSuggestions( [] );
		}
		setIsLoading( false );
	}, 300 );

	const handleChange = ( newValue ) => {
		setInputValue( newValue );
		onChange( { ingredient: newValue, ingredientId: 0 } ); // Clear ID for free-form
		fetchSuggestions( newValue );
	};

	const handleSelect = ( term ) => {
		setInputValue( term.name );
		setSuggestions( [] );
		setIsOpen( false );
		onChange( { ingredient: term.name, ingredientId: term.id } );
	};

	return (
		<div className="rpr-ingredient-autocomplete" ref={ anchorRef }>
			<TextControl
				value={ inputValue }
				onChange={ handleChange }
				onBlur={ () => setTimeout( () => setIsOpen( false ), 150 ) }
				onFocus={ () => suggestions.length > 0 && setIsOpen( true ) }
				placeholder={ __( 'Ingredient', 'recipepress-reloaded' ) }
				hideLabelFromVision
				label={ __( 'Ingredient name', 'recipepress-reloaded' ) }
				className={ `rpr-ingredient-name-input ${ ingredientId ? 'has-term' : '' }` }
				aria-autocomplete="list"
			/>
			{ isOpen && suggestions.length > 0 && (
				<Popover
					anchor={ anchorRef.current }
					placement="bottom-start"
					focusOnMount={ false }
					className="rpr-ingredient-suggestions-popover"
				>
					<ul className="rpr-ingredient-suggestions" role="listbox">
						{ suggestions.map( ( term ) => (
							<li
								key={ term.id }
								role="option"
								aria-selected={ term.id === ingredientId }
								className={ `rpr-suggestion ${ term.id === ingredientId ? 'is-selected' : '' }` }
								onMouseDown={ () => handleSelect( term ) }
							>
								{ term.name }
							</li>
						) ) }
					</ul>
				</Popover>
			) }
		</div>
	);
}

export default function IngredientEdit( { attributes, setAttributes } ) {
	const { amount, unit, ingredient, ingredientId, notes, link: linkUrl, target, blockKey } =
		attributes;
	const blockProps = useBlockProps( { className: 'rpr-ingredient-block' } );
	const [ showLink, setShowLink ] = useState( !! linkUrl );

	const unitList = window.rprBlocksData?.unitList ?? [];
	const unitOptions = [
		{ value: '', label: __( '—', 'recipepress-reloaded' ) },
		...unitList.map( ( u ) => ( { value: u, label: u } ) ),
	];

	useEffect( () => {
		if ( ! blockKey ) {
			setAttributes( { blockKey: Math.random().toString( 36 ).substr( 2, 9 ) } );
		}
	}, [] );

	return (
		<div { ...blockProps }>
			<div className="rpr-ingredient-row">
				{/* Amount */}
				<TextControl
					value={ amount }
					onChange={ ( v ) => setAttributes( { amount: v } ) }
					placeholder={ __( 'Qty', 'recipepress-reloaded' ) }
					hideLabelFromVision
					label={ __( 'Amount', 'recipepress-reloaded' ) }
					className="rpr-ingredient-amount"
				/>

				{/* Unit — ComboboxControl allows free-form typing */}
				<ComboboxControl
					value={ unit }
					onChange={ ( v ) => setAttributes( { unit: v ?? '' } ) }
					options={ unitOptions }
					onFilterValueChange={ ( v ) => setAttributes( { unit: v } ) }
					placeholder={ __( 'Unit', 'recipepress-reloaded' ) }
					hideLabelFromVision
					label={ __( 'Unit', 'recipepress-reloaded' ) }
					className="rpr-ingredient-unit"
					allowReset={ false }
				/>

				{/* Ingredient name with autocomplete */}
				<IngredientAutocomplete
					value={ ingredient }
					ingredientId={ ingredientId }
					onChange={ ( val ) => setAttributes( val ) }
				/>

				{/* Notes */}
				<TextControl
					value={ notes }
					onChange={ ( v ) => setAttributes( { notes: v } ) }
					placeholder={ __( 'Notes', 'recipepress-reloaded' ) }
					hideLabelFromVision
					label={ __( 'Notes', 'recipepress-reloaded' ) }
					className="rpr-ingredient-notes"
				/>

				{/* Link toggle */}
				<Button
					icon={ linkUrl ? 'editor-unlink' : 'admin-links' }
					label={
						linkUrl
							? __( 'Remove link', 'recipepress-reloaded' )
							: __( 'Add link', 'recipepress-reloaded' )
					}
					onClick={ () => {
						if ( linkUrl ) {
							setAttributes( { link: '', target: 'same' } );
							setShowLink( false );
						} else {
							setShowLink( ( v ) => ! v );
						}
					} }
					isSmall
					className={ `rpr-ingredient-link-toggle ${ linkUrl ? 'has-link' : '' }` }
				/>
			</div>

			{/* Link URL input */}
			{ showLink && ! linkUrl && (
				<div className="rpr-ingredient-link-row">
					<URLInput
						value={ linkUrl }
						onChange={ ( url ) => setAttributes( { link: url } ) }
						placeholder={ __( 'Enter URL…', 'recipepress-reloaded' ) }
						className="rpr-ingredient-link-input"
					/>
					<label className="rpr-link-target-label">
						<input
							type="checkbox"
							checked={ target === 'new' }
							onChange={ ( e ) =>
								setAttributes( { target: e.target.checked ? 'new' : 'same' } )
							}
						/>
						{ __( 'Open in new tab', 'recipepress-reloaded' ) }
					</label>
				</div>
			) }
			{ linkUrl && (
				<div className="rpr-ingredient-link-display">
					<span className="rpr-link-icon">🔗</span>
					<a href={ linkUrl } target="_blank" rel="noreferrer noopener">
						{ linkUrl }
					</a>
					<label className="rpr-link-target-label">
						<input
							type="checkbox"
							checked={ target === 'new' }
							onChange={ ( e ) =>
								setAttributes( { target: e.target.checked ? 'new' : 'same' } )
							}
						/>
						{ __( 'New tab', 'recipepress-reloaded' ) }
					</label>
				</div>
			) }
		</div>
	);
}
