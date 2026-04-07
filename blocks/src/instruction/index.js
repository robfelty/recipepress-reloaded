import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import edit from './edit';

registerBlockType( metadata.name, {
	...metadata,
	icon: 'editor-ol',
	edit,
	save: () => null,
} );
