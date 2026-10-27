<?php
/**
 * ALIFY Studio WordPress Theme Functions & Bootstrap
 *
 * @package ALIFY
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ALIFY_THEME_VERSION', '1.0.0' );
define( 'ALIFY_THEME_DIR', get_template_directory() );
define( 'ALIFY_THEME_URI', get_template_directory_uri() );

require_once ALIFY_THEME_DIR . '/inc/template-helpers.php';
require_once ALIFY_THEME_DIR . '/inc/nav-walkers.php';
require_once ALIFY_THEME_DIR . '/inc/setup.php';
require_once ALIFY_THEME_DIR . '/inc/customizer.php';
require_once ALIFY_THEME_DIR . '/inc/page-meta-boxes.php';
require_once ALIFY_THEME_DIR . '/inc/post-types.php';
require_once ALIFY_THEME_DIR . '/inc/cpt-meta-boxes.php';
require_once ALIFY_THEME_DIR . '/inc/cpt-renderers.php';
require_once ALIFY_THEME_DIR . '/inc/starter-importer.php';
require_once ALIFY_THEME_DIR . '/inc/enqueue.php';
require_once ALIFY_THEME_DIR . '/inc/ajax-forms.php';
require_once ALIFY_THEME_DIR . '/inc/leads-crm.php';
