<?php
/**
 * Asset registration and enqueueing.
 *
 * @package BlockInteractions
 */

namespace BlockInteractions;

defined( 'ABSPATH' ) || exit;

/**
 * Registers editor and front-end assets.
 */
class Assets {

	/**
	 * Hooks the class methods into WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		add_action( 'init', array( $this, 'register_frontend_assets' ) );
		add_action( 'wp_head', array( $this, 'print_js_flag' ), 1 );
	}

	/**
	 * Enqueues the editor script and the shared stylesheet used for previews.
	 *
	 * @return void
	 */
	public function enqueue_editor_assets() {
		$asset_file = BLOCK_INTERACTIONS_DIR . 'build/index.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;

		wp_enqueue_script(
			'block-interactions-editor',
			BLOCK_INTERACTIONS_URL . 'build/index.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_enqueue_style(
			'block-interactions',
			BLOCK_INTERACTIONS_URL . 'assets/animations.css',
			array(),
			BLOCK_INTERACTIONS_VERSION
		);
	}

	/**
	 * Registers the front-end Interactivity API module and stylesheet.
	 *
	 * These are only enqueued when a block on the page actually uses motion;
	 * see Render::maybe_inject().
	 *
	 * @return void
	 */
	public function register_frontend_assets() {
		wp_register_script_module(
			'block-interactions-view',
			BLOCK_INTERACTIONS_URL . 'assets/view.js',
			array( '@wordpress/interactivity' ),
			BLOCK_INTERACTIONS_VERSION
		);

		wp_register_style(
			'block-interactions',
			BLOCK_INTERACTIONS_URL . 'assets/animations.css',
			array(),
			BLOCK_INTERACTIONS_VERSION
		);
	}

	/**
	 * Prints a tiny inline script that flags JavaScript availability.
	 *
	 * The initial "hidden" animation state is scoped under the `bi-js` class so
	 * that, when JavaScript is unavailable, content remains fully visible.
	 *
	 * @return void
	 */
	public function print_js_flag() {
		if ( is_admin() ) {
			return;
		}

		echo "<script>document.documentElement.classList.add('bi-js');</script>\n";
	}
}
