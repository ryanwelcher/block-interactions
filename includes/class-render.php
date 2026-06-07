<?php
/**
 * Front-end rendering: inject Interactivity API directives into blocks.
 *
 * @package BlockInteractions
 */

namespace BlockInteractions;

defined( 'ABSPATH' ) || exit;

/**
 * Injects motion directives into the rendered markup of opted-in blocks.
 */
class Render {

	/**
	 * Hooks the class methods into WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'render_block', array( $this, 'maybe_inject' ), 10, 2 );
	}

	/**
	 * Injects motion and/or hover attributes into a block's root element.
	 *
	 * @param string $block_content The block HTML.
	 * @param array  $block         The parsed block, including its attributes.
	 * @return string The (possibly) modified block HTML.
	 */
	public function maybe_inject( $block_content, $block ) {
		if ( is_admin() || empty( $block_content ) ) {
			return $block_content;
		}

		$name = $block['blockName'] ?? '';

		if ( ! in_array( $name, allowed_blocks(), true ) ) {
			return $block_content;
		}

		$attrs  = is_array( $block['attrs'] ?? null ) ? $block['attrs'] : array();
		$motion = $this->get_motion_config( $attrs );
		$hover  = $this->get_hover_config( $attrs );

		if ( null === $motion && null === $hover ) {
			return $block_content;
		}

		$processor = new \WP_HTML_Tag_Processor( $block_content );

		if ( ! $processor->next_tag() ) {
			return $block_content;
		}

		$style_vars = array();

		if ( null !== $motion ) {
			$processor->add_class( 'bi-animate' );
			$processor->add_class( 'bi-' . $motion['preset'] );
			$processor->set_attribute( 'data-wp-interactive', 'blockInteractions' );
			$processor->set_attribute( 'data-wp-context', wp_json_encode( array( 'visible' => false ) ) );
			$processor->set_attribute( 'data-wp-init', 'callbacks.observe' );
			$processor->set_attribute( 'data-wp-class--is-visible', 'context.visible' );
			$style_vars[] = sprintf( '--bi-duration:%dms', $motion['duration'] );
			$style_vars[] = sprintf( '--bi-delay:%dms', $motion['delay'] );
		}

		if ( null !== $hover ) {
			$processor->add_class( 'bi-hover' );
			$processor->add_class( 'bi-hover-' . $hover['effect'] );
			$style_vars[] = sprintf( '--bi-hover-duration:%dms', $hover['duration'] );
		}

		$existing = $processor->get_attribute( 'style' );
		$processor->set_attribute(
			'style',
			implode( ';', $style_vars ) . ';' . ( is_string( $existing ) ? $existing : '' )
		);

		$html = $processor->get_updated_html();

		// Resolve initial state server-side for a flash-free first paint.
		if ( null !== $motion ) {
			if ( function_exists( 'wp_interactivity_process_directives' ) ) {
				$html = wp_interactivity_process_directives( $html );
			}
			wp_enqueue_script_module( 'block-interactions-view' );
		}

		wp_enqueue_style( 'block-interactions' );

		return $html;
	}

	/**
	 * Validates and normalizes the entrance-animation configuration.
	 *
	 * @param array $attrs Block attributes.
	 * @return array|null Normalized config, or null when not configured.
	 */
	private function get_motion_config( array $attrs ) {
		$config = $attrs['blockMotion'] ?? null;

		if ( ! is_array( $config ) || empty( $config['preset'] ) ) {
			return null;
		}

		$preset = sanitize_html_class( $config['preset'] );

		if ( ! in_array( $preset, allowed_presets(), true ) ) {
			return null;
		}

		return array(
			'preset'   => $preset,
			'duration' => isset( $config['duration'] ) ? absint( $config['duration'] ) : 600,
			'delay'    => isset( $config['delay'] ) ? absint( $config['delay'] ) : 0,
		);
	}

	/**
	 * Validates and normalizes the hover-effect configuration.
	 *
	 * @param array $attrs Block attributes.
	 * @return array|null Normalized config, or null when not configured.
	 */
	private function get_hover_config( array $attrs ) {
		$config = $attrs['blockHover'] ?? null;

		if ( ! is_array( $config ) || empty( $config['effect'] ) ) {
			return null;
		}

		$effect = sanitize_html_class( $config['effect'] );

		if ( ! in_array( $effect, allowed_hover_effects(), true ) ) {
			return null;
		}

		return array(
			'effect'   => $effect,
			'duration' => isset( $config['duration'] ) ? absint( $config['duration'] ) : 250,
		);
	}
}
