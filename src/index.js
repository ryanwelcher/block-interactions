/**
 * Block Interactions — editor extension.
 *
 * Adds "Entrance Animation" and "Hover Effect" panels to allow-listed blocks.
 * The chosen configuration is stored purely as block attributes; no save markup
 * is touched, so existing blocks never trigger validation errors. The front-end
 * markup is produced server-side in includes/class-render.php.
 */

import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { Fragment, useEffect, useState } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, RangeControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Blocks that receive the Motion controls. Keep in sync with allowed_blocks()
 * in block-interactions.php.
 */
const ALLOWED = [
	'core/group',
	'core/cover',
	'core/image',
	'core/heading',
	'core/columns',
	'core/media-text',
	'core/button',
];

const PRESETS = [
	{ label: __( 'None', 'block-interactions' ), value: '' },
	{ label: __( 'Fade In', 'block-interactions' ), value: 'fade-in' },
	{ label: __( 'Fade Up', 'block-interactions' ), value: 'fade-up' },
	{ label: __( 'Fade Down', 'block-interactions' ), value: 'fade-down' },
	{ label: __( 'Fade Left', 'block-interactions' ), value: 'fade-left' },
	{ label: __( 'Fade Right', 'block-interactions' ), value: 'fade-right' },
	{ label: __( 'Zoom In', 'block-interactions' ), value: 'zoom-in' },
];

const DEFAULTS = { preset: '', duration: 600, delay: 0 };

const HOVER_EFFECTS = [
	{ label: __( 'None', 'block-interactions' ), value: '' },
	{ label: __( 'Lift', 'block-interactions' ), value: 'lift' },
	{ label: __( 'Scale', 'block-interactions' ), value: 'scale' },
	{ label: __( 'Frame Zoom', 'block-interactions' ), value: 'frame-zoom' },
	{ label: __( 'Brighten', 'block-interactions' ), value: 'brighten' },
	{ label: __( 'Glow', 'block-interactions' ), value: 'glow' },
	{
		label: __( 'Underline Sweep (block)', 'block-interactions' ),
		value: 'underline',
	},
	{
		label: __( 'Underline Sweep (link)', 'block-interactions' ),
		value: 'underline-link',
	},
];

const HOVER_DEFAULTS = { effect: '', duration: 250 };

/**
 * Registers the `blockMotion` attribute on allow-listed blocks.
 *
 * @param {Object} settings Block settings.
 * @param {string} name     Block name.
 * @return {Object} Filtered settings.
 */
function addAttribute( settings, name ) {
	if ( ! ALLOWED.includes( name ) ) {
		return settings;
	}

	return {
		...settings,
		attributes: {
			...settings.attributes,
			blockMotion: { type: 'object' },
			blockHover: { type: 'object' },
		},
	};
}
addFilter(
	'blocks.registerBlockType',
	'block-interactions/attribute',
	addAttribute
);

/**
 * Adds the Motion inspector panel to allow-listed blocks.
 */
const withControls = createHigherOrderComponent(
	( BlockEdit ) => ( props ) => {
		const { name, attributes, setAttributes } = props;

		if ( ! ALLOWED.includes( name ) ) {
			return <BlockEdit { ...props } />;
		}

		const motion = { ...DEFAULTS, ...( attributes.blockMotion || {} ) };
		const hover = { ...HOVER_DEFAULTS, ...( attributes.blockHover || {} ) };

		const updateMotion = ( next ) => {
			const merged = { ...motion, ...next };
			setAttributes( {
				blockMotion: merged.preset ? merged : undefined,
			} );
		};

		const updateHover = ( next ) => {
			const merged = { ...hover, ...next };
			setAttributes( {
				blockHover: merged.effect ? merged : undefined,
			} );
		};

		return (
			<Fragment>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody
						title={ __(
							'Entrance Animation',
							'block-interactions'
						) }
						initialOpen={ false }
					>
						<SelectControl
							label={ __( 'Animation', 'block-interactions' ) }
							value={ motion.preset }
							options={ PRESETS }
							onChange={ ( preset ) =>
								updateMotion( { preset } )
							}
							__nextHasNoMarginBottom
						/>
						{ motion.preset && (
							<Fragment>
								<RangeControl
									label={ __(
										'Duration (ms)',
										'block-interactions'
									) }
									value={ motion.duration }
									min={ 100 }
									max={ 3000 }
									step={ 50 }
									onChange={ ( duration ) =>
										updateMotion( { duration } )
									}
									__nextHasNoMarginBottom
								/>
								<RangeControl
									label={ __(
										'Delay (ms)',
										'block-interactions'
									) }
									value={ motion.delay }
									min={ 0 }
									max={ 2000 }
									step={ 50 }
									onChange={ ( delay ) =>
										updateMotion( { delay } )
									}
									__nextHasNoMarginBottom
								/>
							</Fragment>
						) }
					</PanelBody>
					<PanelBody
						title={ __( 'Hover Effect', 'block-interactions' ) }
						initialOpen={ false }
					>
						<SelectControl
							label={ __( 'Effect', 'block-interactions' ) }
							value={ hover.effect }
							options={ HOVER_EFFECTS }
							onChange={ ( effect ) => updateHover( { effect } ) }
							help={ __(
								'Plays on mouse hover and keyboard focus. Disabled automatically when the visitor prefers reduced motion.',
								'block-interactions'
							) }
							__nextHasNoMarginBottom
						/>
						{ hover.effect && (
							<RangeControl
								label={ __(
									'Duration (ms)',
									'block-interactions'
								) }
								value={ hover.duration }
								min={ 100 }
								max={ 800 }
								step={ 25 }
								onChange={ ( duration ) =>
									updateHover( { duration } )
								}
								__nextHasNoMarginBottom
							/>
						) }
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	},
	'withMotionControls'
);
addFilter( 'editor.BlockEdit', 'block-interactions/controls', withControls );

/**
 * Previews the entrance animation inside the editor canvas. Re-runs whenever
 * the preset, duration, or delay changes so the user can see their choice.
 */
const withPreview = createHigherOrderComponent(
	( BlockListBlock ) => ( props ) => {
		const cfg = props.attributes?.blockMotion;
		const preset = cfg?.preset;
		const [ visible, setVisible ] = useState( false );

		useEffect( () => {
			if ( ! preset ) {
				return undefined;
			}

			setVisible( false );
			const frame = window.requestAnimationFrame( () =>
				window.requestAnimationFrame( () => setVisible( true ) )
			);

			return () => window.cancelAnimationFrame( frame );
		}, [ preset, cfg?.duration, cfg?.delay ] );

		if ( ! preset ) {
			return <BlockListBlock { ...props } />;
		}

		const className = [
			props.className,
			'bi-animate',
			`bi-${ preset }`,
			visible ? 'is-visible' : '',
		]
			.filter( Boolean )
			.join( ' ' );

		const wrapperProps = {
			...props.wrapperProps,
			style: {
				...( props.wrapperProps?.style || {} ),
				'--bi-duration': `${ cfg.duration ?? 600 }ms`,
				'--bi-delay': `${ cfg.delay ?? 0 }ms`,
			},
		};

		return (
			<BlockListBlock
				{ ...props }
				className={ className }
				wrapperProps={ wrapperProps }
			/>
		);
	},
	'withMotionPreview'
);
addFilter( 'editor.BlockListBlock', 'block-interactions/preview', withPreview );
