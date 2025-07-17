/**
 * External dependencies
 */
import fontsJson from './fonts.json';
import systemFontsJson from './systemFonts.json';

import { __ } from '@wordpress/i18n';
import { Component, Fragment } from '@wordpress/element';
import { SelectControl, RangeControl, PanelBody } from '@wordpress/components';
import { RichText, InspectorControls, BlockControls, AlignmentToolbar, PanelColorSettings } from '@wordpress/block-editor';

class GoogleFontsBlock extends Component {

	componentDidUpdate( prevProps ) {
		if ( this.props.attributes.fontID !== prevProps.attributes.fontID ) {
			this.props.attributes.v = "0";
		}
	}

	/**
	 * Get font families for use in <select>.
	 *
	 * @returns {Object}  value/label pair.
	 */
	getFontsForSelect() {

		const customFonts = Object.values( ogf_custom_fonts ).map( ( font ) => {
			return {
				value: font.id,
				label: font.label,
			};
		} );

		customFonts.unshift({
			value: '1',
			label: __( '- Custom Fonts -', 'olympus-google-fonts' ),
			disabled: true,
		});

		const typekitFonts = Object.values( ogf_typekit_fonts ).map( ( font ) => {
			return {
				value: font.id,
				label: font.label,
			};
		} );

		typekitFonts.unshift({
			value: '1',
			label: __( '- Typekit Fonts -', 'olympus-google-fonts' ),
			disabled: true,
		});

		const systemFonts = systemFontsJson.map( ( font ) => {
			const label = font.label;
			const value = font.id;

			return {
				value: value,
				label: label,
			};
		} );

		systemFonts.unshift({
			value: '1',
			label: __( '- System Fonts -', 'olympus-google-fonts' ),
			disabled: true,
		});

		const googleFonts = fontsJson.map( ( font ) => {
			const label = font.f;
			const value = label.replace( /\s+/g, '+' );

			return {
				value: value,
				label: label,
			};
		} );

		googleFonts.unshift({
			value: '1',
			label: __( '- Google Fonts -', 'olympus-google-fonts' ),
			disabled: true,
		});

		const combinedFonts = customFonts.concat( typekitFonts, systemFonts, googleFonts );
		return combinedFonts;
	}

	searchFonts( nameKey, myArray ) {
		for (var i=0; i < myArray.length; i++) {
			if (myArray[i].id === nameKey) {
				return myArray[i];
			}
		}
	}

	isCustomFont( fontID ) {
		const searchResults = this.searchFonts( fontID, Object.values( ogf_custom_fonts ) );
		if ( typeof searchResults === 'object' ) {
			return true;
		}

		return false;
	}

	isSystemFont( fontID ) {
		const searchResults = this.searchFonts( fontID, systemFontsJson );
		if ( typeof searchResults === 'object' ) {
			return true;
		}

		return false;
	}

	isTypekitFont( fontID ) {
		const searchResults = this.searchFonts( fontID, Object.values( ogf_typekit_fonts ) );
		if ( typeof searchResults === 'object' ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if a font weight is italic.
	 *
	 * @param   {string} value A font weight.
	 * @returns {boolean}  false is value is italic.
	 */
	isItalic( value ) {
		if ( value.includes( '0i' ) ) {
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

		const variantNiceNames = {
			0: __( '- Default -', 'olympus-google-fonts' ),
			100: __( 'Thin', 'olympus-google-fonts' ),
			200: __( 'Extra Light', 'olympus-google-fonts' ),
			300: __( 'Light', 'olympus-google-fonts' ),
			400: __( 'Normal', 'olympus-google-fonts' ),
			500: __( 'Medium', 'olympus-google-fonts' ),
			600: __( 'Semi Bold', 'olympus-google-fonts' ),
			700: __( 'Bold', 'olympus-google-fonts' ),
			800: __( 'Extra Bold', 'olympus-google-fonts' ),
			900: __( 'Ultra Bold', 'olympus-google-fonts' ),
		};

		if( fontObject.v.indexOf("0") === -1 ) {
			fontObject.v.unshift("0");
		}

		return fontObject.v.filter( this.isItalic ).map( ( variant ) => {
			return {
				value: variant,
				label: variantNiceNames[variant],
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
		for ( let i = 0; i < fontsJson.length; i++ ) {
			// look for the entry with a matching `code` value
			if ( fontsJson[ i ].f === fontFamily ) {
				return fontsJson[ i ];
			}
		}
	}

	getFontOutput( fontID ) {
		if ( this.isSystemFont( fontID ) ) {
			return fontID.replace( /\-/g, ' ' );
		}

		else if ( this.isTypekitFont( fontID ) ) {
			return fontID
		}

		else if ( this.isCustomFont( fontID ) ) {
			let fontObject = this.searchFonts( fontID, Object.values( ogf_custom_fonts ) );
			return fontObject.family || fontID;
		}

		else {
			return fontID.replace( /\+/g, ' ' );
		}
	};

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

		const fse = document.querySelector('[name=editor-canvas]');
		const head = fse ? fse.contentDocument.head : document.head;
		const link = document.createElement( 'link' );
		const fontName = fontFamily.replace( /\s+/g, '+' );

		link.type = 'text/css';
		link.rel = 'stylesheet';
		link.id = fontName;
		link.href = 'https://fonts.googleapis.com/css?family=' + fontName + ':' + fontObject.v.join( ',' );

		if ( fse && fse.contentDocument.getElementById( fontName ) ) {
			return;
		}

		if ( document.getElementById( fontName ) ) {
			return;
		}

		if ( head ) {
			head.appendChild( link );
		}
	}

	render() {
		const { attributes, setAttributes } = this.props;
		const { fontID, content, align, variant, fontSize, lineHeight, color, blockType } = attributes;

		const fontOptions = this.getFontsForSelect();
		fontOptions.unshift( { label: '- Default -', value: '0' } );

		let variantOptions = [
			{
				value: '0',
				label: __('- Default -', 'olympus-google-fonts' ),
			},
			{
				value: '100',
				label: __('Thin', 'olympus-google-fonts' ),
			},
			{
				value: '200',
				label: __('Extra Light', 'olympus-google-fonts' ),
			},
			{
				value: '300',
				label: __('Light', 'olympus-google-fonts' ),
			},
			{
				value: '400',
				label: __('Regular', 'olympus-google-fonts' ),
			},
			{
				value: '500',
				label: __('Medium', 'olympus-google-fonts' ),
			},
			{
				value: '600',
				label: __('Semi Bold', 'olympus-google-fonts' ),
			},
			{
				value: '700',
				label: __('Bold', 'olympus-google-fonts' ),
			},
			{
				value: '800',
				label: __('Extra Bold', 'olympus-google-fonts' ),
			},
			{
				value: '900',
				label: __('Ultra Bold', 'olympus-google-fonts' ),
			},
		];

		if ( ! this.isTypekitFont( fontID ) && ! this.isSystemFont( fontID ) && ! this.isCustomFont( fontID ) ) {
			const fontObject = this.getFontObject( fontID.replace( /\+/g, ' ' ) );
			variantOptions = this.getVariantsForSelect( fontObject );
			this.addGoogleFontToHead( fontID, fontObject );
		}
	
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
						min={ 10 }
						max={ 150 }
					/>
					<RangeControl
						label={ __( 'Line Height', 'olympus-google-fonts' ) }
						value={ lineHeight }
						onChange={ ( value ) => setAttributes( { lineHeight: value } ) }
						allowReset={ true }
						min={ 1 }
						max={ 3 }
						step={ 0.1 }
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
						fontSize: fontSize ? Number( fontSize ) + 'px' : undefined,
						textAlign: align,
						fontFamily: this.getFontOutput(fontID),
						fontWeight: variant,
						lineHeight: lineHeight ? Number( lineHeight ) : undefined,
						color: color,
					} }
					placeholder={ __( 'Add some content...', 'olympus-google-fonts' ) }
				/>
			</Fragment>
		);
	}

}

export default GoogleFontsBlock;
