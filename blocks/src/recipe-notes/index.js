import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import edit from './edit';

registerBlockType( metadata.name, {
	...metadata,
	icon: 'testimonial',
	edit,
	save: () => null,
} );
