/**
 * External dependencies
 */
import fontsJson from './fonts.json';
const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const { SelectControl, RangeControl, PanelBody } = wp.components;
const { RichText, InspectorControls, BlockControls, AlignmentToolbar, PanelColorSettings } = wp.editor;

class GoogleFontsBlock extends Component {

	componentDidUpdate( prevProps ) {
		if ( this.props.attributes.fontID !== prevProps.attributes.fontID ) {
			this.props.attributes.variant = 'regular';
		}
	}

	/**
	 * Get font families for use in <select>.
	 *
	 * @returns {Object}  value/label pair.
	 */
	getFontsForSelect() {
		return fontsJson.items.map( ( font ) => {
			const label = font.family;
			const value = label.replace( /\s+/g, '+' );

			return {
				value: value,
				label: label,
			};
		} );
	}

	/**
	 * Check if a font weight is italic.
	 *
	 * @param   {string} value A font weight.
	 * @returns {boolean}  false is value is italic.
	 */
	isItalic( value ) {
		if ( value.includes( '0i' ) || value === 'italic' ) {
			return false;
		}
		return true;
	}

	/**
	 * Get font variants for use in <select>.
	 *
	 * @param   {Object} fontObject The font object.
	 * @returns {Object} value/label pair.
	 */
	getVariantsForSelect( fontObject ) {
		if ( ! fontObject ) {
			return;
		}

		return fontObject.variants.filter( this.isItalic ).map( ( v ) => {
			return {
				value: v,
				label: v,
			};
		} );
	}

	/**
	 * All the font weights as options to be used in a <select> element
	 *
	 * @param   {string} fontFamily font-family name.
	 * @returns {Object}  The font object.
	 */
	getFontObject( fontFamily ) {
		if ( ! fontFamily ) {
			return;
		}

		// iterate over each element in the array
		for ( let i = 0; i < fontsJson.items.length; i++ ) {
			// look for the entry with a matching `code` value
			if ( fontsJson.items[ i ].family === fontFamily ) {
				return fontsJson.items[ i ];
			}
		}
	}

	/**
	 * Add Google Font link to head in block editor.
	 *
	 * @param {string} fontFamily font-family name.
	 * @param {Object} fontObject The font object.
	 */
	addGoogleFontToHead( fontFamily, fontObject ) {
		if ( ! fontFamily || ! fontObject ) {
			return;
		}

		const head = document.head;
		const link = document.createElement( 'link' );

		link.type = 'text/css';
		link.rel = 'stylesheet';
		link.href = 'https://fonts.googleapis.com/css?family=' + fontFamily.replace( /\s+/g, '+' ) + ':' + fontObject.variants.join( ',' );

		head.appendChild( link );
	}

	render() {
		const { attributes, setAttributes } = this.props;
		const { fontID, content, align, variant, fontSize, lineHeight, color, blockType } = attributes;

		const fontOptions = this.getFontsForSelect();
		fontOptions.unshift( { label: '- Select Font -', value: '' } );

		const fontObject = this.getFontObject( fontID.replace( /\+/g, ' ' ) );
		const variantOptions = this.getVariantsForSelect( fontObject );
		this.addGoogleFontToHead( fontID, fontObject );
		const controls = (
			<InspectorControls>
				<PanelBody title={ __( 'Font Settings', 'olympus-google-fonts' ) }>
					<SelectControl
						label={ __( 'Block Type', 'olympus-google-fonts' ) }
						type="string"
						value={ blockType }
						options={ [
							{ label: 'Paragraph', value: 'p' },
							{ label: 'H1', value: 'h1' },
							{ label: 'H2', value: 'h2' },
							{ label: 'H3', value: 'h3' },
							{ label: 'H4', value: 'h4' },
							{ label: 'H5', value: 'h5' },
							{ label: 'H6', value: 'h6' },
							{ label: 'Span', value: 'span' },
						] }
						onChange={ ( value ) => setAttributes( { blockType: value } ) }
					/>
					<SelectControl
						label={ __( 'Font', 'olympus-google-fonts' ) }
						type="string"
						value={ fontID }
						options={ fontOptions }
						onChange={ ( value ) => setAttributes( { fontID: value } ) }
					/>
					<SelectControl
						label={ __( 'Font Variant', 'olympus-google-fonts' ) }
						type="string"
						value={ variant }
						options={ variantOptions }
						onChange={ ( value ) => setAttributes( { variant: value } ) }
					/>
					<RangeControl
						label={ __( 'Font Size', 'olympus-google-fonts' ) }
						value={ fontSize }
						onChange={ ( value ) => setAttributes( { fontSize: value } ) }
						allowReset={ true }
						min="10"
						max="50"
					/>
					<RangeControl
						label={ __( 'Line Height', 'olympus-google-fonts' ) }
						value={ lineHeight }
						onChange={ ( value ) => setAttributes( { lineHeight: value } ) }
						allowReset={ true }
						min="1"
						max="3"
						step="0.1"
					/>
					<PanelColorSettings
						title={ __( 'Color Settings', 'olympus-google-fonts' ) }
						colorSettings={ [
							{
								value: attributes.color,
								onChange: ( value ) => setAttributes( { color: value } ),
								label: __( 'Text Color', 'olympus-google-fonts' ),
							},
						] }
					>
					</PanelColorSettings>
				</PanelBody>
			</InspectorControls>
		);

		return (
			<Fragment>
				{ controls }
				<BlockControls>
					<AlignmentToolbar
						value={ align }
						onChange={ ( value ) => setAttributes( { align: value } ) }
					/>
				</BlockControls>
				<RichText
					tagName={ blockType || 'p' }
					value={ content }
					onChange={ ( value ) => setAttributes( { content: value } ) }
					style={ {
						fontSize: fontSize ? fontSize + 'px' : undefined,
						textAlign: align,
						fontFamily: fontID.replace( /\+/g, ' ' ),
						fontWeight: variant,
						lineHeight: lineHeight,
						color: color
					} }
					placeholder={ __( 'Add some content...', 'olympus-google-fonts' ) }
					formattingControls={ [ 'italic', 'link' ] }
				/>
			</Fragment>
		);
	}

}

export default GoogleFontsBlock;
