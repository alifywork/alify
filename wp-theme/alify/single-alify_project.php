<?php
/**
 * Single Custom Post Type Template (single-alify_project.php)
 *
 * @package ALIFY
 */

get_header();
alify_render_partial( 'template-parts/pages/work-single.php' );
alify_render_partial( 'template-parts/work-lightbox.php' );
get_footer();
