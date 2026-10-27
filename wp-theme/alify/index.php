<?php
/**
 * Main Fallback Index Template (`index.php` — required by WordPress)
 *
 * @package ALIFY
 */

if ( is_front_page() ) {
	require ALIFY_THEME_DIR . '/front-page.php';
} else {
	require ALIFY_THEME_DIR . '/home.php';
}
