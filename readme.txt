=== Block Interactions ===
Contributors: welcher
Tags: blocks, animation, interactivity-api, motion
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.0
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Bring your blocks to life with elegant, accessible interactions — animations, hover effects, and more — powered by the Interactivity API.

== Description ==

Block Interactions adds a "Motion" panel to a curated set of blocks (Group, Cover, Image, Heading, Columns, Media & Text). Pick an entrance animation preset and tune its duration and delay; the block then animates into view as the reader scrolls down the page.

Animations are progressive enhancement: triggers are handled by the Interactivity API, the motion itself is pure CSS, and `prefers-reduced-motion` is respected. With JavaScript disabled, content renders fully visible.

= How it works =

* The editor extension stores configuration as a block attribute only — no save markup is altered, so existing blocks never break.
* On the front end, a `render_block` filter injects Interactivity API directives into each opted-in block, working for both static and dynamic blocks.
* A single Interactivity store flips a per-element `visible` flag via an IntersectionObserver; CSS handles the rest.

== Development ==

The complete, human-readable source code for this plugin — including the
unminified editor script (`src/index.js`) that compiles to `build/index.js` —
is included in the distributed plugin and is also developed in the open at:

https://github.com/ryanwelcher/block-interactions

Build tools and steps:

* `npm install && npm run build` — compile `src/index.js` into `build/index.js` (uses @wordpress/scripts / webpack).
* `composer install` — install PHPCS + WordPress Coding Standards.
* `composer run lint` / `composer run format` — check / auto-fix PHP against WPCS.

== Changelog ==

= 0.1.0 =
* Initial scaffold: entrance animation presets with on-view triggering.
