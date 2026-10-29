<?php
/**
 * ALIFY Child Theme Functions
 *
 * Note: The parent `alify` theme (`inc/enqueue.php`) automatically detects `is_child_theme()`
 * and enqueues this child theme's `style.css` (`alify-child-style`) AFTER all parent stylesheets,
 * preserving exact cascade order without duplicate `<link>` tags!
 *
 * @package ALIFY_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load optional child theme translations from `wp-theme/alify-child/languages/`.
 */
function alify_child_setup() {
	load_child_theme_textdomain( 'alify-child', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'alify_child_setup', 20 );
