import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks } from '@wordpress/block-editor';
import metadata from './block.json';
import edit from './edit';

registerBlockType( metadata.name, {
	...metadata,
	icon: 'format-list-numbered',
	edit,
	save: () => <InnerBlocks.Content />,
} );
