/**
 * Migration panel: detects recipes in classic-editor format and offers
 * one-click conversion to Gutenberg blocks.
 *
 * Data flow:
 *  - Meta is read from the editor store (already loaded by Gutenberg).
 *  - Blocks are inserted into the editor; the user must click "Update" to save.
 *  - On the first REST save with recipe blocks, PHP archives the original meta
 *    and sets the rpr_recipe_uses_blocks flag (see class-blocks.php).
 *  - A "Revert to Classic" button calls the /rpr-blocks/v1/restore-meta REST
 *    endpoint and reloads to re-open the classic editor.
 */
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { Button, Notice, Spinner } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { createBlock } from '@wordpress/blocks';
import apiFetch from '@wordpress/api-fetch';

function MigrationPanel() {
	const [ isMigrating, setIsMigrating ]     = useState( false );
	const [ isReverting, setIsReverting ]     = useState( false );
	const [ pendingSave, setPendingSave ]     = useState( false );
	const [ error, setError ]                 = useState( null );

	const { postType, postId, meta, hasRecipeBlocks, existingBlocks } = useSelect(
		( select ) => {
			const editor      = select( 'core/editor' );
			const blockEditor = select( 'core/block-editor' );
			const blocks      = blockEditor.getBlocks();
			const rprNames    = [ 'rpr/ingredients', 'rpr/instructions', 'rpr/recipe-info', 'rpr/recipe-notes' ];
			return {
				postType:        editor.getCurrentPostType(),
				postId:          editor.getCurrentPostId(),
				meta:            editor.getEditedPostAttribute( 'meta' ) ?? {},
				hasRecipeBlocks: blocks.some( ( b ) => rprNames.includes( b.name ) ),
				existingBlocks:  blocks,
			};
		}
	);

	const { resetBlocks } = useDispatch( 'core/block-editor' );

	if ( postType !== 'rpr_recipe' ) {
		return null;
	}

	// ── Convert to blocks ────────────────────────────────────────────────────

	const migrate = () => {
		setIsMigrating( true );
		setError( null );
		try {
			const newBlocks = [];

			newBlocks.push( createBlock( 'rpr/recipe-info', {} ) );

			const ingredients = meta.rpr_recipe_ingredients ?? [];
			const ingInner = ingredients.length > 0
				? ingredients.map( ( ing ) => {
					if ( ing.grouptitle ) {
						return createBlock( 'rpr/ingredient-group', {
							title:    ing.grouptitle,
							blockKey: ing.key || Math.random().toString( 36 ).substr( 2, 9 ),
						} );
					}
					return createBlock( 'rpr/ingredient', {
						amount:       ing.amount ?? '',
						unit:         ing.unit ?? '',
						ingredient:   ing.ingredient ?? '',
						ingredientId: parseInt( ing.ingredient_id, 10 ) || 0,
						notes:        ing.notes ?? '',
						link:         ing.link ?? '',
						target:       ing.target ?? 'same',
						blockKey:     ing.key || Math.random().toString( 36 ).substr( 2, 9 ),
					} );
				} )
				: [ createBlock( 'rpr/ingredient', {} ) ];
			newBlocks.push( createBlock( 'rpr/ingredients', {}, ingInner ) );

			const instructions = meta.rpr_recipe_instructions ?? [];
			const instInner = instructions.length > 0
				? instructions.map( ( inst ) => {
					if ( inst.grouptitle ) {
						return createBlock( 'rpr/instruction-group', {
							title:    inst.grouptitle,
							blockKey: inst.key || Math.random().toString( 36 ).substr( 2, 9 ),
						} );
					}
					return createBlock( 'rpr/instruction', {
						description: inst.description ?? '',
						image:       parseInt( inst.image, 10 ) || 0,
						imageUrl:    '',
						blockKey:    inst.key || Math.random().toString( 36 ).substr( 2, 9 ),
					} );
				} )
				: [ createBlock( 'rpr/instruction', {} ) ];
			newBlocks.push( createBlock( 'rpr/instructions', {}, instInner ) );

			newBlocks.push( createBlock( 'rpr/recipe-notes', {} ) );

			resetBlocks( [ ...existingBlocks, ...newBlocks ] );
			setPendingSave( true );
		} catch ( e ) {
			setError( e.message || __( 'Conversion failed.', 'recipepress-reloaded' ) );
		}
		setIsMigrating( false );
	};

	// ── Revert to classic editor ─────────────────────────────────────────────

	const revert = async () => {
		setIsReverting( true );
		setError( null );
		try {
			await apiFetch( {
				path:   `/rpr-blocks/v1/restore-meta/${ postId }`,
				method: 'POST',
				data:   {},
			} );
			// Reload the page so the classic editor metaboxes are shown.
			window.location.reload();
		} catch ( e ) {
			setError( e.message || __( 'Revert failed.', 'recipepress-reloaded' ) );
			setIsReverting( false );
		}
	};

	// ── Render ───────────────────────────────────────────────────────────────

	// Recipe already uses blocks — show the revert option.
	if ( hasRecipeBlocks && meta?.rpr_recipe_uses_blocks ) {
		return (
			<PluginDocumentSettingPanel
				name="rpr-migration-panel"
				title={ __( 'Block Editor', 'recipepress-reloaded' ) }
				className="rpr-migration-panel"
			>
				<p>{ __( 'This recipe uses the block editor.', 'recipepress-reloaded' ) }</p>
				{ error && (
					<Notice status="error" isDismissible={ false }>{ error }</Notice>
				) }
				<Button
					variant="tertiary"
					isDestructive
					onClick={ revert }
					disabled={ isReverting }
					className="rpr-revert-button"
				>
					{ isReverting ? (
						<><Spinner />{ __( 'Reverting…', 'recipepress-reloaded' ) }</>
					) : (
						__( 'Revert to Classic Editor', 'recipepress-reloaded' )
					) }
				</Button>
			</PluginDocumentSettingPanel>
		);
	}

	// Blocks inserted but not yet saved.
	if ( pendingSave ) {
		return (
			<PluginDocumentSettingPanel
				name="rpr-migration-panel"
				title={ __( 'Convert to Blocks', 'recipepress-reloaded' ) }
				className="rpr-migration-panel"
			>
				<Notice status="success" isDismissible={ false }>
					{ __(
						'Blocks created. Click "Update" to save. Your original data will be archived and can be restored if needed.',
						'recipepress-reloaded'
					) }
				</Notice>
			</PluginDocumentSettingPanel>
		);
	}

	// Classic-editor recipe — show the convert option.
	if ( ! hasRecipeBlocks ) {
		return (
			<PluginDocumentSettingPanel
				name="rpr-migration-panel"
				title={ __( 'Convert to Blocks', 'recipepress-reloaded' ) }
				className="rpr-migration-panel"
			>
				<p>
					{ __(
						'Convert this recipe to the block editor. Your original data will be archived so you can revert if needed.',
						'recipepress-reloaded'
					) }
				</p>
				{ error && (
					<Notice status="error" isDismissible={ false }>{ error }</Notice>
				) }
				<Button
					variant="primary"
					onClick={ migrate }
					disabled={ isMigrating }
					className="rpr-migrate-button"
				>
					{ isMigrating ? (
						<><Spinner />{ __( 'Converting…', 'recipepress-reloaded' ) }</>
					) : (
						__( 'Convert to Blocks', 'recipepress-reloaded' )
					) }
				</Button>
			</PluginDocumentSettingPanel>
		);
	}

	return null;
}

registerPlugin( 'rpr-migration', { render: MigrationPanel } );
