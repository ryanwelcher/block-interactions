<?php
/**
 * Plugin Name:       Block Interactions
 * Description:        Bring your blocks to life with elegant, accessible interactions — entrance animations, hover effects, and more — powered by the WordPress Interactivity API.
 * Version:           0.1.0
 * Requires at least: 6.5
 * Requires PHP:      8.0
 * Author:            Ryan Welcher
 * Author URI:        https://ryanwelcher.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       block-interactions
 *
 * @package BlockInteractions
 */

namespace BlockInteractions;

defined( 'ABSPATH' ) || exit;

define( 'BLOCK_INTERACTIONS_VERSION', '0.1.0' );
define( 'BLOCK_INTERACTIONS_FILE', __FILE__ );
define( 'BLOCK_INTERACTIONS_DIR', plugin_dir_path( __FILE__ ) );
define( 'BLOCK_INTERACTIONS_URL', plugin_dir_url( __FILE__ ) );

require_once BLOCK_INTERACTIONS_DIR . 'includes/class-assets.php';
require_once BLOCK_INTERACTIONS_DIR . 'includes/class-render.php';

/**
 * Blocks that expose the Block Interactions controls.
 *
 * Filter this list with `block_interactions_allowed_blocks` to opt blocks in or out.
 *
 * @return string[] Array of block names.
 */
function allowed_blocks() {
	/**
	 * Filters the list of blocks that receive Block Interactions controls.
	 *
	 * @param string[] $blocks Array of block names.
	 */
	return apply_filters(
		'block_interactions_allowed_blocks',
		array(
			'core/group',
			'core/cover',
			'core/image',
			'core/heading',
			'core/columns',
			'core/media-text',
			'core/button',
		)
	);
}

/**
 * Animation preset slugs supported by the plugin.
 *
 * Keep in sync with assets/animations.css and the editor options in src/index.js.
 *
 * @return string[] Array of preset slugs.
 */
function allowed_presets() {
	return array( 'fade-in', 'fade-up', 'fade-down', 'fade-left', 'fade-right', 'zoom-in' );
}

/**
 * Hover effect slugs supported by the plugin.
 *
 * These are pure-CSS effects (no Interactivity store). Keep in sync with
 * assets/animations.css and the editor options in src/index.js.
 *
 * @return string[] Array of hover effect slugs.
 */
function allowed_hover_effects() {
	return array( 'lift', 'scale', 'frame-zoom', 'brighten', 'glow', 'underline', 'underline-link' );
}

/**
 * Boots the plugin once all plugins are loaded.
 *
 * @return void
 */
function bootstrap() {
	( new Assets() )->register();
	( new Render() )->register();
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\\bootstrap' );
