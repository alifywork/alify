<?php
/** ALIFY Studio theme bootstrap. */
if (!defined('ABSPATH')) { exit; }

define('ALIFY_VERSION', '1.0.0');
define('ALIFY_DIR', get_template_directory());
define('ALIFY_URI', get_template_directory_uri());

require_once ALIFY_DIR . '/inc/theme-setup.php';
require_once ALIFY_DIR . '/inc/post-types.php';
require_once ALIFY_DIR . '/inc/acf-fields.php';
require_once ALIFY_DIR . '/inc/catalog-content.php';
require_once ALIFY_DIR . '/inc/seo.php';
require_once ALIFY_DIR . '/inc/forms.php';
require_once ALIFY_DIR . '/inc/template-functions.php';
require_once ALIFY_DIR . '/inc/admin.php';
require_once ALIFY_DIR . '/inc/routes.php';
require_once ALIFY_DIR . '/inc/importer.php';
