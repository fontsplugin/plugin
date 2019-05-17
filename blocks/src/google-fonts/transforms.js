/**
 * WordPress dependencies
 */
const { createBlock } = wp.blocks;

const transforms = {
	from: [
		{
			type: 'block',
			blocks: [ 'core/paragraph' ],
			transform: ( { content } ) => {
				return createBlock( 'olympus-google-fonts/google-fonts', {
					content,
				} );
			},
		},
		{
			type: 'block',
			blocks: [ 'core/heading' ],
			transform: ( { content, level } ) => {
				return createBlock( 'olympus-google-fonts/google-fonts', {
					content,
					blockType: 'h' + level,
				} );
			},
		},
	],
	to: [
		{
			type: 'block',
			blocks: [ 'core/paragraph' ],
			transform: ( { content } ) => {
				return createBlock( 'core/paragraph', {
					content,
				} );
			},
		},
	],
};

export default transforms;
